<script setup>
import { ref, computed, watch,onMounted } from 'vue';
import { Handle, useVueFlow, useNode } from '@vue-flow/core';

const props = defineProps({
  id: { type: String, required: true },
  data: { type: Object, required: true },
  selected: { type: Boolean, default: false },
});

const emit = defineEmits(['update:isValid', 'update-node']);

const { removeNodes, nodes, addNodes } = useVueFlow();
const node = useNode();

// Extract data from whatsmark output structure
const output = ref(
  props.data.output?.[0] || {
    media_type: 'image',
    media_url: '',
    media_caption: '',
    media_filename: '',
  }
);

const mediaType = ref(output.value.media_type || 'image');
const mediaUrl = ref(output.value.media_url || '');
const caption = ref(output.value.media_caption || '');
const fileName = ref(output.value.media_filename || '');
const errors = ref({});
// Rest of your variables
const isExpanded = ref(true);
const uploadedFile = ref(null);
const isUploading = ref(false);
const uploadError = ref('');

// Get allowed extensions from the window.metaAllowedExtensions
const validate_extension = window.metaAllowedExtensions || {
  image: {
    extension: '.jpeg, .png',
    size: 5,
  },
  video: {
    extension: '.mp4, .3gp',
    size: 16,
  },
  audio: {
    extension: '.aac, .amr, .mp3, .m4a, .ogg',
    size: 16,
  },
  document: {
    extension: '.pdf, .doc, .docx, .txt, .xls, .xlsx, .ppt, .pptx',
    size: 100,
  },
  sticker: {
    extension: '.webp',
    size: 0.1,
  },
};

// Build media types dynamically from the validation data
const mediaTypes = Object.keys(validate_extension)
  .map((type) => {
    const extensions = validate_extension[type].extension.split(', ');
    const iconMap = {
      image: 'image',
      video: 'video',
      audio: 'music',
      document: 'file-text',
      sticker: 'sticker',
    };

    return {
      value: type,
      label: type.charAt(0).toUpperCase() + type.slice(1),
      icon: iconMap[type] || 'file',
      extensions: extensions,
      maxSize: validate_extension[type].size, // Size in MB
    };
  })
  .filter((type) => type.value !== 'sticker'); // Exclude sticker if needed

// Input handler with automatic trimming for caption
function handleCaptionInput() {
  if (caption.value.length > 60) {
    caption.value = caption.value.substring(0, 60);
  }
  updateNodeData();
}

function updateNodeData() {
  const nodeData = {
    output: [
      {
        media_type: mediaType.value,
        media_url: mediaUrl.value,
        media_caption: caption.value,
        media_filename: fileName.value,
      },
    ],
    isValid: isValid.value
  };
  
  // Update local props
  props.data.output = nodeData.output;
  props.data.isValid = nodeData.isValid;
  
  // Emit to parent BotFlowBuilder
  emit('update-node', {
    id: props.id,
    data: nodeData
  });
  
  // Validate the form whenever data is updated
  validateForm();
}

function handleClickDelete() {
  removeNodes(node.id);
}

function handleClickDuplicate() {
  const { type, position, data } = node.node;

  const newNode = {
    id: (nodes.value.length + 1).toString(),
    type,
    position: {
      x: position.x - 100,
      y: position.y - 100,
    },
    data: JSON.parse(JSON.stringify(data)), // Deep copy to prevent shared reference
  };

  addNodes(newNode);
}
// Function to validate the form and update errors
function validateForm() {
  errors.value = {};

  // Check if neither URL nor file is provided
  if (!mediaUrl.value.trim() && !uploadedFile.value) {
    errors.value.mediaUrl = true;
  }

  // Update validation state
  const valid = isValid.value;

  // Update node data with validation state
  props.data.isValid = valid;

  // Emit validation status to parent component
  emit('update:isValid', valid);

  return valid;
}

function toggleExpand() {
  isExpanded.value = !isExpanded.value;
}

// Computed property for overall validation state
const isValid = computed(() => {
  // Valid if either URL is provided OR file is uploaded
  return mediaUrl.value.trim() !== '' || uploadedFile.value !== null;
});

async function handleFileUpload(event) {
  const file = event.target.files[0];
  if (!file) return;

  uploadedFile.value = file;
  fileName.value = file.name;

  // Check file type against allowed extensions
  const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
  const mediaTypeData = mediaTypes.find((type) => type.value === mediaType.value);

  if (!mediaTypeData || !mediaTypeData.extensions.includes(fileExtension)) {
    showNotification(
      `Invalid file type for ${mediaType.value}. Please select one of: ${mediaTypeData?.extensions.join(', ')}`,
      'danger'
    );

    uploadedFile.value = null;
    fileName.value = '';
    return;
  }

  // Check file size
  const fileSizeMB = file.size / (1024 * 1024);
  if (fileSizeMB > mediaTypeData.maxSize) {
    showNotification(
      `File is too large. Maximum size for ${mediaType.value} is ${mediaTypeData.maxSize}MB.`,
      'danger'
    );
    uploadedFile.value = null;
    fileName.value = '';
    return;
  }

  // Upload the file to Laravel backend
  await uploadFileToServer(file);
}

async function uploadFileToServer(file) {
  isUploading.value = true;
  uploadError.value = '';

  try {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', mediaType.value);

    // Get tenant subdomain from window or current URL
    const tenantSubdomain = window.tenantSubdomain || window.location.pathname.split('/')[1] || '';
    
    // Replace with your actual Laravel API endpoint
    const response = await fetch(`/${tenantSubdomain}/upload-media`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN':
          document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: formData,
    });

    if (!response.ok) {
      const errorText = await response.text();
      console.error('Error response text:', errorText);

      try {
        const errorData = JSON.parse(errorText);
        throw new Error(errorData.message || `Upload failed with status: ${response.status}`);
      } catch (jsonError) {
        throw new Error(`Upload failed with status: ${response.status}`);
      }
    }

    const data = await response.json();

    // Update with the public URL received from Laravel
    if (data && data.url) {
      mediaUrl.value = data.url; // This will be a public URL, not a blob
      updateNodeData();
    } else {
      throw new Error('Invalid response from server');
    }
  } catch (error) {
    uploadError.value = error.message || 'Failed to upload file. Please try again.';
  } finally {
    isUploading.value = false;
  }
}

function validateUrl() {
  if (!mediaUrl.value) return true;

  // Basic URL validation
  try {
    new URL(mediaUrl.value);
  } catch (e) {
    return false;
  }

  // If it's a public URL from our server, we know it's valid
  if (
    mediaUrl.value.startsWith(window.location.origin) ||
    mediaUrl.value.includes('/storage/') ||
    mediaUrl.value.includes('/media/')
  ) {
    return true;
  }

  // For other URLs, check file extension
  const fileExtension = '.' + mediaUrl.value.split('.').pop().toLowerCase();
  const mediaTypeData = mediaTypes.find((type) => type.value === mediaType.value);

  return mediaTypeData && mediaTypeData.extensions.includes(fileExtension);
}
// Watch for changes in form fields
watch(
  [mediaUrl],
  () => {
    validateForm();
  },
  { deep: true }
);
// Reset file when media type changes
watch(mediaType, (newType) => {
  if (uploadedFile.value) {
    const fileExtension = '.' + uploadedFile.value.name.split('.').pop().toLowerCase();
    const mediaTypeData = mediaTypes.find((type) => type.value === newType);

    if (!mediaTypeData || !mediaTypeData.extensions.includes(fileExtension)) {
      uploadedFile.value = null;
      fileName.value = '';
      mediaUrl.value = '';
    }
  }
  updateNodeData();
});

const showCaption = computed(() => {
  return ['image', 'video', 'document'].includes(mediaType.value);
});

const showFileName = computed(() => {
  return mediaType.value === 'document';
});

const urlIsValid = computed(() => validateUrl());

const nodeClasses = computed(() => {
  return `flow-node media-message-node relative ${
    props.selected ? 'selected' : ''
  } ${!urlIsValid.value && mediaUrl.value && isValid.value ? 'border-danger-300' : ''} transition-all duration-200`;
});

const selectedMediaType = computed(() => {
  return mediaTypes.find((type) => type.value === mediaType.value) || mediaTypes[0];
});

// Character count for caption field
const captionCount = computed(() => caption.value.length);
// Initial validation on mount
onMounted(() => {
  validateForm();
});
// Get preview content based on media type and URL
const mediaPreview = computed(() => {
  // Priority: 1. Server URL, 2. Local blob URL, 3. None
  let sourceUrl = null;
  
  if (mediaUrl.value && mediaUrl.value.trim()) {
    // Use server URL if available
    sourceUrl = mediaUrl.value;
  } else if (uploadedFile.value) {
    // Use blob URL for local file preview
    sourceUrl = URL.createObjectURL(uploadedFile.value);
  }
  
  if (!sourceUrl) {
    return `<div class="text-sm text-gray-500">No media to preview</div>`;
  }

  switch (mediaType.value) {
    case 'image':
      return `<div>
                <img src="${sourceUrl}" alt="${fileName.value || 'Image preview'}" class="max-h-32 max-w-full rounded" 
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';" 
                     onload="this.style.display='block'; if(this.nextElementSibling) this.nextElementSibling.style.display='none';"/>
                <div style="display:none;" class="text-sm text-red-500">
                  Image preview unavailable
                </div>
              </div>`;
    case 'video':
      return `<video controls class="max-h-32 max-w-full rounded">
                <source src="${sourceUrl}" type="video/mp4">
                Your browser does not support the video tag.
              </video>`;
    case 'audio':
      return `<audio controls class="max-w-full">
                <source src="${sourceUrl}" type="audio/mpeg">
                Your browser does not support the audio tag.
              </audio>`;
    case 'document':
      return `<div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8 mr-2">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                  <polyline points="14 2 14 8 20 8"/>
                  <line x1="16" y1="13" x2="8" y2="13"/>
                  <line x1="16" y1="17" x2="8" y2="17"/>
                  <polyline points="10 9 9 9 8 9"/>
                </svg>
                <span class="text-sm truncate">${fileName.value || 'Document'}</span>
              </div>`;
    default:
      return null;
  }
});

// Get icon for the selected media type
function getMediaTypeIcon(type) {
  const iconMap = {
    image: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>`,
    video: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>`,
    audio: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>`,
    document: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>`,
    sticker: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>`,
  };

  return iconMap[type] || iconMap['document'];
}
</script>

<template>
  <div class="h-full w-full">
    <Handle
      type="target"
      position="left"
      :class="[
        'z-10 !h-4 !w-4 !border-2 !border-white !bg-gradient-to-r !shadow-md !transition-transform !duration-300',
        isValid ? '!from-danger-400 !to-pink-400' : '!from-danger-500 !to-orange-500',
      ]"
    />

    <div
      :class="[
        nodeClasses,
        'overflow-hidden rounded-lg border-2 border-gray-200 bg-white shadow-lg transition-all duration-200 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800',
      ]"
      style="min-width: 280px; max-width: 320px"
    >
      <!-- Node type indicator - gradient bar -->
      <div
        :class="[
          'h-1.5 bg-gradient-to-r',
          isValid ? 'from-danger-400 to-pink-400' : 'from-danger-500 to-orange-500',
        ]"
      ></div>

      <div class="p-4">
        <!-- Node Header -->
        <div class="node-header mb-3 flex items-center justify-between">
          <div class="node-title flex items-center">
            <div
              class="node-icon mr-3 h-8 w-8 rounded-lg bg-danger-100 p-2 text-danger-600 shadow-sm dark:bg-danger-900/50 dark:text-danger-300"
              v-html="getMediaTypeIcon(mediaType)"
            ></div>

            <span class="text-sm font-medium text-gray-800 dark:text-gray-200"
              >{{ selectedMediaType.label }} {{ data.label }}</span
            >
          </div>

          <div class="node-actions flex space-x-1">
            <button
              @click="toggleExpand"
              class="node-action-btn transform rounded-md border border-transparent bg-white p-1.5 text-gray-500 shadow-sm transition-all duration-300 ease-in-out hover:scale-105 hover:border-gray-200 hover:bg-gray-50 hover:text-danger-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:bg-gray-600 dark:hover:text-danger-400"
              :title="isExpanded ? 'Collapse' : 'Expand'"
            >
              <svg
                v-if="isExpanded"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="h-4 w-4 transform transition-transform duration-300"
              >
                <polyline points="18 15 12 9 6 15"></polyline>
              </svg>
              <svg
                v-else
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="h-4 w-4 transform transition-transform duration-300"
              >
                <polyline points="6 9 12 15 18 9"></polyline>
              </svg>
            </button>
            <button
              v-on:click="handleClickDuplicate"
              class="node-action-btn transform rounded-md border border-transparent bg-white p-1.5 text-gray-500 shadow-sm transition-all duration-300 ease-in-out hover:scale-105 hover:border-gray-200 hover:bg-gray-50 hover:text-danger-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:bg-gray-600 dark:hover:text-danger-400"
              title="Copy node"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="h-4 w-4"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"
                />
              </svg>
            </button>
            <button
              v-on:click="handleClickDelete"
              class="node-action-btn transform rounded-md border border-transparent bg-white p-1.5 text-gray-500 shadow-sm transition-all duration-300 ease-in-out hover:scale-105 hover:border-danger-200 hover:bg-danger-50 hover:text-danger-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:border-danger-800/50 dark:hover:bg-danger-900/30 dark:hover:text-danger-400"
              title="Delete node"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="h-4 w-4"
              >
                <path d="M3 6h18"></path>
                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
              </svg>
            </button>
          </div>
        </div>
   <!-- Validation Error Message (when not valid) -->
   <div
        v-if="!isValid && isExpanded"
        class="mb-3 rounded-md border border-danger-200 bg-danger-50 p-3 text-sm text-danger-600 dark:border-danger-800/50 dark:bg-danger-900/30 dark:text-danger-400"
      >
        <div class="flex">
           <svg
              xmlns="http://www.w3.org/2000/svg"
              class="mr-2 h-5 w-5 text-danger-500"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="8" x2="12" y2="12"></line>
              <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
          <div>
            <div class="font-medium">Please fix the following errors:</div>
            <ul class="mt-1 list-inside list-disc">
               <li v-if="errors.mediaUrl">Either upload a media file or provide a media URL</li>
            </ul>
          </div>
        </div>
      </div>

        <!-- Node Content -->
        <div v-show="isExpanded" class="node-content space-y-4">
          <!-- Media Type Selection -->
          <div class="node-field">
            <label
              class="node-field-label mb-1.5 flex items-center text-xs font-medium text-gray-700 dark:text-gray-300"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="mr-1.5 h-3.5 w-3.5 text-danger-500"
              >
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
              </svg>
              Media Type
            </label>
            <div class="relative">
              <v-select
                v-model="mediaType"
                :options="mediaTypes"
                label="label"
                :reduce="(type) => type.value"
                @input="updateNodeData"
              />
            </div>
            <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
              Supported formats: {{ selectedMediaType.extensions.join(', ') }}
            </p>
          </div>

          <!-- Media File Upload -->
          <div class="node-field">
            <label
              class="node-field-label mb-1.5 flex items-center text-xs font-medium text-gray-700 dark:text-gray-300"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="mr-1.5 h-3.5 w-3.5 text-danger-500"
              >
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="17 8 12 3 7 8"></polyline>
                <line x1="12" y1="3" x2="12" y2="15"></line>
              </svg>
              Upload Media
            </label>
            <div class="flex items-center">
              <label
                class="flex-1 cursor-pointer"
                :class="{ 'pointer-events-none opacity-50': isUploading }"
              >
                <div
                  class="flex items-center justify-center rounded-md border border-dashed border-gray-300 p-4 transition-colors hover:border-danger-300 hover:bg-gray-50 dark:border-gray-600 dark:hover:border-danger-700 dark:hover:bg-gray-700"
                >
                  <div class="text-center">
                    <div v-if="isUploading" class="mb-2 flex items-center justify-center">
                      <svg
                        class="h-5 w-5 animate-spin text-danger-500"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                      >
                        <circle
                          class="opacity-25"
                          cx="12"
                          cy="12"
                          r="10"
                          stroke="currentColor"
                          stroke-width="4"
                        ></circle>
                        <path
                          class="opacity-75"
                          fill="currentColor"
                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                      </svg>
                    </div>
                    <svg
                      v-else
                      xmlns="http://www.w3.org/2000/svg"
                      class="mx-auto h-8 w-8 text-gray-400 dark:text-gray-500"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                      />
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                      {{ isUploading ? 'Uploading...' : (uploadedFile ? `✓ ${fileName}` : 'Click to upload media') }}
                    </p>
                    <p v-if="uploadedFile && !isUploading" class="mt-1 text-xs text-green-600 dark:text-green-400">
                      File ready for upload
                    </p>
                  </div>
                </div>
                <input
                  type="file"
                  class="hidden"
                  @change="handleFileUpload"
                  :disabled="isUploading"
                  :accept="selectedMediaType.extensions.join(',')"
                />
              </label>
            </div>
            <p v-if="uploadError" class="mt-1.5 text-xs text-danger-500 dark:text-danger-400">
              {{ uploadError }}
            </p>
          </div>

          <!-- Media URL -->
          <div class="node-field">
            <label
              class="node-field-label mb-1.5 flex items-center text-xs font-medium" :class="errors.mediaUrl ? 'text-danger-500' : 'text-gray-700 dark:text-gray-300'"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="mr-1.5 h-3.5 w-3.5"
                :class="errors.mediaUrl ? 'text-danger-500' : 'text-danger-500'"
              >
                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
              </svg>
              Media URL (Optional if file uploaded)
            </label>
            <input
              v-model="mediaUrl"
              @input="updateNodeData"
              class="block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-danger-500 focus:ring focus:ring-danger-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
              :class="{ 'border-danger-300 dark:border-danger-700': !urlIsValid && mediaUrl && isValid}"
              placeholder="Enter media URL"
            />
            <p v-if="!urlIsValid && mediaUrl" class="mt-1.5 text-xs text-danger-500 dark:text-danger-400">
              Please enter a valid URL for {{ selectedMediaType.label }} format
            </p>
          </div>

          <!-- Filename (for documents) -->
          <div v-if="showFileName" class="node-field">
            <label
              class="node-field-label mb-1.5 flex items-center text-xs font-medium text-gray-700 dark:text-gray-300"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="mr-1.5 h-3.5 w-3.5 text-danger-500"
              >
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
              </svg>
              File Name
            </label>
            <input
              v-model="fileName"
              @input="updateNodeData"
              class="block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-danger-500 focus:ring focus:ring-danger-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
              placeholder="Enter filename (for documents)"
            />
          </div>

          <!-- Caption (for image, video, document) -->
          <div v-if="showCaption" class="node-field">
            <label
              class="node-field-label mb-1.5 flex items-center text-xs font-medium text-gray-700 dark:text-gray-300"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="mr-1.5 h-3.5 w-3.5 text-danger-500"
              >
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
              </svg>
              Caption
            </label>
            <textarea
              v-model="caption"
              @input="handleCaptionInput"
              @paste="setTimeout(() => handleCaptionInput(), 0)"
              class="block w-full resize-none rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-danger-500 focus:ring focus:ring-danger-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
              placeholder="Enter caption text (optional)"
              rows="3"
              maxlength="60"
            ></textarea>
            <div class="mt-1 flex justify-end text-xs">
              <span
                :class="
                  captionCount >= 60
                    ? 'text-warning-500'
                    : 'text-gray-500 dark:text-gray-400'
                "
              >
                {{ captionCount }}/60
                <span v-if="captionCount >= 60" class="ml-1 text-warning-600">(Max reached)</span>
              </span>
            </div>
          </div>

          <!-- Media Preview -->
          <div
            v-if="mediaUrl || uploadedFile"
            class="mt-4 rounded-md border border-gray-200 p-4 shadow-sm dark:border-gray-700"
          >
            <div class="mb-2 text-xs font-medium text-gray-700 dark:text-gray-300">Preview:</div>
            <div class="flex justify-center" v-html="mediaPreview"></div>
          </div>

          <!-- Media Type Preview -->
          <div
            class="mt-4 rounded-md border border-gray-200 bg-gray-50 p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800/50"
          >
            <div class="flex items-center text-gray-700 dark:text-gray-300">
              <div
                class="mr-3 flex h-10 w-10 items-center justify-center rounded-lg bg-danger-100 text-danger-600 shadow-sm dark:bg-danger-900/50 dark:text-danger-300"
                v-html="getMediaTypeIcon(mediaType)"
              ></div>
              <div>
                <div class="text-sm font-medium">{{ selectedMediaType.label }} Message</div>
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                  Will send a {{ selectedMediaType.label.toLowerCase() }} via WhatsApp
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

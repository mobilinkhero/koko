<template>
  <div class="input-collection-node">
    <NodeWrapper
      :id="id"
      :selected="selected"
      :type="'inputCollection'"
      :title="'Input Collection'"
      :icon="FormIcon"
      :color="'#059669'"
    >
      <template #content>
        <div class="space-y-4">
          <!-- Collection Message -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Collection Message
            </label>
            <textarea
              v-model="nodeData.message"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white resize-none"
              placeholder="Please provide your information..."
              @input="updateNodeData"
            />
            <p class="text-xs text-gray-500 mt-1">
              Message to show before collecting input
            </p>
          </div>

          <!-- Input Fields -->
          <div>
            <div class="flex justify-between items-center mb-3">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Input Fields
              </label>
              <button
                @click="addField"
                class="text-sm text-blue-600 hover:text-blue-800"
              >
                + Add Field
              </button>
            </div>

            <div v-for="(field, index) in nodeData.fields" :key="index" class="border border-gray-200 dark:border-gray-600 rounded-lg p-3 mb-3">
              <div class="flex justify-between items-center mb-3">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                  Field {{ index + 1 }}
                </h4>
                <button
                  v-if="nodeData.fields.length > 1"
                  @click="removeField(index)"
                  class="text-red-500 hover:text-red-700 text-sm"
                >
                  Remove
                </button>
              </div>

              <!-- Field Type -->
              <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                  <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                    Field Type
                  </label>
                  <select
                    v-model="field.type"
                    class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                    @change="updateNodeData"
                  >
                    <option value="text">Text</option>
                    <option value="email">Email</option>
                    <option value="phone">Phone</option>
                    <option value="number">Number</option>
                    <option value="date">Date</option>
                    <option value="choice">Multiple Choice</option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                    Variable Name
                  </label>
                  <input
                    v-model="field.variable"
                    type="text"
                    class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                    placeholder="variable_name"
                    @input="updateNodeData"
                  />
                </div>
              </div>

              <!-- Field Label -->
              <div class="mb-3">
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                  Field Label
                </label>
                <input
                  v-model="field.label"
                  type="text"
                  class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                  placeholder="Enter your name"
                  @input="updateNodeData"
                />
              </div>

              <!-- Validation Rules -->
              <div class="grid grid-cols-2 gap-3 mb-3">
                <div class="flex items-center">
                  <input
                    v-model="field.required"
                    type="checkbox"
                    class="h-3 w-3 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    @change="updateNodeData"
                  />
                  <label class="ml-2 text-xs text-gray-700 dark:text-gray-300">
                    Required
                  </label>
                </div>
                <div v-if="field.type === 'text'" class="flex items-center">
                  <input
                    v-model.number="field.minLength"
                    type="number"
                    min="0"
                    class="w-16 px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                    placeholder="0"
                    @input="updateNodeData"
                  />
                  <label class="ml-1 text-xs text-gray-700 dark:text-gray-300">
                    Min length
                  </label>
                </div>
              </div>

              <!-- Choice Options (for multiple choice) -->
              <div v-if="field.type === 'choice'" class="space-y-2">
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">
                  Options (one per line)
                </label>
                <textarea
                  v-model="field.options"
                  rows="3"
                  class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white resize-none"
                  placeholder="Option 1&#10;Option 2&#10;Option 3"
                  @input="updateNodeData"
                />
              </div>

              <!-- Error Message -->
              <div class="mb-3">
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                  Error Message (Optional)
                </label>
                <input
                  v-model="field.errorMessage"
                  type="text"
                  class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                  placeholder="Please enter a valid value"
                  @input="updateNodeData"
                />
              </div>
            </div>
          </div>

          <!-- Collection Settings -->
          <div class="border-t border-gray-200 dark:border-gray-600 pt-3">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Collection Settings</h4>
            
            <div class="space-y-3">
              <!-- Collection Mode -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Collection Mode
                </label>
                <select
                  v-model="nodeData.collectionMode"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                  @change="updateNodeData"
                >
                  <option value="sequential">Sequential (one by one)</option>
                  <option value="form">Form (all at once)</option>
                </select>
              </div>

              <!-- Retry Settings -->
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Max Retries
                  </label>
                  <input
                    v-model.number="nodeData.maxRetries"
                    type="number"
                    min="1"
                    max="5"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                    @input="updateNodeData"
                  />
                </div>
                <div class="flex items-center">
                  <input
                    v-model="nodeData.skipOnError"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    @change="updateNodeData"
                  />
                  <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                    Skip on error
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Preview -->
          <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 border">
            <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Collection Preview:</p>
            <div class="text-sm text-gray-700 dark:text-gray-300">
              <p class="mb-2">{{ nodeData.message || 'Collection message...' }}</p>
              <div class="space-y-1">
                <div v-for="(field, index) in nodeData.fields" :key="index" class="text-xs">
                  {{ index + 1 }}. {{ field.label || 'Field label' }} 
                  <span class="text-gray-500">({{ field.type }}{{ field.required ? ', required' : '' }})</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Info -->
          <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3">
            <p class="text-xs text-green-700 dark:text-green-300">
              <strong>Input Collection:</strong> Collects structured data from users. 
              Variables will be available in subsequent nodes as {{variable_name}}.
            </p>
          </div>
        </div>
      </template>

      <template #handles>
        <Handle
          id="input"
          type="target"
          position="left"
          class="w-3 h-3 !bg-gray-400"
        />
        <Handle
          id="success"
          type="source"
          position="right"
          :style="{ top: '30%' }"
          class="w-3 h-3 !bg-green-500"
        />
        <Handle
          id="error"
          type="source"
          position="right"
          :style="{ top: '70%' }"
          class="w-3 h-3 !bg-red-500"
        />
      </template>
    </NodeWrapper>
  </div>
</template>

<script setup>
import { reactive, watch } from 'vue'
import { Handle, Position } from '@vue-flow/core'
import NodeWrapper from '../ui/NodeWrapper.vue'

// Form icon component
const FormIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
      <path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.89 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm4 18H6V4h7v5h5v11z"/>
      <path d="M8 12h8v2H8zm0 3h8v2H8zm0-6h5v2H8z"/>
    </svg>
  `
}

const props = defineProps({
  id: String,
  data: Object,
  selected: Boolean,
})

const emit = defineEmits(['update-node'])

// Initialize node data with default structure
const nodeData = reactive({
  message: props.data?.message || 'Please provide the following information:',
  fields: props.data?.fields || [
    {
      type: 'text',
      variable: 'user_name',
      label: 'What is your name?',
      required: true,
      minLength: 2,
      errorMessage: 'Please enter a valid name',
      options: ''
    }
  ],
  collectionMode: props.data?.collectionMode || 'sequential',
  maxRetries: props.data?.maxRetries || 3,
  skipOnError: props.data?.skipOnError || false,
})

// Add new field
const addField = () => {
  nodeData.fields.push({
    type: 'text',
    variable: `field_${nodeData.fields.length + 1}`,
    label: 'Enter value:',
    required: false,
    minLength: 0,
    errorMessage: 'Please enter a valid value',
    options: ''
  })
  updateNodeData()
}

// Remove field
const removeField = (index) => {
  nodeData.fields.splice(index, 1)
  updateNodeData()
}

// Update node data when changes occur
const updateNodeData = () => {
  emit('update-node', {
    id: props.id,
    data: { ...nodeData }
  })
}

// Watch for external data changes
watch(() => props.data, (newData) => {
  if (newData) {
    Object.assign(nodeData, {
      message: newData.message || nodeData.message,
      fields: newData.fields || nodeData.fields,
      collectionMode: newData.collectionMode || nodeData.collectionMode,
      maxRetries: newData.maxRetries || nodeData.maxRetries,
      skipOnError: newData.skipOnError || nodeData.skipOnError,
    })
  }
}, { deep: true })
</script>

<style scoped>
.input-collection-node {
  min-width: 380px;
}
</style>

<template>
  <div class="ai-assistant-node">
    <NodeWrapper
      :id="id"
      :selected="selected"
      :type="'aiAssistant'"
      :title="'AI Assistant'"
      :icon="FlFilledLightbulbFilament"
      :color="'#F59E0B'"
    >
      <template #content>
        <div class="space-y-4">
          <!-- Assistant Selection (Primary) -->
          <div v-if="personalAssistants.length > 0">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Select Assistant
            </label>
            <select
              v-model="nodeData.selectedAssistantId"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
              @change="updateNodeData"
            >
              <option value="" disabled>Choose an assistant...</option>
              <option v-for="assistant in personalAssistants" :key="assistant.id" :value="assistant.id">
                {{ assistant.name }}
              </option>
            </select>
            <p class="text-xs text-gray-500 mt-1">
              Select an AI assistant you've created to use in this flow
            </p>
          </div>

          <!-- No Assistants Available -->
          <div v-else class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3">
            <p class="text-sm text-yellow-800 dark:text-yellow-200 font-medium mb-1">
              No Assistants Available
            </p>
            <p class="text-xs text-yellow-700 dark:text-yellow-300">
              Create an AI assistant first at <a href="/ai-assistant" class="underline" target="_blank">AI Assistant</a> to use in this flow.
            </p>
          </div>

          <!-- Selected Assistant Info -->
          <div v-if="personalAssistant && personalAssistants.length > 0" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 space-y-2">
            <div class="flex items-center space-x-2">
              <div class="w-2 h-2 bg-green-500 rounded-full"></div>
              <span class="font-medium text-blue-800 dark:text-blue-200">{{ personalAssistant.name }}</span>
            </div>
            <p class="text-sm text-blue-700 dark:text-blue-300">{{ personalAssistant.description || 'No description' }}</p>
            <p class="text-xs text-blue-600 dark:text-blue-400">
              Model: {{ personalAssistant.model }} • {{ personalAssistant.file_count || 0 }} files loaded
            </p>
          </div>

          <!-- Status Indicator -->
          <div class="flex items-center space-x-2 text-sm">
            <div class="flex items-center">
              <div :class="aiEnabled ? 'bg-green-500' : 'bg-red-500'" class="w-2 h-2 rounded-full mr-2"></div>
              <span :class="aiEnabled ? 'text-green-600' : 'text-red-600'">
                {{ aiEnabled ? 'AI Enabled' : 'AI Disabled' }}
              </span>
            </div>
          </div>

          <!-- Warning if AI not enabled -->
          <div v-if="!aiEnabled" class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3">
            <p class="text-sm text-yellow-800 dark:text-yellow-200">
              <strong>Warning:</strong> AI Assistant module is not enabled. This node will not function until AI integration is configured in settings.
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
          id="output"
          type="source"
          position="right"
          class="w-3 h-3 !bg-yellow-500"
        />
      </template>
    </NodeWrapper>
  </div>
</template>

<script setup>
import { reactive, computed, watch, onMounted } from 'vue'
import { Handle, Position } from '@vue-flow/core'
import NodeWrapper from '../ui/NodeWrapper.vue'
import { FlFilledLightbulbFilament } from '@kalimahapps/vue-icons'

const props = defineProps({
  id: String,
  data: Object,
  selected: Boolean,
})

const emit = defineEmits(['update-node'])

// Check if AI is enabled (from global window variable)
const aiEnabled = computed(() => {
  return window.isAiAssistantModuleEnabled || false
})

// Initialize node data - store selectedAssistantId and set mode to personal
// Also preserve output array if it exists
const nodeData = reactive({
  assistantMode: props.data?.assistantMode ?? 'personal', // Always use personal mode now
  selectedAssistantId: props.data?.selectedAssistantId ?? (window.personalAssistantsList && window.personalAssistantsList.length ? window.personalAssistantsList[0].id : null),
  output: props.data?.output || [], // Preserve output array
})

// Debug on mount
onMounted(() => {
  console.log('AIAssistantNode mounted')
  console.log('window.personalAssistantsList:', window.personalAssistantsList)
  console.log('props.data:', props.data)
  console.log('nodeData.selectedAssistantId:', nodeData.selectedAssistantId)
  
  // If no assistant selected but assistants are available, auto-select first one
  if (!nodeData.selectedAssistantId && window.personalAssistantsList && window.personalAssistantsList.length) {
    nodeData.selectedAssistantId = window.personalAssistantsList[0].id
  }
  
  // Emit the initial node data so it's saved in the flow
  updateNodeData()
})

const personalAssistants = computed(() => {
  const list = window.personalAssistantsList || []
  // Debug logging
  console.log('AIAssistantNode - personalAssistants:', list)
  console.log('AIAssistantNode - Count:', list.length)
  return list
})

// Personal assistant data
const personalAssistant = computed(() => {
  const list = personalAssistants.value
  if (list && list.length) {
    if (nodeData.selectedAssistantId) {
      const found = list.find(a => a.id === nodeData.selectedAssistantId)
      return found || list[0]
    }
    return list[0]
  }
  return null
})

// Update node data when changes occur
const updateNodeData = () => {
  // Ensure assistantMode is always 'personal' when saving
  nodeData.assistantMode = 'personal'
  
  // Build complete data object with all required properties
  const dataToEmit = {
    assistantMode: nodeData.assistantMode,
    selectedAssistantId: nodeData.selectedAssistantId,
    output: nodeData.output || props.data?.output || [], // Preserve output
    // Preserve any other existing properties from props.data
    ...(props.data || {}),
    // Override with our nodeData values
    assistantMode: nodeData.assistantMode,
    selectedAssistantId: nodeData.selectedAssistantId,
  }
  
  console.log('AIAssistantNode - Emitting update:', {
    nodeId: props.id,
    data: dataToEmit,
    hasSelectedAssistant: !!dataToEmit.selectedAssistantId,
    assistantMode: dataToEmit.assistantMode
  })
  
  emit('update-node', {
    id: props.id,
    data: dataToEmit
  })
}

// Watch for selectedAssistantId changes and save immediately
watch(() => nodeData.selectedAssistantId, (newId) => {
  if (newId) {
    console.log('AIAssistantNode - Assistant selected:', newId)
    updateNodeData()
  }
})

// Watch for external data changes (when flow is loaded)
watch(() => props.data, (newData) => {
  if (newData) {
    // Always set mode to personal
    nodeData.assistantMode = 'personal'
    
    // Update selectedAssistantId if provided
    if (newData.selectedAssistantId !== undefined && newData.selectedAssistantId !== null) {
      nodeData.selectedAssistantId = newData.selectedAssistantId
    } else if (!nodeData.selectedAssistantId && window.personalAssistantsList && window.personalAssistantsList.length) {
      // Auto-select first assistant if none selected
      nodeData.selectedAssistantId = window.personalAssistantsList[0].id
      // Emit update to save the auto-selected assistant
      updateNodeData()
    }
  }
}, { deep: true, immediate: true })
</script>

<style scoped>
.ai-assistant-node {
  min-width: 350px;
}

details[open] summary {
  margin-bottom: 0.75rem;
}
</style>

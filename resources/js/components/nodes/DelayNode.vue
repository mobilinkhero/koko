<template>
  <div class="delay-node">
    <NodeWrapper
      :id="id"
      :selected="selected"
      :type="'delay'"
      :title="'Delay'"
      :icon="ClockIcon"
      :color="'#6366F1'"
    >
      <template #content>
        <div class="space-y-4">
          <!-- Delay Type -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Delay Type
            </label>
            <select
              v-model="nodeData.delayType"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
              @change="updateNodeData"
            >
              <option value="fixed">Fixed Delay</option>
              <option value="random">Random Delay</option>
              <option value="typing">Typing Simulation</option>
              <option value="scheduled">Scheduled Time</option>
            </select>
          </div>

          <!-- Fixed Delay Settings -->
          <div v-if="nodeData.delayType === 'fixed'" class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Duration
                </label>
                <input
                  v-model.number="nodeData.duration"
                  type="number"
                  min="1"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                  @input="updateNodeData"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Unit
                </label>
                <select
                  v-model="nodeData.unit"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                  @change="updateNodeData"
                >
                  <option value="seconds">Seconds</option>
                  <option value="minutes">Minutes</option>
                  <option value="hours">Hours</option>
                  <option value="days">Days</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Random Delay Settings -->
          <div v-if="nodeData.delayType === 'random'" class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Min Duration
                </label>
                <input
                  v-model.number="nodeData.minDuration"
                  type="number"
                  min="1"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                  @input="updateNodeData"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Max Duration
                </label>
                <input
                  v-model.number="nodeData.maxDuration"
                  type="number"
                  min="1"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                  @input="updateNodeData"
                />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Unit
              </label>
              <select
                v-model="nodeData.unit"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                @change="updateNodeData"
              >
                <option value="seconds">Seconds</option>
                <option value="minutes">Minutes</option>
                <option value="hours">Hours</option>
              </select>
            </div>
          </div>

          <!-- Typing Simulation Settings -->
          <div v-if="nodeData.delayType === 'typing'" class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Typing Speed
              </label>
              <select
                v-model="nodeData.typingSpeed"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                @change="updateNodeData"
              >
                <option value="slow">Slow (40 WPM)</option>
                <option value="normal">Normal (60 WPM)</option>
                <option value="fast">Fast (80 WPM)</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Base Message Length (characters)
              </label>
              <input
                v-model.number="nodeData.messageLength"
                type="number"
                min="10"
                max="1000"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                placeholder="Estimated characters in next message"
                @input="updateNodeData"
              />
              <p class="text-xs text-gray-500 mt-1">
                Delay will be calculated based on typing speed and message length
              </p>
            </div>
          </div>

          <!-- Scheduled Time Settings -->
          <div v-if="nodeData.delayType === 'scheduled'" class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Schedule Type
              </label>
              <select
                v-model="nodeData.scheduleType"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                @change="updateNodeData"
              >
                <option value="specific_time">Specific Time</option>
                <option value="business_hours">Next Business Hours</option>
                <option value="relative">Relative to Now</option>
              </select>
            </div>

            <div v-if="nodeData.scheduleType === 'specific_time'" class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Time
                </label>
                <input
                  v-model="nodeData.scheduledTime"
                  type="time"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                  @input="updateNodeData"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Days
                </label>
                <select
                  v-model="nodeData.scheduledDays"
                  multiple
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                  @change="updateNodeData"
                >
                  <option value="monday">Monday</option>
                  <option value="tuesday">Tuesday</option>
                  <option value="wednesday">Wednesday</option>
                  <option value="thursday">Thursday</option>
                  <option value="friday">Friday</option>
                  <option value="saturday">Saturday</option>
                  <option value="sunday">Sunday</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Show Typing Indicator -->
          <div class="flex items-center">
            <input
              v-model="nodeData.showTyping"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              @change="updateNodeData"
            />
            <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">
              Show typing indicator during delay
            </label>
          </div>

          <!-- Delay Preview -->
          <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 border">
            <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Delay Preview:</p>
            <p class="text-sm text-gray-700 dark:text-gray-300">
              {{ getDelayDescription() }}
            </p>
          </div>

          <!-- Info -->
          <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3">
            <p class="text-xs text-blue-700 dark:text-blue-300">
              <strong>Delay Node:</strong> Adds realistic timing to conversations. 
              Use typing simulation for natural feel or fixed delays for timed sequences.
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
          class="w-3 h-3 !bg-indigo-500"
        />
      </template>
    </NodeWrapper>
  </div>
</template>

<script setup>
import { reactive, watch } from 'vue'
import { Handle, Position } from '@vue-flow/core'
import NodeWrapper from '../ui/NodeWrapper.vue'

// Clock icon component
const ClockIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
      <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/>
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
  delayType: props.data?.delayType || 'fixed',
  duration: props.data?.duration || 3,
  unit: props.data?.unit || 'seconds',
  minDuration: props.data?.minDuration || 1,
  maxDuration: props.data?.maxDuration || 5,
  typingSpeed: props.data?.typingSpeed || 'normal',
  messageLength: props.data?.messageLength || 100,
  scheduleType: props.data?.scheduleType || 'specific_time',
  scheduledTime: props.data?.scheduledTime || '09:00',
  scheduledDays: props.data?.scheduledDays || ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
  showTyping: props.data?.showTyping !== undefined ? props.data.showTyping : true,
})

// Get delay description for preview
const getDelayDescription = () => {
  switch (nodeData.delayType) {
    case 'fixed':
      return `Wait ${nodeData.duration} ${nodeData.unit}`
    case 'random':
      return `Wait ${nodeData.minDuration}-${nodeData.maxDuration} ${nodeData.unit}`
    case 'typing':
      const wpm = nodeData.typingSpeed === 'slow' ? 40 : nodeData.typingSpeed === 'fast' ? 80 : 60
      const estimatedSeconds = Math.ceil((nodeData.messageLength / 5) / (wpm / 60))
      return `Simulate typing for ~${estimatedSeconds} seconds (${nodeData.typingSpeed} speed)`
    case 'scheduled':
      if (nodeData.scheduleType === 'business_hours') {
        return 'Wait until next business hours'
      } else if (nodeData.scheduleType === 'specific_time') {
        return `Wait until ${nodeData.scheduledTime} on ${nodeData.scheduledDays.join(', ')}`
      }
      return 'Scheduled delay'
    default:
      return 'Delay configured'
  }
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
    Object.assign(nodeData, newData)
  }
}, { deep: true })
</script>

<style scoped>
.delay-node {
  min-width: 320px;
}
</style>

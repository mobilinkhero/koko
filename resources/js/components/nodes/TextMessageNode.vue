<template>
  <div class="text-message-node">
    <NodeWrapper
      :id="id"
      :selected="selected"
      :type="'textMessage'"
      :title="'Text Message'"
      :icon="BsChatRightQuote"
      :color="'#10B981'"
    >
      <template #content>
        <div class="space-y-3">
          <!-- Message Text -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Message Text
            </label>
            <textarea
              v-model="nodeData.output[0].reply_text"
              rows="4"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white resize-none"
              placeholder="Enter your message text here..."
              @input="updateNodeData"
            />
            <p class="text-xs text-gray-500 mt-1">
              Use variables like {{contact_name}}, {{contact_phone}}, etc.
            </p>
          </div>

          <!-- Header (Optional) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Header (Optional)
            </label>
            <input
              v-model="nodeData.output[0].bot_header"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
              placeholder="Message header..."
              @input="updateNodeData"
            />
          </div>

          <!-- Footer (Optional) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Footer (Optional)
            </label>
            <input
              v-model="nodeData.output[0].bot_footer"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
              placeholder="Message footer..."
              @input="updateNodeData"
            />
          </div>

          <!-- Preview -->
          <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 border">
            <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Preview:</p>
            <div class="bg-white dark:bg-gray-700 rounded-lg p-3 shadow-sm">
              <div v-if="nodeData.output[0].bot_header" class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">
                {{ nodeData.output[0].bot_header }}
              </div>
              <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                {{ nodeData.output[0].reply_text || 'Enter message text...' }}
              </div>
              <div v-if="nodeData.output[0].bot_footer" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                {{ nodeData.output[0].bot_footer }}
              </div>
            </div>
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
          class="w-3 h-3 !bg-green-500"
        />
      </template>
    </NodeWrapper>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { Handle, Position } from '@vue-flow/core'
import NodeWrapper from '../ui/NodeWrapper.vue'
import { BsChatRightQuote } from '@kalimahapps/vue-icons'

const props = defineProps({
  id: String,
  data: Object,
  selected: Boolean,
})

const emit = defineEmits(['update-node'])

// Initialize node data with default structure
const nodeData = reactive({
  output: [
    {
      reply_text: props.data?.output?.[0]?.reply_text || '',
      bot_header: props.data?.output?.[0]?.bot_header || '',
      bot_footer: props.data?.output?.[0]?.bot_footer || '',
    }
  ]
})

// Update node data when changes occur
const updateNodeData = () => {
  emit('update-node', {
    id: props.id,
    data: { ...nodeData }
  })
}

// Watch for external data changes
watch(() => props.data, (newData) => {
  if (newData?.output?.[0]) {
    Object.assign(nodeData.output[0], newData.output[0])
  }
}, { deep: true })
</script>

<style scoped>
.text-message-node {
  min-width: 300px;
}
</style>

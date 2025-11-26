<template>
  <div class="trigger-node">
    <NodeWrapper
      :id="id"
      :selected="selected"
      :type="'trigger'"
      :title="'Flow Trigger'"
      :icon="FlFilledLightbulbFilament"
      :color="'#8B5CF6'"
    >
      <template #content>
        <div class="space-y-4">
          <!-- Trigger Rules -->
          <div v-for="(rule, index) in nodeData.output" :key="index" class="border border-gray-200 dark:border-gray-600 rounded-lg p-3">
            <div class="flex justify-between items-center mb-3">
              <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Rule {{ index + 1 }}
              </h4>
              <button
                v-if="nodeData.output.length > 1"
                @click="removeRule(index)"
                class="text-red-500 hover:text-red-700 text-sm"
              >
                Remove
              </button>
            </div>

            <!-- Relation Type -->
            <div class="mb-3">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                User Type
              </label>
              <select
                v-model="rule.rel_type"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                @change="updateNodeData"
              >
                <option value="">All Users</option>
                <option value="guest">Guest</option>
                <option value="lead">Lead</option>
                <option value="customer">Customer</option>
                <option value="vip">VIP</option>
              </select>
            </div>

            <!-- Reply Type -->
            <div class="mb-3">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Trigger Type
              </label>
              <select
                v-model="rule.reply_type"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                @change="updateNodeData"
              >
                <option :value="1">Exact Match</option>
                <option :value="2">Contains</option>
                <option :value="3">First Time User</option>
                <option :value="4">Fallback (Any Message)</option>
              </select>
            </div>

            <!-- Trigger Keywords (for exact match and contains) -->
            <div v-if="rule.reply_type === 1 || rule.reply_type === 2" class="mb-3">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Keywords
              </label>
              <input
                v-model="rule.trigger"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                placeholder="hello, hi, start (comma separated)"
                @input="updateNodeData"
              />
              <p class="text-xs text-gray-500 mt-1">
                Separate multiple keywords with commas
              </p>
            </div>

            <!-- Description for special types -->
            <div v-if="rule.reply_type === 3" class="text-sm text-blue-600 dark:text-blue-400">
              This rule triggers for users contacting for the first time
            </div>
            <div v-if="rule.reply_type === 4" class="text-sm text-orange-600 dark:text-orange-400">
              This rule triggers for any message that doesn't match other rules
            </div>
          </div>

          <!-- Add Rule Button -->
          <button
            @click="addRule"
            class="w-full py-2 px-4 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-600 dark:text-gray-400 hover:border-blue-500 hover:text-blue-500 transition-colors"
          >
            + Add Another Rule
          </button>

          <!-- Flow Info -->
          <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3">
            <p class="text-xs text-blue-700 dark:text-blue-300">
              <strong>Trigger Node:</strong> This is the starting point of your flow. 
              Configure when this flow should activate based on user messages.
            </p>
          </div>
        </div>
      </template>

      <template #handles>
        <Handle
          id="output"
          type="source"
          position="right"
          class="w-3 h-3 !bg-purple-500"
        />
      </template>
    </NodeWrapper>
  </div>
</template>

<script setup>
import { reactive, watch } from 'vue'
import { Handle, Position } from '@vue-flow/core'
import NodeWrapper from '../ui/NodeWrapper.vue'
import { FlFilledLightbulbFilament } from '@kalimahapps/vue-icons'

const props = defineProps({
  id: String,
  data: Object,
  selected: Boolean,
})

const emit = defineEmits(['update-node'])

// Initialize node data with default structure
const nodeData = reactive({
  output: props.data?.output || [
    {
      rel_type: '',
      reply_type: 1,
      trigger: '',
    }
  ]
})

// Add new rule
const addRule = () => {
  nodeData.output.push({
    rel_type: '',
    reply_type: 1,
    trigger: '',
  })
  updateNodeData()
}

// Remove rule
const removeRule = (index) => {
  nodeData.output.splice(index, 1)
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
  if (newData?.output) {
    nodeData.output = [...newData.output]
  }
}, { deep: true })
</script>

<style scoped>
.trigger-node {
  min-width: 320px;
}
</style>

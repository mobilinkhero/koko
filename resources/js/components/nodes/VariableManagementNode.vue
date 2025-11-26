<template>
  <div class="variable-management-node">
    <NodeWrapper
      :id="id"
      :selected="selected"
      title="Variable Manager"
      :icon="IconComponent"
      :data="nodeData"
    >
      <template #content>
        <div class="space-y-3">
          <!-- Action Type -->
          <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
              Action *
            </label>
            <select
              v-model="nodeData.action"
              @change="updateNode"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-blue-500"
            >
              <option value="set">Set Variable</option>
              <option value="clear">Clear Variable</option>
              <option value="increment">Increment (Numbers)</option>
              <option value="append">Append (Text)</option>
            </select>
          </div>

          <!-- Variable Name -->
          <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
              Variable Name *
            </label>
            <input
              v-model="nodeData.variableName"
              @input="updateNode"
              type="text"
              placeholder="e.g., user_score, order_id"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-blue-500"
            />
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              Use lowercase with underscores. Will be accessible as <span v-text="`{{${nodeData.variableName || 'variable_name'}}}`"></span>
            </p>
          </div>

          <!-- Variable Value (for set/increment/append) -->
          <div v-if="nodeData.action !== 'clear'">
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
              Value *
            </label>
            <textarea
              v-model="nodeData.value"
              @input="updateNode"
              rows="2"
              :placeholder="getValuePlaceholder()"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white resize-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              You can use other variables: <span v-text="'{{contact_name}}, {{last_message}}'"></span>, etc.
            </p>
          </div>

          <!-- Variable Scope -->
          <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
              Scope
            </label>
            <select
              v-model="nodeData.scope"
              @change="updateNode"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-blue-500"
            >
              <option value="contact">Contact (Per User)</option>
              <option value="flow">Flow (This Flow Only)</option>
              <option value="global">Global (All Flows)</option>
            </select>
          </div>

          <!-- Expiration -->
          <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
              Expiration
            </label>
            <div class="flex gap-2">
              <input
                v-model.number="nodeData.expirationValue"
                @input="updateNode"
                type="number"
                min="0"
                placeholder="0 = Never"
                class="w-24 px-3 py-2 text-sm border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-blue-500"
              />
              <select
                v-model="nodeData.expirationUnit"
                @change="updateNode"
                class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              >
                <option value="never">Never Expires</option>
                <option value="minutes">Minutes</option>
                <option value="hours">Hours</option>
                <option value="days">Days</option>
                <option value="weeks">Weeks</option>
              </select>
            </div>
          </div>

          <!-- Settings -->
          <div class="border-t border-gray-200 dark:border-gray-600 pt-3 space-y-2">
            <div class="flex items-center justify-between">
              <label class="text-xs font-medium text-gray-700 dark:text-gray-300">
                Encrypt sensitive data
              </label>
              <input
                v-model="nodeData.encrypt"
                @change="updateNode"
                type="checkbox"
                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
            </div>
            <div class="flex items-center justify-between">
              <label class="text-xs font-medium text-gray-700 dark:text-gray-300">
                Log variable changes
              </label>
              <input
                v-model="nodeData.logChanges"
                @change="updateNode"
                type="checkbox"
                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
            </div>
          </div>

          <!-- Preview -->
          <div class="border-t border-gray-200 dark:border-gray-600 pt-3">
            <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Preview</p>
            <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-md border border-gray-200 dark:border-gray-600 font-mono text-xs">
              <div class="space-y-1">
                <div class="text-gray-700 dark:text-gray-300">
                  <span class="text-purple-600 dark:text-purple-400">{{ nodeData.action }}</span>
                  <span class="text-gray-500">:</span>
                </div>
                <div class="pl-4 text-gray-600 dark:text-gray-400">
                  <span class="text-blue-600 dark:text-blue-400">{{ nodeData.variableName || 'variable_name' }}</span>
                  <span v-if="nodeData.action !== 'clear'"> = </span>
                  <span v-if="nodeData.action !== 'clear'" class="text-green-600 dark:text-green-400">
                    "{{ nodeData.value || 'value' }}"
                  </span>
                </div>
                <div class="pl-4 text-gray-500 dark:text-gray-500 text-xs">
                  scope: {{ nodeData.scope }} | 
                  expires: {{ getExpirationText() }}
                </div>
              </div>
            </div>
          </div>

          <!-- Info -->
          <div class="bg-blue-50 dark:bg-blue-900 p-2 rounded-md">
            <p class="text-xs text-blue-700 dark:text-blue-300">
              <strong>Variable Manager:</strong> Store and manipulate data throughout your flows. 
              Variables persist across sessions based on scope.
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
          class="w-3 h-3 !bg-purple-500"
        />
      </template>
    </NodeWrapper>
  </div>
</template>

<script setup>
import { reactive, watch, h } from 'vue'
import { Handle } from '@vue-flow/core'
import NodeWrapper from '../ui/NodeWrapper.vue'
import { HiDocumentText } from '@kalimahapps/vue-icons'

const IconComponent = {
  render: () => h(HiDocumentText, { class: 'w-5 h-5' })
}

const props = defineProps({
  id: String,
  data: Object,
  selected: Boolean,
})

const emit = defineEmits(['update-node'])

// Initialize node data
const nodeData = reactive({
  action: props.data?.action || 'set',
  variableName: props.data?.variableName || '',
  value: props.data?.value || '',
  scope: props.data?.scope || 'contact',
  expirationValue: props.data?.expirationValue || 0,
  expirationUnit: props.data?.expirationUnit || 'never',
  encrypt: props.data?.encrypt ?? false,
  logChanges: props.data?.logChanges ?? true,
  isValid: true
})

function getValuePlaceholder() {
  const placeholders = {
    set: 'Enter value or use {{variables}}',
    increment: 'Enter number to add (e.g., 1)',
    append: 'Text to append'
  }
  return placeholders[nodeData.action] || 'Enter value'
}

function getExpirationText() {
  if (nodeData.expirationUnit === 'never' || nodeData.expirationValue === 0) {
    return 'never'
  }
  return `${nodeData.expirationValue} ${nodeData.expirationUnit}`
}

function updateNode() {
  // Validate variable name and value
  const hasName = nodeData.variableName.trim().length > 0
  const hasValue = nodeData.action === 'clear' || nodeData.value.trim().length > 0
  
  nodeData.isValid = hasName && hasValue

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
.variable-management-node {
  min-width: 320px;
}
</style>

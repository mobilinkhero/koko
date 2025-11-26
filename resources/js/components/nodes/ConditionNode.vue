<template>
  <div class="condition-node">
    <NodeWrapper
      :id="id"
      :selected="selected"
      :type="'condition'"
      :title="'Condition'"
      :icon="BsMenuButtonFill"
      :color="'#EF4444'"
    >
      <template #content>
        <div class="space-y-4">
          <!-- Condition Rules -->
          <div v-for="(condition, index) in nodeData.conditions" :key="index" class="border border-gray-200 dark:border-gray-600 rounded-lg p-3">
            <div class="flex justify-between items-center mb-3">
              <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Condition {{ index + 1 }}
              </h4>
              <button
                v-if="nodeData.conditions.length > 1"
                @click="removeCondition(index)"
                class="text-red-500 hover:text-red-700 text-sm"
              >
                Remove
              </button>
            </div>

            <!-- Variable/Field -->
            <div class="mb-3">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Variable/Field
              </label>
              <select
                v-model="condition.variable"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                @change="updateNodeData"
              >
                <option value="">Select Variable</option>
                <optgroup label="Contact Variables">
                  <option value="contact_name">Contact Name</option>
                  <option value="contact_phone">Contact Phone</option>
                  <option value="contact_email">Contact Email</option>
                  <option value="contact_type">Contact Type</option>
                </optgroup>
                <optgroup label="System Variables">
                  <option value="current_time">Current Time</option>
                  <option value="day_of_week">Day of Week</option>
                  <option value="business_hours">Business Hours</option>
                  <option value="user_message">User Message</option>
                </optgroup>
                <optgroup label="Custom Variables">
                  <option value="custom_variable">Custom Variable</option>
                </optgroup>
              </select>
            </div>

            <!-- Custom Variable Name (if custom selected) -->
            <div v-if="condition.variable === 'custom_variable'" class="mb-3">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Custom Variable Name
              </label>
              <input
                v-model="condition.customVariable"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                placeholder="variable_name"
                @input="updateNodeData"
              />
            </div>

            <!-- Operator -->
            <div class="mb-3">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Operator
              </label>
              <select
                v-model="condition.operator"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                @change="updateNodeData"
              >
                <option value="equals">Equals</option>
                <option value="not_equals">Not Equals</option>
                <option value="contains">Contains</option>
                <option value="not_contains">Does Not Contain</option>
                <option value="starts_with">Starts With</option>
                <option value="ends_with">Ends With</option>
                <option value="greater_than">Greater Than</option>
                <option value="less_than">Less Than</option>
                <option value="is_empty">Is Empty</option>
                <option value="is_not_empty">Is Not Empty</option>
              </select>
            </div>

            <!-- Value (if not empty/not empty operators) -->
            <div v-if="!['is_empty', 'is_not_empty'].includes(condition.operator)" class="mb-3">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Value
              </label>
              <input
                v-model="condition.value"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                placeholder="Enter comparison value..."
                @input="updateNodeData"
              />
            </div>

            <!-- Logic Operator (for multiple conditions) -->
            <div v-if="index < nodeData.conditions.length - 1" class="mb-3">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Logic
              </label>
              <select
                v-model="condition.logic"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                @change="updateNodeData"
              >
                <option value="AND">AND</option>
                <option value="OR">OR</option>
              </select>
            </div>
          </div>

          <!-- Add Condition Button -->
          <button
            @click="addCondition"
            class="w-full py-2 px-4 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-600 dark:text-gray-400 hover:border-blue-500 hover:text-blue-500 transition-colors"
          >
            + Add Another Condition
          </button>

          <!-- Default Action -->
          <div class="border-t border-gray-200 dark:border-gray-600 pt-3">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Default Action (if conditions fail)
            </label>
            <select
              v-model="nodeData.defaultAction"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
              @change="updateNodeData"
            >
              <option value="continue">Continue to Default Output</option>
              <option value="stop">Stop Flow</option>
              <option value="fallback">Go to Fallback Flow</option>
            </select>
          </div>

          <!-- Info -->
          <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3">
            <p class="text-xs text-blue-700 dark:text-blue-300">
              <strong>Condition Node:</strong> Routes users to different paths based on conditions. 
              Connect the "True" output for when conditions match, and "False" for when they don't.
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
          id="true"
          type="source"
          position="right"
          :style="{ top: '30%' }"
          class="w-3 h-3 !bg-green-500"
        />
        <Handle
          id="false"
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
import { BsMenuButtonFill } from '@kalimahapps/vue-icons'

const props = defineProps({
  id: String,
  data: Object,
  selected: Boolean,
})

const emit = defineEmits(['update-node'])

// Initialize node data with default structure
const nodeData = reactive({
  conditions: props.data?.conditions || [
    {
      variable: '',
      customVariable: '',
      operator: 'equals',
      value: '',
      logic: 'AND'
    }
  ],
  defaultAction: props.data?.defaultAction || 'continue'
})

// Add new condition
const addCondition = () => {
  nodeData.conditions.push({
    variable: '',
    customVariable: '',
    operator: 'equals',
    value: '',
    logic: 'AND'
  })
  updateNodeData()
}

// Remove condition
const removeCondition = (index) => {
  nodeData.conditions.splice(index, 1)
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
      conditions: newData.conditions || nodeData.conditions,
      defaultAction: newData.defaultAction || 'continue'
    })
  }
}, { deep: true })
</script>

<style scoped>
.condition-node {
  min-width: 350px;
}
</style>

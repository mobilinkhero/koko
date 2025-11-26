<template>
  <div class="tag-management-node">
    <NodeWrapper
      :id="id"
      :selected="selected"
      title="Tag Management"
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
              <option value="add">Add Tags</option>
              <option value="remove">Remove Tags</option>
              <option value="replace">Replace All Tags</option>
              <option value="conditional">Conditional Tagging</option>
            </select>
          </div>

          <!-- Tags Input -->
          <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
              Tags *
            </label>
            <div class="space-y-2">
              <div 
                v-for="(tag, index) in nodeData.tags" 
                :key="index"
                class="flex items-center gap-2"
              >
                <input
                  v-model="tag.name"
                  @input="updateNode"
                  type="text"
                  :placeholder="`Tag ${index + 1}`"
                  class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-blue-500"
                />
                <select
                  v-if="nodeData.action === 'conditional'"
                  v-model="tag.color"
                  class="px-2 py-2 text-sm border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                >
                  <option value="blue">🔵 Blue</option>
                  <option value="green">🟢 Green</option>
                  <option value="red">🔴 Red</option>
                  <option value="yellow">🟡 Yellow</option>
                  <option value="purple">🟣 Purple</option>
                </select>
                <button
                  @click="removeTag(index)"
                  v-if="nodeData.tags.length > 1"
                  class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900 rounded-md transition-colors"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </div>
            </div>
            <button
              @click="addTag"
              class="mt-2 w-full px-3 py-2 text-sm text-blue-600 border border-blue-300 rounded-md hover:bg-blue-50 dark:hover:bg-blue-900 dark:border-blue-600 dark:text-blue-400 transition-colors"
            >
              + Add Tag
            </button>
          </div>

          <!-- Conditional Rules (if action is conditional) -->
          <div v-if="nodeData.action === 'conditional'" class="border-t border-gray-200 dark:border-gray-600 pt-3">
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
              Tagging Conditions
            </label>
            <div class="space-y-2">
              <div 
                v-for="(condition, index) in nodeData.conditions" 
                :key="index"
                class="p-2 bg-gray-50 dark:bg-gray-800 rounded-md space-y-2"
              >
                <div class="flex gap-2">
                  <select
                    v-model="condition.variable"
                    @change="updateNode"
                    class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                  >
                    <option value="">Select variable...</option>
                    <option value="contact_name">Contact Name</option>
                    <option value="contact_type">Contact Type</option>
                    <option value="last_message">Last Message</option>
                    <option value="message_count">Message Count</option>
                    <option value="custom">Custom Variable</option>
                  </select>
                  <select
                    v-model="condition.operator"
                    @change="updateNode"
                    class="px-2 py-1 text-xs border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                  >
                    <option value="equals">Equals</option>
                    <option value="contains">Contains</option>
                    <option value="greater_than">Greater Than</option>
                    <option value="less_than">Less Than</option>
                  </select>
                  <button
                    @click="removeCondition(index)"
                    class="p-1 text-red-600 hover:bg-red-50 dark:hover:bg-red-900 rounded"
                  >
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                  </button>
                </div>
                <input
                  v-model="condition.value"
                  @input="updateNode"
                  type="text"
                  placeholder="Value to compare"
                  class="w-full px-2 py-1 text-xs border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                />
                <input
                  v-model="condition.tagToApply"
                  @input="updateNode"
                  type="text"
                  placeholder="Tag to apply if true"
                  class="w-full px-2 py-1 text-xs border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                />
              </div>
            </div>
            <button
              @click="addCondition"
              class="mt-2 w-full px-2 py-1 text-xs text-blue-600 border border-blue-300 rounded-md hover:bg-blue-50 dark:hover:bg-blue-900"
            >
              + Add Condition
            </button>
          </div>

          <!-- Settings -->
          <div class="border-t border-gray-200 dark:border-gray-600 pt-3 space-y-2">
            <div class="flex items-center justify-between">
              <label class="text-xs font-medium text-gray-700 dark:text-gray-300">
                Create tags if not exist
              </label>
              <input
                v-model="nodeData.createIfNotExist"
                @change="updateNode"
                type="checkbox"
                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
            </div>
            <div class="flex items-center justify-between">
              <label class="text-xs font-medium text-gray-700 dark:text-gray-300">
                Log tagging activity
              </label>
              <input
                v-model="nodeData.logActivity"
                @change="updateNode"
                type="checkbox"
                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
            </div>
          </div>

          <!-- Preview -->
          <div class="border-t border-gray-200 dark:border-gray-600 pt-3">
            <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Preview</p>
            <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-md border border-gray-200 dark:border-gray-600">
              <p class="text-xs text-gray-700 dark:text-gray-300 mb-2">
                <strong>Action:</strong> {{ nodeData.action.charAt(0).toUpperCase() + nodeData.action.slice(1) }}
              </p>
              <div class="flex flex-wrap gap-1">
                <span
                  v-for="(tag, index) in nodeData.tags.filter(t => t.name)"
                  :key="index"
                  :class="[
                    'inline-block px-2 py-1 text-xs rounded-full',
                    getTagColorClass(tag.color)
                  ]"
                >
                  🏷️ {{ tag.name }}
                </span>
              </div>
              <p v-if="nodeData.tags.filter(t => t.name).length === 0" class="text-xs text-gray-500 dark:text-gray-400">
                No tags configured
              </p>
            </div>
          </div>

          <!-- Info -->
          <div class="bg-blue-50 dark:bg-blue-900 p-2 rounded-md">
            <p class="text-xs text-blue-700 dark:text-blue-300">
              <strong>Tag Management:</strong> Automatically segment users by adding or removing tags based on their interactions.
              Tags can be used for targeted campaigns and analytics.
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
          :style="{ top: '40%' }"
          class="w-3 h-3 !bg-green-500"
        />
        <Handle
          v-if="nodeData.action === 'conditional'"
          id="no_match"
          type="source"
          position="right"
          :style="{ top: '60%' }"
          class="w-3 h-3 !bg-gray-500"
        />
      </template>
    </NodeWrapper>
  </div>
</template>

<script setup>
import { reactive, watch, h } from 'vue'
import { Handle } from '@vue-flow/core'
import NodeWrapper from '../ui/NodeWrapper.vue'
import { BsTagsFill } from '@kalimahapps/vue-icons'

const IconComponent = {
  render: () => h(BsTagsFill, { class: 'w-5 h-5' })
}

const props = defineProps({
  id: String,
  data: Object,
  selected: Boolean,
})

const emit = defineEmits(['update-node'])

// Initialize node data
const nodeData = reactive({
  action: props.data?.action || 'add',
  tags: props.data?.tags || [{ name: '', color: 'blue' }],
  conditions: props.data?.conditions || [],
  createIfNotExist: props.data?.createIfNotExist ?? true,
  logActivity: props.data?.logActivity ?? true,
  isValid: true
})

function addTag() {
  nodeData.tags.push({ name: '', color: 'blue' })
  updateNode()
}

function removeTag(index) {
  if (nodeData.tags.length > 1) {
    nodeData.tags.splice(index, 1)
    updateNode()
  }
}

function addCondition() {
  nodeData.conditions.push({
    variable: '',
    operator: 'equals',
    value: '',
    tagToApply: ''
  })
  updateNode()
}

function removeCondition(index) {
  nodeData.conditions.splice(index, 1)
  updateNode()
}

function getTagColorClass(color) {
  const colors = {
    blue: 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
    green: 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
    red: 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
    yellow: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
    purple: 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300'
  }
  return colors[color] || colors.blue
}

function updateNode() {
  // Validate that at least one tag has a name
  const hasTags = nodeData.tags.some(t => t.name.trim().length > 0)
  nodeData.isValid = hasTags

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
.tag-management-node {
  min-width: 320px;
}
</style>

<template>
  <div 
    :class="[
      'node-wrapper',
      'bg-white dark:bg-gray-800',
      'border-2 rounded-lg shadow-lg',
      'transition-all duration-200',
      selected ? 'border-blue-500 shadow-blue-200' : 'border-gray-200 dark:border-gray-600',
      'hover:shadow-xl'
    ]"
    :style="{ borderColor: selected ? color : undefined }"
  >
    <!-- Node Header -->
    <div 
      class="flex items-center px-4 py-3 border-b border-gray-200 dark:border-gray-600"
      :style="{ backgroundColor: selected ? `${color}15` : undefined }"
    >
      <div 
        class="flex items-center justify-center w-8 h-8 rounded-full mr-3"
        :style="{ backgroundColor: `${color}20`, color: color }"
      >
        <component :is="icon" class="w-5 h-5" />
      </div>
      <div class="flex-1">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
          {{ title }}
        </h3>
        <p class="text-xs text-gray-500 dark:text-gray-400">
          {{ type }}
        </p>
      </div>
      <div class="flex items-center space-x-2">
        <!-- Node Status Indicator -->
        <div 
          :class="[
            'w-2 h-2 rounded-full',
            nodeStatus === 'active' ? 'bg-green-500' : 
            nodeStatus === 'error' ? 'bg-red-500' : 
            'bg-gray-400'
          ]"
        />
        <!-- Node Menu (optional) -->
        <button 
          v-if="showMenu"
          class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded"
          @click="$emit('menu-click')"
        >
          <DotsVerticalIcon class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Node Content -->
    <div class="p-4">
      <slot name="content" />
    </div>

    <!-- Node Handles -->
    <slot name="handles" />
  </div>
</template>

<script setup>
import { computed } from 'vue'

// Dots vertical icon component
const DotsVerticalIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
      <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
    </svg>
  `
}

const props = defineProps({
  id: {
    type: String,
    required: true
  },
  selected: {
    type: Boolean,
    default: false
  },
  type: {
    type: String,
    required: true
  },
  title: {
    type: String,
    required: true
  },
  icon: {
    type: [Object, String],
    required: true
  },
  color: {
    type: String,
    default: '#6B7280'
  },
  status: {
    type: String,
    default: 'inactive',
    validator: (value) => ['active', 'inactive', 'error'].includes(value)
  },
  showMenu: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['menu-click'])

const nodeStatus = computed(() => props.status)
</script>

<style scoped>
.node-wrapper {
  min-width: 250px;
  max-width: 400px;
  position: relative;
}

.node-wrapper:hover {
  transform: translateY(-1px);
}

/* Handle positioning adjustments */
.node-wrapper :deep(.vue-flow__handle) {
  width: 12px;
  height: 12px;
  border: 2px solid white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  cursor: crosshair;
  opacity: 1;
  z-index: 10;
}

.node-wrapper :deep(.vue-flow__handle:hover) {
  transform: scale(1.2);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.node-wrapper :deep(.vue-flow__handle-left) {
  left: -6px;
}

.node-wrapper :deep(.vue-flow__handle-right) {
  right: -6px;
}

.node-wrapper :deep(.vue-flow__handle-top) {
  top: -6px;
}

.node-wrapper :deep(.vue-flow__handle-bottom) {
  bottom: -6px;
}

.node-wrapper :deep(.vue-flow__handle-connecting) {
  background: #3b82f6 !important;
  transform: scale(1.3);
}
</style>

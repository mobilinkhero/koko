<template>
  <BaseEdge
    :id="id"
    :style="style"
    :path="path[0]"
    :marker-end="markerEnd"
    :marker-start="markerStart"
    class="custom-edge"
  />
</template>

<script setup>
import { computed } from 'vue'
import { BaseEdge, getBezierPath } from '@vue-flow/core'

const props = defineProps({
  id: String,
  sourceX: Number,
  sourceY: Number,
  targetX: Number,
  targetY: Number,
  sourcePosition: String,
  targetPosition: String,
  style: Object,
  markerEnd: String,
  markerStart: String,
})

const path = computed(() => 
  getBezierPath({
    sourceX: props.sourceX,
    sourceY: props.sourceY,
    sourcePosition: props.sourcePosition,
    targetX: props.targetX,
    targetY: props.targetY,
    targetPosition: props.targetPosition,
  })
)
</script>

<style scoped>
.custom-edge {
  stroke: #b1b1b7;
  stroke-width: 2;
}

.custom-edge:hover {
  stroke: #3b82f6;
  stroke-width: 3;
}
</style>

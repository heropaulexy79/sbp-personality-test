<script setup lang="ts">
import { useToast } from './use-toast'
import Toast from './Toast.vue'

// Destructure the global state (toasts) and the dismissal function
const { toasts, dismiss } = useToast()
</script>

<template>
  <!-- Fixed position container for all toasts (top-right corner) -->
  <div class="fixed top-0 right-0 z-[100] w-full max-w-sm p-4 pointer-events-none">
    <div class="space-y-4">
      <!-- 
        Iterate over the global 'toasts' state.
        When an individual Toast component emits 'update:open' with 'false', 
        we call dismiss(toast.id) from the useToast composable to clean up 
        the global state after the exit animation completes (as handled inside Toast.vue).
      -->
      <Toast
        v-for="toast in toasts"
        :key="toast.id"
        :toast="toast"
        @update:open="value => !value && dismiss(toast.id)"
      />
    </div>
  </div>
</template>
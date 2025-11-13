<script setup lang="ts">
import { defineProps, ref, watch } from 'vue';
import type { ToastState } from './types';
import { Button } from '@/Components/ui/button';
import { X } from 'lucide-vue-next'; 

const props = defineProps<{
  toast: ToastState;
}>();

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void;
}>();

const localOpen = ref(props.toast.open);

// Watch for changes to the 'open' prop (triggered by auto-dismiss or manual dismiss)
watch(
  () => props.toast.open,
  (newVal) => {
    localOpen.value = newVal;
    if (!newVal) {
      // Delay emitting the final dismiss signal to allow the CSS transition to complete
      setTimeout(() => {
        // Emit false to signal the Toaster to remove this toast from its array
        emit('update:open', false);
      }, 300); 
    }
  }
);

const handleClose = () => {
    // Setting localOpen to false initiates the fade-out transition
    localOpen.value = false;
    // The state management in use-toast will handle the global dismissal logic.
}
</script>

<template>
  <Transition
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="translate-x-full opacity-0"
    enter-to-class="translate-x-0 opacity-100"
    leave-active-class="transition duration-300 ease-in"
    leave-from-class="translate-x-0 opacity-100"
    leave-to-class="translate-x-full opacity-0"
  >
    <div
      v-if="localOpen"
      :class="[
        'relative w-full overflow-hidden rounded-md border p-4 shadow-xl transition-all',
        'bg-white text-gray-900 dark:bg-gray-900 dark:text-white', 
      ]"
      :aria-live="props.toast.duration === 0 ? 'assertive' : 'polite'"
      :role="props.toast.duration === 0 ? 'alert' : 'status'"
    >
      <div class="grid grid-cols-[auto_max-content] gap-2">
        <!-- Content -->
        <div class="flex flex-col space-y-1">
          <div class="text-sm font-semibold">{{ props.toast.title }}</div>
          <p v-if="props.toast.description" class="text-sm opacity-90 text-gray-700 dark:text-gray-300">
            {{ props.toast.description }}
          </p>
        </div>

        <!-- Action/Close Button -->
        <div class="flex items-center">
          <Button
            v-if="props.toast.action"
            size="sm"
            variant="ghost"
            class="text-xs font-medium px-3 py-1 mr-2 text-primary hover:bg-primary/10"
            @click="props.toast.action.onClick"
          >
            {{ props.toast.action.label }}
          </Button>
          
          <button
            class="h-5 w-5 rounded-full text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors"
            @click="handleClose"
            aria-label="Close"
          >
            <X class="h-4 w-4" />
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>
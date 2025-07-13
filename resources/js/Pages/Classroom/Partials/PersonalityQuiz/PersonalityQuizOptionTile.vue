<script lang="ts" setup>
import { computed } from "vue";
import { cn } from "@/lib/utils";

const props = defineProps<{
  value: string;
  modelValue: string | undefined;
  disabled?: boolean;
}>();

const emit = defineEmits(["update:modelValue"]);

const isSelected = computed(() => props.modelValue === props.value);

const handleClick = () => {
  if (!props.disabled) {
    emit("update:modelValue", props.value); // Emit this tile's value to update the parent's v-model
  }
};
</script>

<template>
  <button
    type="button"
    :class="
      cn(
        'flex min-h-[100px] cursor-pointer items-center justify-center rounded-lg border-2 p-4 text-center transition-all duration-200',
        'text-lg font-semibold',
        props.disabled
          ? 'cursor-not-allowed opacity-70' // Disabled state
          : 'hover:bg-primary/10 hover:border-primary', // Hover state when not disabled
        isSelected
          ? 'bg-primary text-primary-foreground border-primary shadow-lg' // Selected state
          : 'border-gray-300 bg-white text-gray-800 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100', // Default state
      )
    "
    @click="handleClick"
  >
    <slot />
  </button>
</template>

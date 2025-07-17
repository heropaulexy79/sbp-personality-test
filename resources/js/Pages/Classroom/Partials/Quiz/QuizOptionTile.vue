<script lang="ts" setup>
import { computed } from "vue";
import { cn } from "@/lib/utils";

const props = defineProps<{
  value: string;
  modelValue: string | string[] | undefined;
  disabled?: boolean;
  isCorrectOption?: boolean;
  isQuizCompleted?: boolean;
}>();

const emit = defineEmits(["update:modelValue"]);

const isSelectedByUser = computed(() => props.modelValue === props.value);

const handleClick = () => {
  if (!props.disabled) {
    emit("update:modelValue", props.value);
  }
};

const tileClasses = computed(() => {
  let classes = [
    "flex items-center justify-center min-h-[70px] text-center p-3 border-2 rounded-lg cursor-pointer transition-all duration-200",
    "font-semibold text-base",
  ];

  if (props.disabled) {
    classes.push("cursor-not-allowed opacity-70");
    if (isSelectedByUser.value) {
      if (props.isCorrectOption) {
        classes.push(
          "bg-green-100 border-green-500 text-green-800 dark:bg-green-900 dark:border-green-600 dark:text-green-200",
        );
      } else {
        classes.push(
          "bg-red-100 border-red-500 text-red-800 dark:bg-red-900 dark:border-red-600 dark:text-red-200",
        );
      }
    } else if (props.isCorrectOption) {
      classes.push(
        "bg-green-50 border-green-400 text-green-700 dark:bg-green-800 dark:border-green-500 dark:text-green-100",
      );
    } else {
      // classes.push(
      //   "bg-gray-100 border-gray-300 text-gray-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400",
      // );
    }
  } else {
    if (isSelectedByUser.value) {
      classes.push(
        "bg-primary text-primary-foreground border-primary shadow-lg",
      );
    } else {
      // classes.push(
      //   "bg-white border-gray-300 text-gray-800 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100",
      // );
    }
    classes.push(
      "hover:bg-primary/10 hover:text-black dark:hover:text-white  hover:border-primary",
    );
  }

  return cn(...classes);
});
</script>

<template>
  <div :class="tileClasses" @click="handleClick">
    <slot />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { QuestionOption } from '@/types/app-types'; // Assuming this interface exists
import { Check, Circle } from 'lucide-vue-next';

const props = defineProps<{
    option: QuestionOption;
    isSelected: boolean;
    isDisabled: boolean; // Not used currently, but good practice
}>();

const emit = defineEmits(['select']);

const handleClick = () => {
    if (!props.isDisabled) {
        emit('select', props.option.id);
    }
};

const tileClasses = computed(() => {
    const base = 'flex items-start p-4 border rounded-xl cursor-pointer transition-all duration-200';
    const focus = 'hover:border-primary-500 focus-within:ring-2 focus-within:ring-primary-500';
    const state = props.isSelected
        ? 'border-primary-600 bg-primary-50 dark:bg-primary-900/20 shadow-md'
        : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800';
    
    return `${base} ${focus} ${state}`;
});

const iconClasses = computed(() => {
    return props.isSelected
        ? 'text-primary-600 dark:text-primary-400'
        : 'text-gray-400 dark:text-gray-500';
});
</script>

<template>
    <div :class="tileClasses" @click="handleClick" :aria-checked="isSelected" role="radio" tabindex="0">
        <!-- Selection Indicator -->
        <div class="flex-shrink-0 mr-4 mt-1">
            <template v-if="isSelected">
                <Check :class="iconClasses" class="w-5 h-5" />
            </template>
            <template v-else>
                <Circle :class="iconClasses" class="w-5 h-5" />
            </template>
        </div>

        <!-- Option Text -->
        <div class="flex-grow">
            <p class="text-sm font-medium" :class="isSelected ? 'text-primary-700 dark:text-white' : 'text-gray-900 dark:text-gray-100'">
                {{ option.option_text }}
            </p>
        </div>
    </div>
</template>
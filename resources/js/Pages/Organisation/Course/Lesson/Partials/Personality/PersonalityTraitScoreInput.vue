<script lang="ts" setup>
import { ref, watch } from "vue";
import { Label } from "@/Components/ui/label";
import { Input } from "@/Components/ui/input";
import { Button } from "@/Components/ui/button";
import InputError from "@/Components/InputError.vue";
import { Slider } from "@/Components/ui/slider";
import { X, Plus, Weight } from "lucide-vue-next";
import {
  PersonalityQuestion,
  PersonalityTrait,
  PersonalityAnswerOption,
} from "./types";
import { nanoid } from "nanoid";
import { Badge } from "@/Components/ui/badge";

const props = defineProps<{
  trait: PersonalityTrait;
  // modelValue will be the actual number from option.scores[trait.id]
  modelValue: number | undefined;
  optionId: string; // Used for generating unique IDs for the input elements
  errors: string | undefined; // Error message specific to this score input
}>();

const emit = defineEmits(["update:modelValue"]);

// Create a local ref that will be used by the internal input/slider component.
// This ref will be synchronized with the `modelValue` prop.
const localScore = ref<[number] | undefined>(
  props.modelValue ? [props.modelValue] : undefined,
);

// Watch for changes in the `modelValue` prop (from parent) and update localScore.
// This ensures that if the parent updates the score, the local input reflects it.
watch(
  () => props.modelValue,
  (newVal) => {
    // Prevent unnecessary updates and potential infinite loops if the value is already the same
    if (newVal !== localScore.value) {
      localScore.value = newVal ? [newVal] : undefined;
    }
  },
  // No deep watch needed as modelValue is a primitive
);

// Watch for changes in `localScore` (from user interaction with the input/slider) and emit to parent.
// This ensures that user changes are propagated back to the parent.
watch(localScore, (newVal) => {
  // Only emit if the local score changes and it's different from the prop,
  // which indicates a change originated from this component (user interaction).
  if (newVal !== props.modelValue) {
    emit("update:modelValue", newVal?.[0]);
  }
});

// A helper to ensure scores object is initialized in the parent when the input is focused.
// This is called from the parent via a listener if needed, not directly in this component.
// The `ensureScoresInitialized` function needs to live where `option.scores` is managed, i.e., in PersonalityQuestionEditor.
</script>

<template>
  <div class="space-y-2">
    <div class="flex items-center justify-between">
      <Label
        :for="`editor-score-${optionId}-${trait.id}`"
        class="text-sm font-medium"
      >
        {{ trait.name }}
      </Label>

      <Badge
        :variant="(localScore?.[0] ?? 0) > 0 ? 'default' : 'secondary'"
        class="text-xs"
      >
        {{ localScore?.[0] ?? 0 }}%
      </Badge>
    </div>

    <Slider
      v-model="localScore"
      :id="`score-${optionId}-${trait.id}`"
      :max="100"
      :step="5"
      class="w-full"
    />

    <InputError class="mt-0" :message="errors" />
  </div>
</template>

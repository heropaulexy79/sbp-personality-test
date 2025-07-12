<script lang="ts" setup>
import { Label } from "@/Components/ui/label";
import { Input } from "@/Components/ui/input";
import { Button } from "@/Components/ui/button";
import { watch, ref, computed } from "vue";
import InputError from "@/Components/InputError.vue";
import { Slider } from "@/Components/ui/slider";
import { X, Plus, Weight } from "lucide-vue-next";
import {
  PersonalityQuestion,
  PersonalityTrait,
  PersonalityAnswerOption,
} from "./types";
import { nanoid } from "nanoid";
import PersonalityTraitScoreInput from "./PersonalityTraitScoreInput.vue";
import { nextTick } from "vue";

const props = defineProps<{
  question: PersonalityQuestion;
  traits: PersonalityTrait[];
  errors: { [key: string]: string } | undefined;
  questionIndex: number; // To help with error message paths
}>();

const emit = defineEmits(["update:question", "add-option", "delete-option"]);

const editingQuestion = ref<PersonalityQuestion>(
  JSON.parse(JSON.stringify(props.question)),
);

const endEl = ref<HTMLDivElement>();

// // Watch for prop changes to update the local copy (if the parent sends a new question object)
// watch(
//   () => props.question,
//   (newVal) => {
//     editingQuestion.value = JSON.parse(JSON.stringify(newVal));
//   },
//   { deep: true },
// );

// // Emit updates back to the parent whenever the local copy changes
// watch(
//   () => editingQuestion.value,
//   (newVal) => {
//     emit("update:question", newVal);
//   },
//   { deep: true },
// );

function save() {
  emit("update:question", editingQuestion.value);
}

const getTraitScoreError = (oIndex: number, traitId: string) => {
  return props.errors?.[
    `quiz.${props.questionIndex}.options.${oIndex}.scores.${traitId}`
  ];
};

const ensureScoresInitialized = (option: PersonalityAnswerOption) => {
  if (!option.scores) {
    option.scores = {};
  }
};

const addLocalOption = async () => {
  editingQuestion.value.options.push({
    id: nanoid(),
    text: "",
    scores: {},
  });

  await nextTick();
  endEl.value?.scrollIntoView({ behavior: "smooth", block: "end" });
};

const deleteLocalOption = (optionIndex: number) => {
  if (editingQuestion.value.options.length > 1) {
    editingQuestion.value.options.splice(optionIndex, 1);
  }
};
</script>

<template>
  <div>
    <div class="space-y-4">
      <div class="space-y-4 px-4">
        <div>
          <Label :for="'editor-q-text-' + editingQuestion.id"
            >Question Text:</Label
          >
          <Input
            type="text"
            :id="'editor-q-text-' + editingQuestion.id"
            v-model="editingQuestion.text"
            placeholder="Enter question text"
            class="mt-1"
          />
          <InputError
            class="mt-1"
            :message="errors?.[`quiz.${questionIndex}.text`]"
          />
        </div>

        <h4 class="mt-4 text-sm font-semibold">Answer Options:</h4>
        <ul class="grid gap-4">
          <li
            v-for="(option, oIndex) in editingQuestion.options"
            :key="option.id"
            class="space-y-4 rounded-lg border bg-gray-50 p-4"
          >
            <div class="flex items-center justify-between gap-2">
              <Label :for="'editor-o-text-' + option.id"
                >Option {{ oIndex + 1 }}:</Label
              >
              <Button
                type="button"
                variant="outline"
                size="icon"
                class="size-8 text-xs"
                @click="deleteLocalOption(oIndex)"
              >
                <X :size="14" />
              </Button>
            </div>
            <Input
              type="text"
              :id="'editor-o-text-' + option.id"
              v-model="option.text"
              placeholder="Enter option text"
              class="mt-1"
            />
            <InputError
              class="mt-1"
              :message="
                errors?.[`quiz.${questionIndex}.options.${oIndex}.text`]
              "
            />

            <div class="mt-3 space-y-1 border-t pt-2">
              <Label
                class="text-muted-foreground flex items-center gap-1 text-sm"
              >
                <Weight :size="16" />
                Archetype Weights (0-100):
              </Label>

              <p v-if="!traits.length" class="text-xs text-red-500">
                Please define traits in the main builder to assign scores.
              </p>
              <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <PersonalityTraitScoreInput
                  v-for="trait in traits"
                  :key="trait.id"
                  :trait="trait"
                  v-model="option.scores[trait.id]"
                  :option-id="option.id"
                  :errors="getTraitScoreError(oIndex, trait.id)"
                  @focus="ensureScoresInitialized(option)"
                />
              </div>
            </div>
          </li>
        </ul>
      </div>

      <div class="bg-background sticky bottom-0 border-t px-4 py-4">
        <div class="flex items-center justify-end gap-4">
          <Button
            type="button"
            variant="outline"
            class="mt-1"
            @click="addLocalOption"
          >
            Add option
          </Button>
          <Button type="button" class="mt-1" @click="save"> Save </Button>
        </div>
      </div>
    </div>

    <div :ref="(el) => (endEl = el as HTMLDivElement)" />
  </div>
</template>

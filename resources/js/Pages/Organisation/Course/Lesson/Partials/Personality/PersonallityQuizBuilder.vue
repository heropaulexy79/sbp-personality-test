<script lang="ts" setup>
import { Label } from "@/Components/ui/label";
import { Input } from "@/Components/ui/input";
import { Button } from "@/Components/ui/button";
import { watch, ref } from "vue";
import InputError from "@/Components/InputError.vue";
import { X, Plus, Edit } from "lucide-vue-next";
import { usePersonalityQuizManager } from "./use-personality-quiz-manager";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuPortal,
  DropdownMenuSeparator,
  DropdownMenuShortcut,
  DropdownMenuSub,
  DropdownMenuSubContent,
  DropdownMenuSubTrigger,
  DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";
import { PersonalityQuestion, PersonalityTrait } from "./types";
import PersonalityQuestionEditor from "./PersonalityQuestionEditor.vue";
import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetDescription,
  SheetTrigger,
} from "@/Components/ui/sheet";
import { toast } from "vue-sonner";
import PersonalityTraitManager from "./PersonalityTraitManager.vue";

defineProps<{ errors: { [key: string]: string } | undefined }>();

const questionsModel = defineModel<PersonalityQuestion[]>();
const traitsModel = defineModel<PersonalityTrait[]>("traits"); // Define a second model for traits

const {
  questions,
  traits,
  addQuestion,
  deleteQuestion,
  addTrait,
  deleteTrait,
} = usePersonalityQuizManager({
  initialQuestions: questionsModel.value ?? [],
  initialTraits: traitsModel.value ?? [], // Pass initial traits to the composable
});

const newTraitName = ref("");
const newTraitDescription = ref("");

const handleAddTrait = () => {
  if (newTraitName.value.trim()) {
    const isDuplicate = traits.value.some(
      (t) => t.name.toLowerCase() === newTraitName.value.trim().toLowerCase(),
    );
    if (!isDuplicate) {
      addTrait(newTraitName.value.trim(), newTraitDescription.value.trim());
      newTraitName.value = "";
      newTraitDescription.value = "";
    } else {
      // Potentially show a local error message for duplicate trait name
      toast.error("Trait with this name already exists.");
    }
  }
};

const isEditorOpen = ref(false);
const currentQuestionIndex = ref<number | null>(null);

const openEditor = (index: number) => {
  currentQuestionIndex.value = index;
  isEditorOpen.value = true;
};
const closeEditor = () => {
  currentQuestionIndex.value = null;
  isEditorOpen.value = false;
};

// Watch for changes in the questions and emit them via the model
watch(
  () => questions.value,
  (n) => {
    questionsModel.value = n;
  },
  { deep: true },
);

// NEW: Watch for changes in traits and emit them via the new traits model
watch(
  () => traits.value,
  (n) => {
    traitsModel.value = n;
  },
  { deep: true },
);
</script>

<template>
  <div class="space-y-8">
    <PersonalityTraitManager
      :traits="traits"
      :errors="errors"
      :add-trait="addTrait"
      :delete-trait="deleteTrait"
    />

    <div>
      <div class="mb-4 flex items-center justify-between gap-4">
        <Label class="font-semibold">Personality Questions</Label>

        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="outline"> Add Question </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent class="w-56">
            <!-- <DropdownMenuLabel>My Account</DropdownMenuLabel> -->
            <!-- <DropdownMenuSeparator /> -->
            <DropdownMenuGroup>
              <DropdownMenuItem @select="addQuestion('likert_scale')">
                <span>Likert Scale Question</span>
              </DropdownMenuItem>
              <DropdownMenuItem @select="addQuestion('multiple_choice')">
                <span>Multiple Choice Question</span>
              </DropdownMenuItem>
            </DropdownMenuGroup>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
      <ul class="space-y-4">
        <li
          v-for="(question, qIndex) in questions"
          :key="question.id"
          class="flex items-center justify-between gap-6 rounded-md border p-3"
        >
          <div class="flex-1">
            <h4 class="font-medium">Question {{ qIndex + 1 }}</h4>
            <span class="text-muted-foreground line-clamp-2 text-xs">{{
              question.text || "[No Question Text]"
            }}</span>
            <!-- <span class="ml-2 text-xs text-muted-foreground"> ({{ question.type.replace("_", " ") }}) </span> -->
          </div>
          <div class="flex shrink-0 gap-2">
            <Sheet>
              <SheetTrigger as-child>
                <Button type="button" variant="ghost" size="icon">
                  <Edit />
                </Button>
              </SheetTrigger>
              <SheetContent
                class="flex flex-col gap-0 sm:max-w-[800px] [&>button:last-child]:fixed [&>button:last-child]:top-3.5 [&>button:last-child]:z-[2]"
              >
                <SheetHeader class="bg-background sticky top-0 z-[1] border-b">
                  <SheetTitle>
                    Edit Question
                    {{ qIndex + 1 }}
                  </SheetTitle>
                  <SheetDescription>
                    Make changes to your question, options, and trait scores
                    here.
                  </SheetDescription>
                </SheetHeader>

                <div class="flex-1 overflow-y-auto pt-4">
                  <div>
                    <PersonalityQuestionEditor
                      :question="questions[qIndex]"
                      :traits="traits"
                      :errors="errors"
                      :question-index="qIndex"
                      @update:question="
                        (updatedQuestion: PersonalityQuestion) => {
                          questions[qIndex] = updatedQuestion;
                        }
                      "
                    />
                  </div>
                </div>
              </SheetContent>
            </Sheet>

            <Button
              type="button"
              variant="ghost"
              size="icon"
              @click="deleteQuestion(qIndex)"
            >
              <X />
            </Button>
          </div>
        </li>
        <li v-if="!questions.length">
          <p class="text-muted-foreground py-4 text-center text-sm">
            No questions added yet. Use the buttons below to add one!
          </p>
        </li>
      </ul>
    </div>
  </div>
</template>

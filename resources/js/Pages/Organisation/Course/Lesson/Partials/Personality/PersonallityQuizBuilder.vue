<script lang="ts" setup>
import { Label } from "@/Components/ui/label";
import { Button } from "@/Components/ui/button";
import { watch, ref, onMounted } from "vue";
import { X, Edit, WandSparklesIcon } from "lucide-vue-next"; // Removed SaveIcon
import { usePersonalityQuizManager } from "./use-personality-quiz-manager";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
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
import axios from "axios";
import { generateId } from "../utils";
// Removed router, usePage, and PageProps as they are no longer needed

defineProps<{ errors: { [key: string]: string } | undefined }>();

const questionsModel = defineModel<PersonalityQuestion[]>();
const traitsModel = defineModel<PersonalityTrait[]>("traits");

// Removed 'course' constant

// ======================================================================
// FIX: Simplify initialization and safely clone the data.
// ======================================================================
let initialQuestions: PersonalityQuestion[] = [];
if (Array.isArray(questionsModel.value) && questionsModel.value.length > 0) {
    // Deep clone the incoming data to ensure the usePersonalityQuizManager hook
    // has its own independent copy for local mutation.
    initialQuestions = JSON.parse(JSON.stringify(questionsModel.value));
} else if (Array.isArray(questionsModel.value)) {
    // Initialize as empty if it's an empty array
    initialQuestions = [];
}
// ======================================================================


const {
  questions,
  traits,
  addQuestion,
  deleteQuestion,
  addTrait,
  deleteTrait,
} = usePersonalityQuizManager({
  initialQuestions: initialQuestions, // Pass the clean, cloned array
  initialTraits: traitsModel.value ?? [],
});

// ======================================================================
// FIX: REMOVED redundant: questionsModel.value = questions.value;
// ======================================================================


const isEditorOpen = ref(false);
const currentQuestionIndex = ref<number | null>(null);

// --- AI GENERATION LOGIC START ---
const isGenerating = ref(false);
// Removed 'isSaving' ref
const hasFetchedTraits = ref(false);
const generatedQuizData = ref<any | null>(null);

const fetchInitialTraits = async () => {
  if (traits.value.length === 0 && !hasFetchedTraits.value) {
    hasFetchedTraits.value = true;
    try {
      const response = await axios.get(route("api.personality-traits.index"));
      traits.value = response.data.map((trait: any) => ({
        id: generateId(),
        name: trait.name,
        description: trait.description || "",
      }));
    } catch (error) {
      console.error("Failed to fetch initial traits:", error);
      toast.error("Could not load personality archetypes.");
    }
  }
};

onMounted(() => {
  fetchInitialTraits();
});

const generateWithAi = async () => {
  if (traits.value.length < 12) {
    toast.error("Waiting for archetypes to load. Please try again in a moment.");
    if (!hasFetchedTraits.value) {
      await fetchInitialTraits();
    }
    return;
  }

  isGenerating.value = true;
  generatedQuizData.value = null;
  const loadingToast = toast.loading("Generating quiz with AI... This may take a minute.");

  try {
    const response = await axios.post(route("ai.quiz.generate"));
    const data = response.data;

    generatedQuizData.value = data;

    if (data.archetypes) {
      traits.value.forEach((trait) => {
        trait.description = "";
      });
      data.archetypes.forEach((aiTrait: any) => {
        const localTrait = traits.value.find(
          (t: any) => t.name.toLowerCase() === aiTrait.name.toLowerCase()
        );
        if (localTrait && aiTrait.description) {
          localTrait.description = aiTrait.description;
        }
      });
    }

    questions.value = [];

    if (data.questions) {
      data.questions.forEach((q: any) => {
        const mappedOptions = q.options.map((o: any) => {
          const matchedTrait = traits.value.find(
            (t) => t.name.toLowerCase() === o.maps_to_archetype.toLowerCase()
          );
          if (!matchedTrait) {
            console.warn(
              `AI option "${o.option_text}" mapped to unknown archetype "${o.maps_to_archetype}".`
            );
          }

          // This logic correctly creates the nested 'scores' object
          const scores: { [key: string]: number } = {};
          if (matchedTrait) {
            scores[matchedTrait.id] = 1;
          }

          return {
            id: generateId(),
            text: o.option_text,
            scores: scores,
          };
        });

        questions.value.push({
          id: generateId(),
          type: "multiple_choice",
          text: q.question_text,
          options: mappedOptions,
        });
      });
    }

    toast.success("Quiz generated successfully! Review the questions below or save.");
  } catch (error: any) {
    console.error("AI Generation Error:", error);
    toast.error(error.response?.data?.error || "Failed to generate quiz. Please try again.");
    generatedQuizData.value = null;
  } finally {
    toast.dismiss(loadingToast);
    isGenerating.value = false;
  }
};

// Removed the entire 'saveGeneratedQuiz' function

watch(
  () => questions.value,
  (n) => {
    questionsModel.value = n;
  },
  { deep: true }
);
watch(
  () => traits.value,
  (n) => {
    traitsModel.value = n;
  },
  { deep: true }
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

        <div class="flex items-center gap-2">
            
          <Button
            v-if="!generatedQuizData"
            type="button"
            variant="secondary"
            @click="generateWithAi"
            :disabled="isGenerating"
          >
            <WandSparklesIcon class="mr-2 size-4" :class="{ 'animate-pulse': isGenerating }" />
            {{ isGenerating ? "Generating..." : "Generate with AI" }}
          </Button>

          <Button
            v-if="generatedQuizData"
            type="button"
            variant="ghost"
            size="icon"
            @click="generatedQuizData = null"
            title="Generate new quiz"
          >
            <X class="size-4" />
          </Button>


          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="outline"> Add Question </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent class="w-56">
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
      </div>

      <ul class="space-y-4">
        <li
          v-for="(question, qIndex) in questions"
          :key="question.id"
          class="flex items-center justify-between gap-6 rounded-md border p-3"
        >
          <div class="flex-1">
            <h4 class="font-medium">Question {{ qIndex + 1 }}</h4>
            <span class="text-muted-foreground line-clamp-2 text-xs">
              {{ question.text || "[No Question Text]" }}
            </span>
          </div>

          <div class="flex shrink-0 gap-2">
            <Sheet>
              <SheetTrigger as-child>
                <Button type="button" variant="ghost" size="icon">
                  <Edit class="size-4" />
                </Button>
              </SheetTrigger>
              <SheetContent
                class="flex flex-col gap-0 sm:max-w-[800px] [&>button:last-child]:fixed [&>button:last-child]:top-3.5 [&>button:last-child]:z-[2]"
              >
                <SheetHeader class="bg-background sticky top-0 z-[1] border-b pb-4">
                  <SheetTitle>Edit Question {{ qIndex + 1 }}</SheetTitle>
                  <SheetDescription>
                    Make changes to your question, options, and trait scores here.
                  </SheetDescription>
                </SheetHeader>

                <div class="flex-1 overflow-y-auto pt-4">
                  <div>
                    <PersonalityQuestionEditor
                      :question="questions[qIndex]"
                      :traits="traits"
                      :errors="errors"
                      :question-index="qIndex"
                      @update:question="(updatedQuestion: PersonalityQuestion) => {
                        questions[qIndex] = updatedQuestion;
                      }"
                    />
                  </div>
                </div>
              </SheetContent>
            </Sheet>

            <Button type="button" variant="ghost" size="icon" @click="deleteQuestion(qIndex)">
              <X class="size-4" />
            </Button>
          </div>
        </li>

        <li v-if="!questions.length">
          <p class="text-muted-foreground py-4 text-center text-sm">
            No questions added yet. Add traits first, then use 'Generate with AI' or add questions
            manually.
          </p>
        </li>
      </ul>
    </div>
  </div>
</template>
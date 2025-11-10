<script lang="ts" setup>
import { Label } from "@/Components/ui/label";
import { Button } from "@/Components/ui/button";
import { watch, ref, onMounted } from "vue";
import { X, Edit, WandSparklesIcon, SaveIcon } from "lucide-vue-next";
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
import { router, usePage } from "@inertiajs/vue3";
import { PageProps } from "@/types";

defineProps<{ errors: { [key: string]: string } | undefined }>();

const questionsModel = defineModel<PersonalityQuestion[]>();
const traitsModel = defineModel<PersonalityTrait[]>("traits");

const course = (usePage().props as PageProps).course ?? null;


// ======================================================================
// CRITICAL FIX: Robustly parse JSON and DEEP CLONE the data for the hook.
// This prevents VNode corruption caused by shared references.
// ======================================================================
let initialQuestions: PersonalityQuestion[] = [];
const modelValue = questionsModel.value;

try {
    let parsedData: any = [];

    // 1. Parse string data (if it came as a raw JSON string from DB/Inertia)
    if (typeof modelValue === 'string' && modelValue.length > 0) {
        parsedData = JSON.parse(modelValue);
    } else if (Array.isArray(modelValue)) {
        parsedData = modelValue;
    }

    // 2. Deep clone the data to break any shared memory references
    if (Array.isArray(parsedData) && parsedData.length > 0) {
        // Use JSON methods for a fast deep clone of simple data structures
        initialQuestions = JSON.parse(JSON.stringify(parsedData));
    }
} catch (e) {
    console.error("Failed to process initial questions data.", e);
    // initialQuestions remains []
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
// FIX #3: Reactivity Sync (Required after cloning to ensure model is updated)
// ======================================================================
questionsModel.value = questions.value;
traitsModel.value = traits.value;
// ======================================================================


const isEditorOpen = ref(false);
const currentQuestionIndex = ref<number | null>(null);

// --- AI GENERATION LOGIC START ---
const isGenerating = ref(false);
const isSaving = ref(false);
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

          // FIX #1: The mandatory saving logic correction (nested 'scores')
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

const saveGeneratedQuiz = async () => {
    if (!generatedQuizData.value) {
        toast.error("No generated quiz data to save.");
        return;
    }

    const title = window.prompt("Please enter a title for this new quiz:", "New Personality Quiz");
    if (!title) {
        return;
    }

    isSaving.value = true;
    const loadingToast = toast.loading("Saving your new quiz...");

    try {
        const payload = {
            title: title,
            course_id: course ? course.id : null,
            quiz_data: generatedQuizData.value,
        };

        const response = await axios.post(route('ai.quiz.store'), payload);

        toast.dismiss(loadingToast);
        toast.success(response.data.message || "Quiz saved successfully!");

        if (response.data.redirect_url) {
            router.visit(response.data.redirect_url);
        }

    } catch (error: any) {
        console.error("Failed to save quiz:", error);
        toast.dismiss(loadingToast);
        toast.error(error.response?.data?.error || "Failed to save quiz.");
    } finally {
        isSaving.value = false;
    }
};

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
                v-if="generatedQuizData"
                type="button"
                variant="default"
                @click="saveGeneratedQuiz"
                :disabled="isSaving"
            >
                <SaveIcon class="mr-2 size-4" :class="{ 'animate-pulse': isSaving }" />
                {{ isSaving ? "Saving..." : "Save Quiz" }}
            </Button>

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
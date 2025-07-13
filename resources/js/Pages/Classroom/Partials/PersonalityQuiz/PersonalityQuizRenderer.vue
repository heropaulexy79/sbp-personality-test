<script lang="ts" setup>
import { Button } from "@/Components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/Components/ui/dialog";
import { Label } from "@/Components/ui/label";
import { RadioGroup, RadioGroupItem } from "@/Components/ui/radio-group";
import { Course, Lesson, PersonalityQuiz } from "@/types";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { toast } from "vue-sonner";
import { WithUserLesson } from "../types";
import { usePersonalityQuizAnswerManager } from "./use-personality-quiz-answer-manager";
import { cn } from "@/lib/utils";
import { ArrowLeft, ArrowRight } from "lucide-vue-next";
import { Progress } from "@/Components/ui/progress";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { PersonalityTrait } from "@/Pages/Organisation/Course/Lesson/Partials/Personality/types";
import PersonalityQuizFinalResults from "./PersonalityQuizFinalResults.vue";
import PersonalityQuizOptionTile from "./PersonalityQuizOptionTile.vue";

const page = usePage();

const props = defineProps<{
  course: Course;
  lesson: WithUserLesson<Lesson>;
  nextLessonId: Lesson["slug"] | null;
}>();

const personalityQuizQuestions = computed<PersonalityQuiz["questions"]>(() => {
  return (props.lesson.content_json as any)?.questions || [];
});

const personalityQuizTraits = computed<PersonalityQuiz["traits"]>(() => {
  return (props.lesson.content_json as any)?.traits || [];
});

const {
  answerQuestion,
  nextQuestion,
  previousQuestion,
  currentQuestion,
  currentQuestionIdx,
  currentAnswer,
  hasNextQuestion,
  hasPreviousQuestion,
  answers,
  totalQuestions,
} = usePersonalityQuizAnswerManager(
  `${props.course.id}:${props.lesson.id}`,
  personalityQuizQuestions.value,

  props.lesson.answers ?? null,
);

const resultsDialog = ref(false);
const finalPersonalityResults = ref<{ [traitId: string]: number } | null>(null);

const completionForm = useForm({
  answers: [],
});

function submitPersonalityQuiz() {
  completionForm
    .transform((d) => {
      const ansArray = Array.from(answers.value.values());
      return { answers: ansArray };
    })
    .patch(
      route("classroom.lesson.answerPersonalityQuiz", {
        // Use the new backend route
        course: props.course.slug,
        lesson: props.lesson.slug,
      }),
      {
        onSuccess(page) {
          const flashMessage = page.props.flash.message as
            | {
                status: string;
                message: string;
                personality_results?: { [traitId: string]: number };
                personality_traits?: PersonalityTrait[];
              }
            | undefined;

          if (flashMessage && flashMessage.status === "success") {
            finalPersonalityResults.value =
              flashMessage.personality_results || null;
            // You might want to update the traits here too if they are dynamic
            // personalityQuizTraits.value = flashMessage.personality_traits || []; // Not necessary if traits are loaded from lesson initially
            resultsDialog.value = true;
          } else if (flashMessage && flashMessage.status === "error") {
            toast.error(flashMessage.message);
          } else {
            toast.error("An unknown error occurred during submission.");
          }
        },
        onError(error) {
          console.error("Submission error:", error);
          toast.error(
            "Failed to submit quiz. Please check your answers and try again.",
          );
        },
      },
    );
}

const isCompleted = computed(() => props.lesson.completed);

function onContinue() {
  if (props.nextLessonId) {
    router.visit(
      route("classroom.lesson.show", {
        lesson: props.nextLessonId,
        course: props.course.slug,
      }),
    );
    return;
  }
  router.visit(
    route("classroom.course.completed.show", {
      course: props.course.slug,
    }),
  );
}

const handleOptionUpdate = (selectedValue: string) => {
  answerQuestion(currentQuestion.value.id, selectedValue);
};
</script>

<template>
  <div
    class="bg-primary/20 flex min-h-[calc(100svh-65px)] flex-col items-center justify-center p-4"
  >
    <Card class="mx-auto w-full max-w-(--breakpoint-md) rounded-md">
      <CardHeader>
        <div class="mb-2 flex items-center justify-end gap-4">
          <!-- <Button
              variant="outline"
              size="sm"
              @click="resultsDialog = true"
              v-if="isCompleted"
            >
              Show Results
            </Button> -->

          <Progress
            :model-value="((currentQuestionIdx + 1) / totalQuestions) * 100"
            class="h-2"
          />
          <span class="shrink-0">
            {{ currentQuestionIdx + 1 }} /
            {{ totalQuestions }}
          </span>
        </div>

        <CardTitle class="mb-6 text-center text-lg font-bold">
          {{ currentQuestion.text }}
        </CardTitle>
      </CardHeader>
      <CardContent class="">
        <div class="relative">
          <div class="">
            <!-- Render Likert Scale Questions -->
            <!-- <RadioGroup
              v-if="currentQuestion.type === 'likert_scale'"
              :key="currentQuestion.id + 'or'"
              :default-value="currentAnswer as string | undefined"
              @update:model-value="
                (v) => {
                  answerQuestion(currentQuestion.id, v);
                }
              "
              :disabled="isCompleted"
            >
              <div
                v-for="option in currentQuestion.options"
                :key="option.id"
                class="mb-2 flex items-center space-x-2"
              >
                <RadioGroupItem
                  :id="option.id"
                  :value="option.id"
                  :disabled="isCompleted"
                />
                <Label :for="option.id">
                  {{ option.text }}
                </Label>
              </div>
            </RadioGroup> -->

            <!-- Render Multiple Choice Questions -->
            <!-- <RadioGroup
              v-else-if="currentQuestion.type === 'multiple_choice'"
              :key="currentQuestion.id + 'mult'"
              :default-value="currentAnswer as string | undefined"
              @update:model-value="
                (v) => {
                  answerQuestion(currentQuestion.id, v);
                }
              "
              :disabled="isCompleted"
            >
              <div
                v-for="option in currentQuestion.options"
                :key="option.id"
                class="mb-2 flex items-center space-x-2"
              >
                <RadioGroupItem
                  :id="option.id"
                  :value="option.id"
                  :disabled="isCompleted"
                />
                <Label :for="option.id">
                  {{ option.text }}
                </Label>
              </div>
            </RadioGroup> -->

            <div class="grid gap-4 sm:grid-cols-2">
              <PersonalityQuizOptionTile
                v-for="option in currentQuestion.options"
                :key="option.id"
                :value="option.id"
                :model-value="currentAnswer as string | undefined"
                :disabled="isCompleted"
                @update:model-value="handleOptionUpdate"
              >
                {{ option.text }}
              </PersonalityQuizOptionTile>
            </div>

            <!-- <p v-else class="text-red-500">
              Unknown question type: {{ currentQuestion.type }}
            </p> -->
          </div>

          <div
            class="mt-6 flex items-center justify-between gap-4 [&_button]:min-w-20"
          >
            <Button
              class="group"
              variant="secondary"
              size="sm"
              @click="previousQuestion"
              :disabled="!hasPreviousQuestion"
            >
              <ArrowLeft
                :size="16"
                class="transition-all group-hover:-translate-x-2"
              />
              Previous
            </Button>
            <Button
              class="group"
              size="sm"
              @click="nextQuestion"
              :disabled="!hasNextQuestion || !currentAnswer"
              v-if="hasNextQuestion"
            >
              Next
              <ArrowRight
                :size="16"
                class="transition-all group-hover:translate-x-2"
              />
            </Button>

            <Button
              class="group"
              size="sm"
              @click="submitPersonalityQuiz"
              :disabled="!currentAnswer"
              v-if="!hasNextQuestion && !isCompleted"
            >
              Submit
              <ArrowRight
                :size="16"
                class="transition-all group-hover:translate-x-2"
              />
            </Button>
            <!-- Show Next/Continue if quiz is completed -->
            <Button
              class="group"
              size="sm"
              @click="onContinue"
              v-if="isCompleted"
            >
              Continue
              <ArrowRight
                :size="16"
                class="transition-all group-hover:translate-x-2"
              />
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>

    <div>
      <Dialog v-model:open="resultsDialog">
        <DialogContent class="sm:max-w-[575px]">
          <DialogHeader>
            <DialogTitle class="text-center"> Personality Results </DialogTitle>
          </DialogHeader>
          <div>
            <PersonalityQuizFinalResults
              :final-personality-results="finalPersonalityResults"
              :personality-quiz-traits="personalityQuizTraits"
            />
          </div>
          <DialogFooter class="items-center justify-center sm:justify-center">
            <Button @click="onContinue"> Continue </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </div>
</template>

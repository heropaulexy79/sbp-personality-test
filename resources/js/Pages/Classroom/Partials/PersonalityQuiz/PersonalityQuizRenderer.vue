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
import MarketingEmailCapture from "@/Components/MarketingEmailCapture.vue";
import { getCookie } from "@/Pages/Classroom/Partials/cookie";

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
const emailSubmittedViaCookie = ref(getCookie("email_captured") === "true");
const showEmailCollection = ref(false);

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
          showEmailCollection.value = true;
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
    class="bg-background flex min-h-[calc(100svh)] flex-col items-center justify-center p-4"
  >
    <Card
      v-if="!finalPersonalityResults && !showEmailCollection"
      class="mx-auto w-full max-w-(--breakpoint-md) rounded-md"
    >
      <CardHeader>
        <div class="mb-2 flex items-center justify-end gap-4">
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
            <div class="grid gap-4 sm:grid-cols-2">
              <PersonalityQuizOptionTile
                v-for="option in currentQuestion.options"
                :key="option.id"
                :value="option.id"
                :model-value="currentAnswer as string | undefined"
                @update:model-value="handleOptionUpdate"
              >
                <!-- :disabled="isCompleted" -->
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
              v-if="!hasNextQuestion"
            >
              Submit
              <ArrowRight
                :size="16"
                class="transition-all group-hover:translate-x-2"
              />
            </Button>
            <!-- Show Next/Continue if quiz is completed -->
            <!-- <Button
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
            </Button> -->
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- <Card
      v-else-if=""
      class="mx-auto w-full max-w-(--breakpoint-md) rounded-md"
    >
      <CardHeader>
        <CardTitle class="mb-6 text-center text-lg font-bold">
          Enter your email to see your results!
        </CardTitle>
      </CardHeader>
      <CardContent>

      </CardContent>
    </Card> -->

    <div v-else-if="finalPersonalityResults" class="mx-auto w-full">
      <PersonalityQuizFinalResults
        :final-personality-results="finalPersonalityResults"
        :personality-quiz-traits="personalityQuizTraits"
        :resources="course.metadata.resources || []"
        :course="course"
      />
    </div>

    <Dialog :open="showEmailCollection && !emailSubmittedViaCookie">
      <DialogContent>
        <DialogHeader>
          <DialogTitle> Enter your email to see your results! </DialogTitle>
        </DialogHeader>
        <div>
          <MarketingEmailCapture
            @on-success="
              () => {
                emailSubmittedViaCookie = true; // Update local ref
                showEmailCollection = false; // Hide the form
                // resultsDialog.value = true; // Show results immediately
              }
            "
          />
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<style>
[data-slot="dialog-overlay"] {
  backdrop-filter: blur(var(--blur-md));
}
</style>

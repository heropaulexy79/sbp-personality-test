<script lang="ts" setup>
import { Button } from "@/Components/ui/button";
// Removed unused Dialog imports
// import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, } from "@/Components/ui/dialog"; 
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
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card";
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

// Initialize with existing scores if available (e.g., on page reload after email capture)
const finalPersonalityResults = ref<{ [traitId: string]: number } | null>(
    props.lesson.personality_scores || null
);
const emailSubmittedViaCookie = ref(getCookie("email_captured") === "true");

// Flag to track the state after clicking 'Submit'. Used for the transition to results or lead capture.
const quizSubmitted = ref(false); 

// Computed property to determine if the email wall should be shown
const showLeadCaptureCard = computed(() => {
    // Show email wall if quiz was submitted, no final results are present, AND email is not yet captured via cookie
    return quizSubmitted.value && !finalPersonalityResults.value && !emailSubmittedViaCookie.value;
});

// Computed property to determine if the quiz questions should be shown
const showQuizCard = computed(() => {
    // Show quiz if no final results, and we are not in a submitted state
    return !finalPersonalityResults.value && !quizSubmitted.value;
});

const completionForm = useForm({
  answers: [],
});

function submitPersonalityQuiz() {
  // Set submission state and clear any old final results
  quizSubmitted.value = true;
  finalPersonalityResults.value = null;

  completionForm
    .transform((d) => {
      const ansArray = Array.from(answers.value.values());
      return { answers: ansArray };
    })
    .patch(
      route("classroom.lesson.answerPersonalityQuiz", {
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
            const results = flashMessage.personality_results || null;

            if (results) {
                // Results received, show them
                finalPersonalityResults.value = results;
                quizSubmitted.value = false; // Clear state as results are available
            }
            // If results are null, the template will transition to the email capture card (if cookie is not set)
            // or remain in quizSubmitted state awaiting external factors (if cookie is set but results are null - shouldn't happen)

          } else if (flashMessage && flashMessage.status === "error") {
            toast.error(flashMessage.message);
            quizSubmitted.value = false; // Clear state on error
          } else {
            toast.error("An unknown error occurred during submission.");
            quizSubmitted.value = false; // Clear state on error
          }
        },
        onError(error) {
          console.error("Submission error:", error);
          toast.error(
            "Failed to submit quiz. Please check your answers and try again.",
          );
          quizSubmitted.value = false; // Clear state on error
        },
      },
    );
}

// Function to handle the successful submission of the email capture form
function handleEmailCaptureSuccess() {
    emailSubmittedViaCookie.value = true;

    // After email capture, we need to reload the page/component
    // so the backend re-evaluates the lesson and sends the personality_scores.
    router.reload({
        preserveScroll: true,
        onFinish: () => {
            toast.success("Thank you! Loading your results now...");
            // The reload will set finalPersonalityResults via props on the next render
            // and the component will switch to the results view.
        },
    });
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
    <!-- 1. QUIZ CARD -->
    <Card
      v-if="showQuizCard"
      class="mx-auto w-full max-w-xl rounded-xl shadow-2xl p-6"
    >
      <CardHeader class="pt-0">
        <div class="mb-4 flex items-center justify-between gap-4">
          <span class="text-sm font-medium text-primary">
            Question {{ currentQuestionIdx + 1 }} of {{ totalQuestions }}
          </span>
          <Progress
            :model-value="((currentQuestionIdx + 1) / totalQuestions) * 100"
            class="h-2 flex-grow max-w-[200px]"
          />
        </div>

        <CardTitle class="text-center text-2xl font-extrabold text-gray-800 dark:text-gray-100 leading-snug">
          {{ currentQuestion.text }}
        </CardTitle>
      </CardHeader>
      <CardContent class="pt-6">
        <div class="relative">
          <div class="grid gap-4 sm:grid-cols-2">
            <!-- Render personality options as tiles -->
            <PersonalityQuizOptionTile
              v-for="option in currentQuestion.options"
              :key="option.id"
              :value="option.id"
              :model-value="currentAnswer as string | undefined"
              @update:model-value="handleOptionUpdate"
            >
              {{ option.text }}
            </PersonalityQuizOptionTile>
          </div>

          <div
            class="mt-8 flex items-center justify-between gap-4 [&_button]:min-w-28"
          >
            <Button
              class="group"
              variant="secondary"
              size="lg"
              @click="previousQuestion"
              :disabled="!hasPreviousQuestion"
            >
              <ArrowLeft
                :size="18"
                class="transition-all group-hover:-translate-x-1 mr-2"
              />
              Previous
            </Button>
            <Button
              class="group"
              size="lg"
              @click="nextQuestion"
              :disabled="!hasNextQuestion || !currentAnswer"
              v-if="hasNextQuestion"
            >
              Next
              <ArrowRight
                :size="18"
                class="transition-all group-hover:translate-x-1 ml-2"
              />
            </Button>

            <Button
              class="group"
              size="lg"
              @click="submitPersonalityQuiz"
              :disabled="!currentAnswer || completionForm.processing"
              v-if="!hasNextQuestion"
            >
              <span v-if="completionForm.processing">Submitting...</span>
              <span v-else>
                Submit
                <ArrowRight
                  :size="18"
                  class="transition-all group-hover:translate-x-1 ml-2"
                />
              </span>
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- 2. EMAIL CAPTURE CARD (Replaces the empty screen + modal logic) -->
    <Card
      v-else-if="showLeadCaptureCard"
      class="mx-auto w-full max-w-lg rounded-xl shadow-2xl border-4 border-primary/50 bg-white dark:bg-gray-900 p-6"
    >
      <CardHeader class="text-center space-y-4">
        <CardTitle class="text-3xl font-extrabold text-primary">
          🎉 Quiz Complete! 🎉
        </CardTitle>
        <CardDescription class="text-base text-muted-foreground">
          Enter your email to instantly receive your personalized results and a free guide to understanding your new profile.
        </CardDescription>
      </CardHeader>
      <CardContent class="pt-6">
        <MarketingEmailCapture
          :metadata="{
            quizResults: {
              courseId: course.id,
              courseName: course.title,
              // Send answers to allow backend to calculate results after lead capture
              answers: Array.from(answers.values()), 
            },
          }"
          @on-success="handleEmailCaptureSuccess"
        />
      </CardContent>
    </Card>

    <!-- 3. FINAL RESULTS VIEW -->
    <div v-else-if="finalPersonalityResults" class="mx-auto w-full max-w-4xl">
      <PersonalityQuizFinalResults
        :final-personality-results="finalPersonalityResults"
        :personality-quiz-traits="personalityQuizTraits"
        :resources="course.metadata.resources || []"
        :course="course"
      />
      <!-- Add a continue button after results -->
      <div class="mt-8 flex justify-center">
        <Button size="lg" @click="onContinue" class="shadow-lg">
          Continue to next lesson
          <ArrowRight
            :size="18"
            class="transition-all group-hover:translate-x-1 ml-2"
          />
        </Button>
      </div>
    </div>
    
    <!-- Fallback/Loading State -->
    <div v-else class="text-center p-8">
        <p class="text-muted-foreground">Loading quiz data...</p>
    </div>
  </div >
</template>

<style>
/* No styles needed for this component, Tailwind handles them. */
</style>
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
import { Course, Lesson, Question } from "@/types";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { toast } from "vue-sonner";
import { WithUserLesson } from "./types";
import { useQuizAnswerManager } from "./use-quiz-answer-manager";
import { computed } from "vue";
import { cn } from "@/lib/utils";
import { AngryIcon, ArrowLeft, ArrowRight, SmileIcon } from "lucide-vue-next";
import { Progress } from "@/Components/ui/progress";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import QuizOptionTile from "./Quiz/QuizOptionTile.vue";

const page = usePage();

const props = defineProps<{
  course: Course;
  lesson: WithUserLesson<Lesson>;
  nextLessonId: Lesson["slug"] | null;
}>();

const quizzQuestionData = (props.lesson.content_json || []) as Question[];
// const quizzQuestionData = computed(() => {
//   return ((props.lesson.content_json as any)?.questions || []) as Question[];
// });

const {
  answerQuestion,
  nextQuestion,
  previousQuestion,
  currentQuesion,
  currentQuestionIdx,
  currentAnswer,
  hasNextQuesion,
  hasPreviousQuesion,
  answers,
  persistScore,
} = useQuizAnswerManager(
  `${props.course.id}:${props.lesson.id}`,
  quizzQuestionData,
  props.lesson.answers ?? null,
);

const successDialog = ref(false);

const completionForm = useForm({
  answers: [],
});

function submit() {
  completionForm
    .transform((d) => {
      const ans = Array.from(answers.value.values());
      return {
        // When console.log ing it shows as a proxy
        // answers: ans.map(({ ...rest }) => ({
        //     ...rest,
        // })),
        answers: ans,
      };
    })
    .patch(
      route("classroom.lesson.answerQuiz", {
        course: props.course.slug,
        lesson: props.lesson.slug,
      }),
      {
        // preserveState: true,
        onSuccess(page) {
          console.log(page.props.flash);
          if (Boolean(page.props.flash.message)) {
            successDialog.value = true;
          }
        },
        onError(error) {
          toast.error(JSON.stringify(error));
        },
      },
    );
}

const isCompleted = props.lesson.completed;

const correctOption = computed(() => {
  if (!isCompleted) return null;

  const n = currentQuesion.value;

  const r = n.options.find(
    // @ts-ignore
    (r) => r.id === n.correct_option,
  );

  return r;
});

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
  // successDialog.value = false;
  // router.reload();
}

const score = computed(() => {
  return Math.round(
    quizzQuestionData.reduce((prev, curr) => {
      const answr = answers.value.get(curr.id);

      if (answr?.selected_option_id === curr.correct_option) {
        return prev + 1;
      }

      return prev;
    }, 0),
  );
});
const scoreInPercent = computed(() =>
  Math.round(
    page.props.flash.message?.score ??
      (score.value / quizzQuestionData.length) * 100,
  ),
);

const handleOptionUpdate = (selectedValue: string) => {
  answerQuestion(currentQuesion.value.id, selectedValue);
};

watch(scoreInPercent, (newValue) => {
  persistScore(newValue);
});
</script>

<template>
  <!-- class="flex min-h-[calc(100svh-65px)] flex-col items-center justify-center bg-linear-to-b from-rose-100 to-teal-100 p-4 dark:from-gray-900 dark:to-gray-800" -->
  <div
    class="bg-primary/20 flex min-h-[calc(100svh-65px)] flex-col items-center justify-center p-4"
  >
    <!-- <Card class="mx-auto mb-6 w-full max-w-(--breakpoint-md) rounded-md">
      <CardHeader>
        <div class="flex items-center justify-end gap-4">
          <Button
            variant="outline"
            size="sm"
            @click="successDialog = true"
            v-if="isCompleted"
          >
            Show score
          </Button>

          <Progress
            :model-value="
              ((currentQuestionIdx + 1) / quizzQuestionData.length) * 100
            "
            class="h-3"
          />
          <span class="shrink-0">
            {{ currentQuestionIdx + 1 }} /
            {{ quizzQuestionData.length }}
          </span>
        </div>
      </CardHeader>
    </Card> -->
    <Card class="mx-auto w-full max-w-(--breakpoint-md) rounded-md">
      <CardHeader>
        <div class="mb-2 flex items-center justify-end gap-4">
          <Button
            variant="outline"
            size="sm"
            @click="successDialog = true"
            v-if="isCompleted"
          >
            Show score
          </Button>

          <Progress
            :model-value="
              ((currentQuestionIdx + 1) / quizzQuestionData.length) * 100
            "
            class="h-2"
          />
          <span class="shrink-0">
            {{ currentQuestionIdx + 1 }} /
            {{ quizzQuestionData.length }}
          </span>
        </div>

        <CardTitle class="mb-6 text-center text-lg font-bold">
          {{ currentQuesion.text }}
        </CardTitle>
      </CardHeader>
      <CardContent class="">
        <div class="relative">
          <!-- <div class="my-6">
            You scored {{ score }} / {{ lesson.content_json.length }}
        </div> -->

          <!-- <CardTitle class="mb-6 max-w-(--breakpoint-sm) text-lg font-bold">
                        {{ currentQuesion.text }}
                    </CardTitle> -->

          <div class="grid gap-4 sm:grid-cols-2">
            <!-- <RadioGroup
              :key="currentQuesion.id"
              :default-value="currentAnswer as string | undefined"
              @update:model-value="
                (v) => {
                  answerQuestion(currentQuesion.id, v);
                }
              "
              :disabled="isCompleted"
              v-if="currentQuesion.type === 'single_choice'"
            >
              <div
                v-for="option in currentQuesion.options"
                :key="option.id"
                class="flex items-center space-x-2"
                :class="
                  cn(
                    isCompleted && currentAnswer === option.id
                      ? correctOption?.id === option.id
                        ? 'text-green-500'
                        : 'text-destructive'
                      : '',
                  )
                "
              >
                <RadioGroupItem
                  :id="option.id"
                  :value="option.id"
                  :disabled="isCompleted"
                />
                <Label :for="option.id">
                  {{ option.text }}
                </Label> -->
            <!-- <RadioGroupItem
        v-bind="forwardedProps"
        :class="
            cn(
                'aspect-square h-4 w-4 rounded-full border border-primary text-primary ring-offset-background focus:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
                props.class,
            )
        "
    >
        <RadioGroupIndicator class="flex items-center justify-center">
            <Circle class="h-2.5 w-2.5 fill-current text-current" />
        </RadioGroupIndicator>
    </RadioGroupItem> -->
            <!-- </div>
            </RadioGroup>

            <div v-if="isCompleted" class="my-4">
              The correct answer is {{ correctOption?.text }}
            </div> -->

            <QuizOptionTile
              v-for="option in currentQuesion.options"
              :key="option.id"
              :value="option.id"
              :model-value="currentAnswer"
              @update:model-value="handleOptionUpdate"
              :disabled="isCompleted"
              :is-quiz-completed="isCompleted"
              :is-correct-option="option.id === correctOption?.id"
            >
              {{ option.text }}
            </QuizOptionTile>
          </div>

          <div
            class="mt-6 flex items-center justify-between gap-4 [&_button]:min-w-20"
          >
            <Button
              class="group"
              variant="secondary"
              size="sm"
              @click="previousQuestion"
              :disabled="!hasPreviousQuesion"
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
              :disabled="!hasNextQuesion"
              v-if="hasNextQuesion || isCompleted"
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
              @click="submit"
              v-if="!hasNextQuesion && !isCompleted"
            >
              Submit
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
      <Dialog v-model:open="successDialog">
        <!-- <DialogTrigger as-child>
      <Button variant="outline">
        Edit Profile
      </Button>
    </DialogTrigger> -->
        <DialogContent class="sm:max-w-[575px]">
          <DialogHeader>
            <DialogTitle class="text-center"> Quiz Results </DialogTitle>
            <!-- <DialogDescription>
                        Make changes to your profile here. Click save when
                        you're done.
                    </DialogDescription> -->
          </DialogHeader>
          <div class="text-center">
            <div
              class="radial-progress mx-auto size-32 rounded-full text-4xl font-bold"
              :style="{
                '--progress': Number(scoreInPercent),
              }"
            >
              <span
                class="bg-background flex size-28 items-center justify-center rounded-full"
              >
                {{ scoreInPercent ?? 0 }}%
              </span>
            </div>

            <!-- <div class="mt-4">
                        {{ $page.props.flash.message?.message }}
                    </div> -->

            <div class="grid grid-cols-2 gap-4">
              <div class="flex flex-col items-center space-y-2">
                <SmileIcon class="size-8 text-green-600" />
                <span class="text-sm font-medium"> Correct Answers </span>
                <span class="text-2xl font-bold">{{ score }}</span>
              </div>
              <div class="flex flex-col items-center space-y-2">
                <AngryIcon class="text-destructive size-8" />
                <span class="text-sm font-medium"> Incorrect Answers </span>
                <span class="text-2xl font-bold">
                  {{ quizzQuestionData.length - score }}
                </span>
              </div>
            </div>
          </div>
          <DialogFooter
            class="mt-4 items-center justify-center sm:justify-center"
          >
            <Button @click="onContinue"> Continue </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </div>
</template>

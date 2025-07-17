<script setup lang="ts">
import { Button } from "@/Components/ui/button";
import { Progress } from "@/Components/ui/progress";
import { Course, Lesson } from "@/types";
import { AwardIcon, ExternalLink } from "lucide-vue-next";
import { WithUserLesson } from "./types";
import { Card, CardContent } from "@/Components/ui/card";
import { Separator } from "@/Components/ui/separator";

const props = defineProps<{
  course: Course;
  lessons: WithUserLesson<Omit<Lesson, "content" | "content_json">>[];
  progress: number;
  completed_lessons: number;
  total_score: number;
}>();

const hasQuiz = props.lessons.some((r) => r.type === "QUIZ");
</script>

<template>
  <div class="flex min-h-[calc(100svh-65px)] items-center justify-center">
    <div class="mx-auto max-w-3xl">
      <Card class="overflow-hidden">
        <div class="p-6 text-center">
          <AwardIcon class="mx-auto mb-4 h-16 w-16" />
          <h1 class="mb-2 text-3xl font-bold md:text-4xl">Congratulations!</h1>
          <p class="text-xl">You've completed the course!</p>
        </div>
        <CardContent class="p-6">
          <div class="mb-6">
            <h2 class="mb-2 text-2xl font-semibold">
              Course: {{ course.title }}
            </h2>
            <Progress :model-value="progress" class="mb-2 h-2" />
            <p class="text-muted-foreground text-sm">
              {{ progress }}% Complete
            </p>
          </div>
          <div class="mb-6 flex flex-wrap items-center justify-center gap-4">
            <div class="flex-1 text-center">
              <p class="text-primary text-4xl font-bold">
                {{ completed_lessons }}
              </p>
              <p class="text-muted-foreground">Lessons Completed</p>
            </div>

            <div class="flex-1 text-center" v-show="hasQuiz">
              <p class="text-4xl font-bold">
                {{ total_score }}
              </p>
              <p class="text-muted-foreground">Points Won</p>
            </div>

            <!-- <div class="flex-1 text-center" v-show="!hasPersonalityQuiz">
            <p class="text-alternate text-4xl font-bold">
              {{ total_score }}
            </p>
            <p class="text-muted-foreground">Personality</p>
          </div> -->
          </div>
          <div>
            <Separator class="mb-4" />
          </div>

          <div class="grid md:grid-cols-2">
            <div class="mb-6">
              <h3 class="mb-4 text-lg font-semibold dark:text-white">
                Lessons covered:
              </h3>
              <ul class="text-muted-foreground list-inside list-disc">
                <li v-for="lesson in lessons" :key="lesson.id">
                  {{ lesson.title }}
                </li>
              </ul>
            </div>
            <div class="mb-6" v-if="course?.metadata?.resources.length > 0">
              <h3 class="mb-4 text-lg font-semibold dark:text-white">
                Resources:
              </h3>
              <ul class="text-muted-foreground">
                <li
                  v-for="lesson in course?.metadata?.resources"
                  :key="lesson.label"
                >
                  <a
                    :href="lesson.url"
                    target="_blank"
                    class="group flex w-full items-center gap-2"
                  >
                    <ExternalLink :size="16" />
                    <span>
                      {{ lesson.label }}
                    </span>
                  </a>
                </li>
              </ul>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

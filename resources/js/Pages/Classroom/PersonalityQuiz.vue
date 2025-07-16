<script lang="ts" setup>
import { Course, Lesson } from "@/types";
import { WithUserLesson } from "./Partials/types";
import PersonalityQuizRenderer from "./Partials/PersonalityQuiz/PersonalityQuizRenderer.vue";

const props = defineProps<{
  course: Course;
  quiz: WithUserLesson<Lesson>;
  // nextLessonId: Lesson["slug"] | null;
}>();
</script>

<template>
  <div>
    <div if="quiz.type === 'PERSONALITY_QUIZ'">
      <PersonalityQuizRenderer
        :course="course"
        :lesson="quiz"
        :next-lesson-id="null"
      />
    </div>
  </div>
</template>

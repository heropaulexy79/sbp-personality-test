<script lang="ts" setup>
import { Course, Lesson } from "@/types";
import { computed } from "vue";
import LessonContent from "./Partials/LessonContent.vue";
import { WithUserLesson } from "./Partials/types";

const props = defineProps<{
  course: Course;
  lessons: WithUserLesson<Omit<Lesson, "content" | "content_json">>[];
  lesson: WithUserLesson<Lesson>;
}>();

const nextLesson = computed(() => {
  const idx = props.lessons.findIndex((r) => r.id === props.lesson.id);

  return props.lessons[idx + 1] ?? null;
});
</script>

<template>
  <LessonContent
    :lesson="lesson"
    :course="course"
    :next-lesson-id="nextLesson?.slug ?? null"
  />
</template>

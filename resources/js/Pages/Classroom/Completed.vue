<script lang="ts" setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Course, Lesson } from "@/types";
import { Head } from "@inertiajs/vue3";
import LessonsSidenav from "./Partials/LessonsSidenav.vue";
import { WithUserLesson } from "./Partials/types";
import CourseCompleted from "./Partials/CourseCompleted.vue";
import ClassroomLayout from "./Partials/ClassroomLayout.vue";

const props = defineProps<{
  course: Course;
  lessons: WithUserLesson<Omit<Lesson, "content" | "content_json">>[];
  progress: number;
  completed_lessons: number;
  total_score: number;
}>();
</script>

<template>
  <Head :title="course.title" />

  <ClassroomLayout :course="course" :lessons="lessons">
    <CourseCompleted
      :course="course"
      :lessons="lessons"
      :progress="Math.round(progress)"
      :completed_lessons="completed_lessons"
      :total_score="Math.round(total_score)"
    />
  </ClassroomLayout>
</template>

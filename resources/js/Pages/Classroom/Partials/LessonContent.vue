<script lang="ts" setup>
import { Course, Lesson } from "@/types";

defineProps<{
    course: Course;
    lesson: Lesson;
}>();
</script>

<template>
    <div>
        <div v-if="lesson.type === 'DEFAULT'" class="prose lg:prose-lg">
            {{ lesson.content }}
        </div>
        <div v-else-if="lesson.type === 'QUIZ'">
            {{ lesson.content_json }}
        </div>
    </div>
</template>

<script setup lang="ts">
import TeacherLayout from "@/Layouts/TeacherLayout.vue";
import { Head } from "@inertiajs/vue3";
import { Lesson } from "@/types";

defineProps<{ lesson: Lesson }>();
</script>

<template>
    <Head :title="lesson.title" />

    <TeacherLayout> </TeacherLayout>
</template>

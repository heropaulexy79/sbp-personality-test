<script lang="ts" setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Course, Lesson } from "@/types";
import { Head } from "@inertiajs/vue3";
import LessonContent from "./Partials/LessonContent.vue";
import LessonsSidenav from "./Partials/LessonsSidenav.vue";

defineProps<{
    course: Course;
    lessons: Omit<Lesson, "content" | "content_json">[];
    lesson: Lesson;
}>();
</script>

<template>
    <Head :title="lesson.title" />

    <AuthenticatedLayout>
        <div class="container">
            <div
                class="relative grid md:grid-cols-[225px_1fr] min-h-[calc(100svh-65px)] bg-background"
            >
                <LessonsSidenav
                    class="h-[100svh] border-r fixed md:sticky top-0 self-start overflow-auto bg-background"
                    :course="course"
                    :lessons="lessons"
                />
                <div class="bg-white">
                    <div class="py-12 px-12">
                        <LessonContent :lesson="lesson" :course="course" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

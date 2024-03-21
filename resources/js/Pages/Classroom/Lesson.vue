<script lang="ts" setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Course, Lesson } from "@/types";
import { Head } from "@inertiajs/vue3";
import LessonContent from "./Partials/LessonContent.vue";
import LessonsSidenav from "./Partials/LessonsSidenav.vue";
import { computed } from "vue";
import { WithCompleted } from "./Partials/types";

const props = defineProps<{
    course: Course;
    lessons: WithCompleted<Omit<Lesson, "content" | "content_json">>[];
    lesson: WithCompleted<Lesson>;
}>();

const nextLesson = computed(() => {
    const idx = props.lessons.findIndex((r) => r.id === props.lesson.id);

    return props.lessons[idx + 1] ?? null;
});
</script>

<template>
    <Head :title="lesson.title" />

    <AuthenticatedLayout>
        <div class="container">
            <div
                class="relative grid min-h-[calc(100svh-65px)] bg-background md:grid-cols-[225px_1fr]"
            >
                <LessonsSidenav
                    class="fixed top-0 h-[100svh] self-start overflow-auto border-r bg-background md:sticky"
                    :course="course"
                    :lessons="lessons"
                />
                <div class="bg-white">
                    <div class="px-12 py-12">
                        <LessonContent
                            :lesson="lesson"
                            :course="course"
                            :next-lesson-id="nextLesson?.id"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

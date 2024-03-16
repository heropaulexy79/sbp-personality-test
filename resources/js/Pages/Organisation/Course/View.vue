<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { Course } from "@/types";
import ManageCourseLayout from "./Partials/ManageCourseLayout.vue";

defineProps<{ course: Course }>();
</script>

<template>
    <Head :title="course.title" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="container">
                <div class="space-y-6">
                    <h2 class="text-lg font-medium">{{ course.title }}</h2>
                </div>

                <div>
                    <ManageCourseLayout> Lesson </ManageCourseLayout>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { Course } from "@/types";
import ManageCourseLayout from "./Partials/ManageCourseLayout.vue";
import UpdateCourseForm from "./Partials/UpdateCourseForm.vue";

defineProps<{ course: Course }>();
</script>

<template>
    <Head :title="course.title" />

    <AuthenticatedLayout>
        <ManageCourseLayout :course="course">
            <div class="py-4">
                <div class="container">
                    <div
                        class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg"
                    >
                        <UpdateCourseForm :course="course" class="max-w-xl" />
                    </div>
                </div>
            </div>
        </ManageCourseLayout>
    </AuthenticatedLayout>
</template>

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
        <ManageCourseLayout :course="course">
            <div class="py-4">
                <div class="container">Edit form</div>
            </div>
        </ManageCourseLayout>
    </AuthenticatedLayout>
</template>

<script lang="ts" setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Course, Paginated } from "@/types";
import { Head } from "@inertiajs/vue3";
import CourseCard from "./Partials/CourseCard.vue";

const props = defineProps<{
    courses: Paginated<Course>;
}>();

console.log(props.courses);
</script>

<template>
    <Head title="Courses" />

    <AuthenticatedLayout>
        <ul class="space-y-4">
            <li v-for="course in courses.data">
                <CourseCard :course="course" />
            </li>
            <li v-if="courses.data.length === 0" class="text-center">
                No enrolled courses at the moment
            </li>
        </ul>

        <LaravelPagination
            v-if="courses.total > courses.data.length"
            :items="courses"
            class="flex justify-center py-6"
        />
    </AuthenticatedLayout>
</template>

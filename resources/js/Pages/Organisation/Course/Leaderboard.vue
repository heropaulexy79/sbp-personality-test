<script setup lang="ts">
import LaravelPagination from "@/Components/ui/LaravelPagination.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Course, Paginated } from "@/types";
import { Head, Link, usePage } from "@inertiajs/vue3";

type Student = {
    user: {
        id: number;
        name: string;
    };
    score: number;
    scores: {
        score: string | null;
    }[];
};

defineProps<{
    students: Student[];
}>();
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="container">
                <p v-for="student in students" :key="student.user.id">
                    {{ student.user.name }} ({{ student.score }})
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

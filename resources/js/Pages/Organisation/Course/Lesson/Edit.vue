<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { Lesson } from "@/types"; // Ensure Lesson type doesn't require course_id strictly if you changed it
import UpdateLessonForm from "@/Pages/Organisation/Course/Lesson/Partials/UpdateLessonForm.vue"; // You might want to move this component too later
import BaseBreadcrumb from "@/Components/ui/BaseBreadcrumb.vue";

// We no longer expect a 'course' prop
defineProps<{ lesson: Lesson }>();
</script>

<template>
    <Head :title="lesson.title" />

    <AuthenticatedLayout>
        <header class="bg-white shadow-sm dark:bg-gray-800">
            <div class="container flex items-center justify-between py-6">
                <div>
                    <BaseBreadcrumb
                        :items="[
                            {
                                href: route('dashboard'),
                                label: 'Dashboard',
                            },
                            { label: lesson.title },
                        ]"
                    />
                </div>
            </div>
        </header>
        <div class="py-12">
            <div class="container">
                <UpdateLessonForm :lesson="lesson" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
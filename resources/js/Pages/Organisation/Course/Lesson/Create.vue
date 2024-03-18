<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { Course } from "@/types";
import CreateLessonForm from "./Partials/CreateLessonForm.vue";

defineProps<{ course: Course }>();
</script>

<template>
    <Head :title="course.title + ' Lesson'" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="container">
                <div class="space-y-6">
                    <header class="flex items-start gap-6 justify-between">
                        <div>
                            <h2 class="text-lg font-medium">Create Lesson</h2>
                        </div>
                    </header>
                    <!--  -->

                    <div>
                        <!-- Form -->
                        <CreateLessonForm :course="course" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

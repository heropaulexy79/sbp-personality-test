<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { Course, Lesson } from "@/types";
import UpdateLessonForm from "./Partials/UpdateLessonForm.vue";

defineProps<{ lesson: Lesson }>();
</script>

<template>
    <Head :title="lesson.title" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="container">
                <div class="space-y-6">
                    <header class="flex items-start gap-6 justify-between">
                        <div>
                            <h2 class="text-lg font-medium">
                                {{ lesson.title }}
                            </h2>
                        </div>
                    </header>
                    <!--  -->

                    <div>
                        <!-- Form -->
                        <UpdateLessonForm :lesson="lesson" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

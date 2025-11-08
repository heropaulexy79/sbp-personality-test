<script setup lang="ts">
import OrganisationLayout from '@/Layouts/OrganisationLayout.vue';
// import Header from '@/Components/Header.vue'; // <-- Removed: This component doesn't exist
import PersonallityQuizBuilder from '@/Pages/Organisation/Course/Lesson/Partials/Personality/PersonallityQuizBuilder.vue';
import { Lesson } from '@/types/index';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card';
import DangerButton from '@/Components/DangerButton.vue';
// import { useToast } from '@/Components/ui/toast/use-toast'; // <-- Removed: Project uses sonner
import { toast } from 'vue-sonner'; // <-- Added: Correct toast import
import { computed } from 'vue';

const props = defineProps<{
    lesson: Lesson;
}>();

// const { toast } = useToast(); // <-- Removed

// Use local form data for deletion
const deleteForm = useForm({});

// Compute the current context (Quiz) vs. Course Lesson
const isStandaloneQuiz = computed(() => !props.lesson.course_id);

function destroy() {
    if (confirm('Are you sure you want to delete this quiz? This action cannot be undone.')) {
        deleteForm.delete(route('quizzes.destroy', props.lesson.id), {
            onSuccess: () => {
                // This toast call will now work with vue-sonner
                toast.success('Quiz Deleted', {
                    description: `${props.lesson.title} has been successfully deleted.`,
                });
            },
            onError: () => {
                toast.error('Deletion Failed', {
                    description: 'There was an error deleting the quiz.',
                });
            }
        });
    }
}
</script>

<template>
    <Head :title="`Edit Quiz: ${lesson.title}`" />

    <OrganisationLayout>
        <!-- Removed: <template #header> as OrganisationLayout does not have this slot -->

        <div class="container py-8">
            <!-- Added h1 title to replace the missing Header component -->
            <h1 class="text-2xl font-bold tracking-tight mb-6 dark:text-white">
                Edit Personality Quiz: {{ lesson.title }}
            </h1>

            <div class="max-w-4xl mx-auto space-y-6">

                <PersonallityQuizBuilder :lesson="lesson" />

                <!-- Delete Quiz Card (Only for standalone quizzes) -->
                <Card v-if="isStandaloneQuiz" class="border-destructive">
                    <CardHeader class="flex flex-row items-center justify-between">
                        <div class="space-y-1">
                            <CardTitle class="text-destructive">Delete Quiz</CardTitle>
                            <CardDescription>
                                Permanently remove this quiz and all associated data.
                            </CardDescription>
                        </div>
                        <DangerButton @click="destroy" :disabled="deleteForm.processing">
                            Delete Quiz
                        </DangerButton>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground">
                            This action cannot be reversed. All quiz results and settings will be lost.
                        </p>
                    </CardContent>
                </Card>

                <!-- Back to Quizzes Link -->
                <div class="pt-4">
                    <Link :href="route('quizzes.index')" class="text-sm text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-200">
                        &larr; Back to all Quizzes
                    </Link>
                </div>
            </div>
        </div>
    </OrganisationLayout>
</template>
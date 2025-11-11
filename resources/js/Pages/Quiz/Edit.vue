<script setup lang="ts">
import OrganisationLayout from '@/Layouts/OrganisationLayout.vue';
import PersonallityQuizBuilder from '@/Pages/Organisation/Course/Lesson/Partials/Personality/PersonallityQuizBuilder.vue';
import { Lesson } from '@/types/index';
import { Head, Link, useForm } from '@inertiajs/vue3'; // <-- Import useForm
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card';
import DangerButton from '@/Components/DangerButton.vue';
import { toast } from 'vue-sonner';
import { computed } from 'vue';
import { Button } from '@/Components/ui/button'; // <-- Import Button
import { SaveIcon } from 'lucide-vue-next'; // <-- Import SaveIcon

const props = defineProps<{
    lesson: Lesson;
    errors: { [key: string]: string };
}>();

// =================================================================
// FIX: Create an Inertia form to manage and save the quiz data
// =================================================================
const form = useForm({
    title: props.lesson.title,
    // We must ensure content_json and its properties exist
    content_json: {
        questions: props.lesson.content_json?.questions || [],
        traits: props.lesson.content_json?.traits || [],
        // You might need other properties from content_json here
    },
});

// This function will be called by our new "Save Changes" button
function saveQuiz() {
    form.patch(route('quizzes.update', props.lesson.id), {
        onSuccess: () => {
            toast.success('Quiz Updated Successfully');
        },
        onError: () => {
            toast.error('Failed to update quiz. Please check for errors.');
        },
    });
}
// =================================================================
// END OF FIX
// =================================================================

const deleteForm = useForm({});
const isStandaloneQuiz = computed(() => !props.lesson.course_id);

function destroy() {
    if (confirm('Are you sure you want to delete this quiz? This action cannot be undone.')) {
        deleteForm.delete(route('quizzes.destroy', props.lesson.id), {
            onSuccess: () => {
                toast.success('Quiz Deleted');
            },
            onError: () => {
                toast.error('Deletion Failed');
            }
        });
    }
}
</script>

<template>
    <Head :title="`Edit Quiz: ${lesson.title}`" />

    <OrganisationLayout>
        <div class="container py-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold tracking-tight dark:text-white">
                    Edit Personality Quiz: {{ form.title }}
                </h1>
                
                <Button @click="saveQuiz" :disabled="form.processing">
                    <SaveIcon class="mr-2 size-4" :class="{ 'animate-spin': form.processing }" />
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </Button>
                </div>

            <div class="max-w-4xl mx-auto space-y-6">

                <PersonallityQuizBuilder 
                    :lesson="lesson" 
                    :errors="form.errors"  
                    v-model="form.content_json.questions"
                    v-model:traits="form.content_json.traits"
                />

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

                <div class="pt-4">
                    <Link :href="route('quizzes.index')" class="text-sm text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-200">
                        &larr; Back to all Quizzes
                    </Link>
                </div>
            </div>
        </div>
    </OrganisationLayout>
</template>
<script setup lang="ts">
import OrganisationLayout from '@/Layouts/OrganisationLayout.vue';
import PersonallityQuizBuilder from '@/Pages/Organisation/Course/Lesson/Partials/Personality/PersonallityQuizBuilder.vue';
import QuizBuilder from '@/Pages/Organisation/Course/Lesson/Partials/QuizBuilder.vue'; // <-- ADDED IMPORT
import { Lesson } from '@/types/index';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card';
import DangerButton from '@/Components/DangerButton.vue';
import { toast } from 'vue-sonner';
import { computed } from 'vue';
import { Button } from '@/Components/ui/button';
import { SaveIcon } from 'lucide-vue-next';

const props = defineProps<{
    lesson: Lesson;
    errors: { [key: string]: string };
}>();

// Safely get the parsed content (which should be an object/array thanks to Laravel's $casts)
// If it's null, we default to an empty object to prevent runtime errors.
const initialContent = props.lesson.content_json || {};

const form = useForm({
    title: props.lesson.title,
    slug: props.lesson.slug,
    is_published: props.lesson.is_published,

    // Use 'quiz' for standard quizzes - Explicitly extract the nested 'questions' array
    quiz: props.lesson.type === 'QUIZ' 
        ? { questions: initialContent.questions || [] } 
        : null,

    // Use 'personality_quiz' for personality quizzes - Explicitly extract sub-arrays
    personality_quiz: props.lesson.type === 'PERSONALITY_QUIZ' 
        ? { 
            questions: initialContent.questions || [], 
            traits: initialContent.traits || [], 
            archetypes: initialContent.archetypes || [] 
          }
        : null,
});

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
                    Edit Quiz: {{ form.title }}
                </h1>
                
                <Button @click="saveQuiz" :disabled="form.processing">
                    <SaveIcon class="mr-2 size-4" :class="{ 'animate-spin': form.processing }" />
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </Button>
            </div>

            <div class="max-w-4xl mx-auto space-y-6">

<PersonallityQuizBuilder
    v-if="lesson.type === 'PERSONALITY_QUIZ'"
    :lesson="lesson"
    :errors="form.errors"
    v-model="form.personality_quiz.questions"
    v-model:traits="form.personality_quiz.traits"
/>

<QuizBuilder
    v-if="lesson.type === 'QUIZ'"
    :errors="form.errors"
    v-model="form.quiz.questions"
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
<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { Button } from "@/Components/ui/button";
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from "@/Components/ui/card";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import InputError from "@/Components/InputError.vue";
// --- 1. Import the Quiz Builder ---
import PersonallityQuizBuilder from '@/Pages/Organisation/Course/Lesson/Partials/Personality/PersonallityQuizBuilder.vue';
import { SaveIcon } from "lucide-vue-next"; // Import save icon

// --- 2. Add content_json to the form ---
const form = useForm({
    title: "",
    type: "PERSONALITY_QUIZ",
    // We will now manage the quiz content *from this page*
    content_json: {
        questions: [],
        traits: [],
    },
});

const submit = () => {
    // This now saves the title, type, AND all the questions/traits at once
    form.post(route('quizzes.store'));
};
</script>

<template>
    <Head title="Create Quiz" />

    <AuthenticatedLayout>
        <header class="bg-white shadow-sm dark:bg-gray-800">
            <div class="container py-6">
                <h1 class="text-xl font-bold">Create a New Quiz</h1>
            </div>
        </header>

        <div class="py-12">
            <div class="container max-w-4xl"> <!-- Made layout wider for builder -->
                <form @submit.prevent="submit">
                    <Card>
                        <CardHeader>
                            <CardTitle>Quiz Details</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-6"> <!-- Added more space -->
                            <div>
                                <Label for="title">Quiz Title</Label>
                                <Input
                                    id="title"
                                    v-model="form.title"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.title" />
                            </div>

                            <!-- --- 3. Add the Quiz Builder Here --- -->
                            <!--
                                We bind the builder directly to the form's
                                content_json questions and traits.
                                When "Generate with AI" is clicked, it will
                                automatically update `form.content_json.questions`
                            -->
                            <PersonallityQuizBuilder
                                :errors="form.errors"
                                v-model="form.content_json.questions"
                                v-model:traits="form.content_json.traits"
                            />

                        </CardContent>
                        <CardFooter class="flex justify-end">
                            <!-- --- 4. Updated Save Button --- -->
                            <Button :disabled="form.processing">
                                <SaveIcon class="mr-2 size-4" :class="{ 'animate-spin': form.processing }" />
                                {{ form.processing ? 'Creating...' : 'Create Quiz' }}
                            </Button>
                        </CardFooter>
                    </Card>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
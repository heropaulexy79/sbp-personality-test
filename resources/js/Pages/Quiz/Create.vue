<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { Button } from "@/Components/ui/button";
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from "@/Components/ui/card";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import InputError from "@/Components/InputError.vue";
import { SparklesIcon } from "lucide-vue-next"; 

// --- FIX IS HERE: Use default values, do not read props.lesson ---
const form = useForm({
    title: "", // Default empty string
    // Set the type directly as it's a personality test quiz factory
    type: "PERSONALITY_QUIZ",
    
    // Provide an empty JSON structure to satisfy backend validation/schema
    content_json: {
        questions: [],
        traits: [],
        archetypes: [],
    },
});

const submit = () => {
    form.post(route('quizzes.store'));
};

// ... (Rest of the AI generation logic remains the same)

// NEW: Separate form for AI generation
const aiForm = useForm({
    title: "", // Will sync with the main form's title
});

const generateWithAi = () => {
    // Sync the title before submission
    aiForm.title = form.title;
    
    // We post to the route defined in the first response (ai.quizzes.generate_and_store)
    aiForm.post(route('ai.quizzes.generate_and_store'), {
        onBefore: () => {
            form.processing = true; 
        },
        onFinish: () => {
            form.processing = false;
        }
    });
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
            <div class="container max-w-2xl">
                <form @submit.prevent="submit">
                    <Card>
                        <CardHeader>
                            <CardTitle>Quiz Details</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
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
                        </CardContent>
                        <CardFooter class="flex justify-between">
                            <Button 
                                type="button" 
                                variant="outline"
                                @click="generateWithAi"
                                :disabled="form.processing || aiForm.processing || !form.title"
                            >
                                <SparklesIcon class="mr-2 size-4" :class="{ 'animate-spin': aiForm.processing }" />
                                {{ aiForm.processing ? 'Generating...' : 'Generate with AI' }}
                            </Button>
                            
                            <Button :disabled="form.processing || aiForm.processing || !form.title" type="submit">
                                {{ form.processing ? 'Creating...' : 'Create & Edit Quiz' }}
                            </Button>
                        </CardFooter>
                    </Card>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
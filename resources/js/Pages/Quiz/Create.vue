<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { Button } from "@/Components/ui/button";
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from "@/Components/ui/card";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import InputError from "@/Components/InputError.vue";

// AFTER
const form = useForm({
    title: "",
    // We hardcode the type to simplify the form,
    // as your app is now only for personality quizzes.
    type: "PERSONALITY_QUIZ",
    
    // Add this default structure to pass validation
    content_json: {
        questions: [],
        traits: [],
        archetypes: [],
    },
});

const submit = () => {
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
                             <!-- The 'type' is hidden, as we only create one type -->
                        </CardContent>
                        <CardFooter class="flex justify-end">
                            <Button :disabled="form.processing">
                                {{ form.processing ? 'Creating...' : 'Create & Edit Quiz' }}
                            </Button>
                        </CardFooter>
                    </Card>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { Lesson } in "@/types"; // We are re-using the 'Lesson' type for our Quizzes
import { Button } from "@/Components/ui/button";
import { Card, CardHeader, CardTitle, CardDescription, CardFooter } from "@/Components/ui/card";
import { PlusCircle } from "lucide-vue-next";

defineProps<{ quizzes: Lesson[] }>();
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <header class="bg-white shadow-sm dark:bg-gray-800">
            <div class="container flex items-center justify-between py-6">
                <h1 class="text-xl font-bold">My Quizzes</h1>
                <Button as-child>
                    <Link :href="route('quizzes.create')">
                        <PlusCircle class="mr-2 h-4 w-4" />
                        Create New Quiz
                    </Link>
                </Button>
            </div>
        </header>

        <div class="py-12">
            <div class="container">
                <div v-if="quizzes.length > 0" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="quiz in quizzes" :key="quiz.id">
                        <CardHeader>
                            <CardTitle class="line-clamp-2">{{ quiz.title }}</CardTitle>
                            <CardDescription>
                                Type: {{ quiz.type.replace('_', ' ') }}
                            </CardDescription>
                        </CardHeader>
                        <CardFooter class="flex justify-end">
                            <Button as-child variant="secondary">
                                <Link :href="route('quizzes.edit', quiz.id)">
                                    Edit
                                </Link>
                            </Button>
                        </CardFooter>
                    </Card>
                </div>
                <div v-else class="text-center rounded-lg border-2 border-dashed border-gray-300 p-12">
                     <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        No quizzes found
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Get started by creating your first quiz.
                    </p>
                    <div class="mt-6">
                         <Button as-child>
                            <Link :href="route('quizzes.create')">
                                <PlusCircle class="mr-2 h-4 w-4" />
                                Create New Quiz
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
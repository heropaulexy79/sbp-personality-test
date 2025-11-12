<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/Components/ui/card';

// 1. Define the prop we will pass from the controller
defineProps({
    enrolledCourses: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                My Quizzes
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        
                        <div v-if="enrolledCourses.length > 0">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                                <Card v-for="course in enrolledCourses" :key="course.id">
                                    <CardHeader>
                                        <CardTitle>{{ course.title }}</CardTitle>
                                        <CardDescription v-if="course.description">
                                            {{ course.description.substring(0, 100) }}...
                                        </CardDescription>
                                    </CardHeader>
                                    <CardFooter class="flex justify-between">
                                        <Link :href="route('courses.view', course.slug)">
                                            <Button variant="outline">Learn More</Button>
                                        </Link>

                                        <Link v-if="course.first_lesson_slug" :href="route('classroom.lesson', [course.slug, course.first_lesson_slug])">
                                            <Button>Take Quiz</Button>
                                        </Link>
                                    </CardFooter>
                                </Card>
                            </div>
                        </div>

                        <div v-else>
                            <p>You are not currently enrolled in any quizzes. Please contact your administrator.</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
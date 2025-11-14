<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Separator } from '@/Components/ui/separator';
import { GraduationCap, Pencil, BookOpen, Users, CircleCheck } from 'lucide-vue-next';

interface Quiz {
    id: number;
    title: string;
    slug: string;
    is_published?: boolean;
    // Added for managed quizzes count
    total_responses?: number;
}

const props = defineProps<{
    // FIX: Expecting 'managedQuizzes' (for teachers) and 'enrolledQuizzes' (for all users)
    managedQuizzes: Quiz[];
    enrolledQuizzes: Quiz[];
    // FIX: Added the missing required prop
    isTeacher: boolean;
    // NOTE: managedCourses and enrolledCourses are typically passed here too, 
    // but omitted from the prop definition above for brevity as they weren't used in the provided template
}>();
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Dashboard" />

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-12">
                
                <!-- === STUDENT VIEW: ENROLLED QUIZZES === -->
                <div v-if="enrolledQuizzes.length > 0" class="space-y-6">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                        <GraduationCap class="w-6 h-6 mr-3 text-primary" />
                        Quizzes I Can Take
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <Card v-for="quiz in enrolledQuizzes" :key="quiz.id" class="hover:shadow-lg transition-shadow duration-300">
                            <CardHeader>
                                <CardTitle class="text-lg">{{ quiz.title }}</CardTitle>
                                <CardDescription>Enrolled as a student.</CardDescription>
                            </CardHeader>
                            <CardContent class="flex justify-end pt-0">
                                <Link :href="route('public.quiz.show', quiz.slug)">
                                    <Button variant="default">
                                        <BookOpen class="w-4 h-4 mr-2" />
                                        Start Quiz
                                    </Button>
                                </Link>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- Separator only visible if there's both teacher-managed and student-enrolled content -->
                <Separator v-if="isTeacher && enrolledQuizzes.length > 0 && managedQuizzes.length > 0" />

                <!-- === TEACHER VIEW: CREATED QUIZZES === -->
                <div v-if="isTeacher" class="space-y-6">
                    <div class="flex justify-between items-center">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                            <Pencil class="w-6 h-6 mr-3 text-secondary-foreground" />
                            Quizzes I Manage
                        </h3>
                        <Link :href="route('quizzes.create')">
                            <Button variant="outline" class="group">
                                Create New Quiz
                            </Button>
                        </Link>
                    </div>

                    <div v-if="managedQuizzes.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <Card v-for="quiz in managedQuizzes" :key="quiz.id" class="border-2" :class="{'border-primary shadow-md': quiz.is_published, 'border-dashed border-gray-400': !quiz.is_published}">
                            <CardHeader>
                                <CardTitle class="text-lg">{{ quiz.title }}</CardTitle>
                                <CardDescription class="flex items-center space-y-1">
                                    <span class="flex items-center">
                                        Status: 
                                        <span v-if="quiz.is_published" class="text-green-600 dark:text-green-400 ml-1 flex items-center">
                                            <CircleCheck class="w-4 h-4 mr-1"/> Published
                                        </span>
                                        <span v-else class="text-red-500 dark:text-red-400 ml-1">Draft</span>
                                    </span>
                                    <!-- Display responses count if available -->
                                    <span v-if="quiz.total_responses !== undefined" class="text-sm text-gray-500 dark:text-gray-400 ml-4">
                                        ({{ quiz.total_responses }} responses)
                                    </span>
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="flex justify-end space-x-2 pt-0">
                                <Link :href="route('quizzes.enroll', quiz.slug)">
                                    <Button variant="secondary" size="sm">
                                        <Users class="w-4 h-4 mr-2" />
                                        Enroll Users
                                    </Button>
                                </Link>
                                <Link :href="route('quizzes.edit', quiz.slug)">
                                    <Button size="sm">
                                        <Pencil class="w-4 h-4 mr-2" />
                                        Edit Quiz
                                    </Button>
                                </Link>
                            </CardContent>
                        </Card>
                    </div>
                    
                    <div v-else class="text-center py-10 bg-gray-50 dark:bg-gray-800 rounded-lg border border-dashed border-gray-300 dark:border-gray-700">
                        <p class="text-gray-500 dark:text-gray-400">You haven't created any quizzes yet.</p>
                        <Link :href="route('quizzes.create')">
                            <Button variant="link" class="mt-2">Create Your First Quiz</Button>
                        </Link>
                    </div>
                </div>

                <!-- Fallback: Only a student, and no enrolled quizzes -->
                <div v-if="!isTeacher && enrolledQuizzes.length === 0" class="text-center py-20">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">No Enrolled Quizzes Found</h3>
                    <p class="text-gray-600 dark:text-gray-400">It looks like you haven't been enrolled in any published quizzes yet.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
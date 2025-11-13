<script setup lang="ts">
import TeacherLayout from '@/Layouts/TeacherLayout.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3'; 
import { defineProps, computed, onMounted } from 'vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Label } from '@/Components/ui/label';
import { Checkbox } from '@/Components/ui/checkbox';
import { useToast } from '@/Components/ui/toast/use-toast'; 
import Toaster from '@/Components/ui/toast/Toaster.vue'; 

interface Course {
    id: number;
    title: string;
    slug: string | null; // Allow null/missing slug for robust checking
}

interface User {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    course: Course;
    allUsers: User[];
    enrolledUserIds: number[];
}>();

const { toast } = useToast();

// Initialize usePage() for stable access to global props (like auth)
const page = usePage();

const form = useForm({
    user_ids: props.enrolledUserIds,
});

// Computed list of users with their current enrollment status
const userList = computed(() => {
    // Accessing global props via the reactive usePage() object is safer.
    // We are excluding the authenticated user (the teacher/admin) from the enrollment list.
    const authenticatedUserId = (page.props.auth as any).user.id;
    
    return props.allUsers
        .filter(user => user.id !== authenticatedUserId) 
        .map(user => ({
            ...user,
            isEnrolled: form.user_ids.includes(user.id),
        }))
        // Sort the list so enrolled users appear first, for easier management
        .sort((a, b) => (b.isEnrolled as any) - (a.isEnrolled as any));
});

// Handle checkbox change to add or remove user IDs from the form data
const toggleEnrollment = (userId: number, isChecked: boolean) => {
    if (isChecked) {
        if (!form.user_ids.includes(userId)) {
            // Add the ID if it's being checked and is not already present
            form.user_ids.push(userId);
        }
    } else {
        // Remove the ID if it's being unchecked
        form.user_ids = form.user_ids.filter(id => id !== userId);
    }
};

const submit = () => {
    // FIX: Add check for missing slug before attempting to call route()
    if (!props.course.slug) {
        toast({
            title: 'Error',
            description: 'Cannot update enrollment: Quiz slug is missing.',
            duration: 5000,
            variant: 'destructive',
        });
        return;
    }

    // Pass the slug explicitly
    form.post(route('quizzes.enroll.update', { quiz: props.course.slug }), {
        onSuccess: () => {
            // Display success notification
            toast({
                title: 'Enrollment Updated!',
                description: `Successfully updated enrollment for ${props.course.title}.`,
                duration: 3000,
            });
        },
        onError: (errors) => {
             // Concatenate all error messages for display
             const errorMessages = Object.values(errors).flat().join(' ');
             toast({
                title: 'Error updating enrollment',
                description: errorMessages || 'Please check the form for errors.',
                duration: 5000,
            });
            console.error(errors);
        },
    });
};

onMounted(() => {
    // Sync the initial form data with props on mount to ensure reactivity 
    // and correct initial state of checkboxes.
    form.user_ids = [...props.enrolledUserIds];

    // FIX: Check if processing is stuck and force a reset if necessary.
    // This will fix the unclickable button if it's stuck from a previous error.
    if (form.processing) {
        console.warn('Form.processing was stuck true on mount. Forcing reset.');
        form.processing = false;
    }
});

</script>

<template>
    <TeacherLayout>
        <Head :title="`Enroll Users in ${course.title}`" />

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <Card class="shadow-lg">
                    <CardHeader class="border-b">
                        <CardTitle class="text-2xl font-bold">Enroll Users in Quiz: {{ course.title }}</CardTitle>
                        <CardDescription>
                            Select the users who should have access to this quiz.
                        </CardDescription>
                    </CardHeader>

                    <form @submit.prevent="submit">
                        <CardContent class="p-6 space-y-2 max-h-[60vh] overflow-y-auto">
                            
                            <!-- Header for the list -->
                            <div class="flex items-center justify-between font-semibold sticky top-0 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 py-2">
                                <span class="text-sm text-gray-700 dark:text-gray-300">User</span>
                                <span class="text-sm text-gray-700 dark:text-gray-300">Enrolled</span>
                            </div>

                            <!-- User List -->
                            <div v-for="user in userList" :key="user.id" class="flex items-center justify-between py-3 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-md px-2 -mx-2 transition-colors">
                                <div class="flex flex-col">
                                    <Label :for="`user-${user.id}`" class="text-sm font-medium leading-none cursor-pointer">
                                        {{ user.name }}
                                    </Label>
                                    <span class="text-xs text-muted-foreground">{{ user.email }}</span>
                                </div>
                                
                                <Checkbox
                                    :id="`user-${user.id}`"
                                    :checked="user.isEnrolled"
                                    @update:checked="toggleEnrollment(user.id, $event as boolean)"
                                    class="h-5 w-5"
                                />
                            </div>

                            <div v-if="userList.length === 0" class="text-center text-gray-500 py-4">
                                No other users found in the system.
                            </div>
                        </CardContent>

                        <div class="flex items-center justify-end p-6 bg-gray-50 dark:bg-gray-800 border-t rounded-b-lg">
                            <Button :disabled="form.processing" type="submit">
                                {{ form.processing ? 'Saving...' : 'Save Enrollments' }}
                            </Button>
                        </div>
                    </form>
                </Card>
            </div>
        </div>

        <!-- Toast container must be present to display notifications -->
        <Toaster />
    </TeacherLayout>
</template>
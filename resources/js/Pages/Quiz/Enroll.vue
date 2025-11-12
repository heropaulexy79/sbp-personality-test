<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Checkbox } from '@/Components/ui/checkbox';
import { Label } from '@/Components/ui/label';
import { useToast } from '@/Components/ui/toast/use-toast';
import { Toaster } from '@/Components/ui/toast';
import { onMounted, watch } from 'vue';

// 1. Define the props coming from CourseEnrollmentController@edit
const props = defineProps({
    course: Object,
    allUsers: Array,
    enrolledUserIds: Array,
    flash: Object, // To receive the success message
});

// 2. Set up the form
// We initialize the 'user_ids' array with the IDs that
// were passed from the controller.
const form = useForm({
    user_ids: props.enrolledUserIds || [],
});

// 3. Set up the toast notifications
const { toast } = useToast();

// 4. Function to handle the form submission
const submit = () => {
    form.post(route('quizzes.enroll.update', props.course.id), {
        preserveScroll: true,
        // onSuccess is handled by the 'flash' prop watcher
    });
};

// 5. Watch for the 'flash.success' message and show a toast
watch(() => props.flash.success, (message) => {
    if (message) {
        toast({
            title: 'Success!',
            description: message,
        });
    }
});
</script>

<template>
    <Head :title="`Enroll Users in ${course.title}`" />
    <Toaster />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        Enroll Users in "{{ course.title }}"
                    </h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Select the users you want to enroll in this quiz.
                    </p>
                </div>
                <!-- Add a "Back" button to return to the admin page -->
                <Link :href="route('quizzes.index')">
                    <Button variant="outline">Back to Quizzes</Button>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <Card>
                    <CardHeader>
                        <CardTitle>User List</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <!-- 6. Create the form -->
                        <form @submit.prevent="submit">
                            <div class="space-y-6">
                                <!-- 7. Loop over ALL users -->
                                <div
                                    v-for="user in allUsers"
                                    :key="user.id"
                                    class="flex items-center space-x-3 rounded-lg border p-4"
                                >
                                    <!-- 8. The checkbox
                                         We use v-model="form.user_ids" and provide
                                         the user.id as the 'value'. vue-form-helpers
                                         will automatically add/remove the user.id
                                         from the form.user_ids array when checked/unchecked.
                                    -->
                                    <Checkbox
                                        :id="`user-${user.id}`"
                                        v-model:checked="form.user_ids"
                                        :value="user.id"
                                    />
                                    <Label
                                        :for="`user-${user.id}`"
                                        class="flex flex-col"
                                    >
                                        <span class="font-medium">{{ user.name }}</span>
                                        <span class="text-sm text-gray-500">{{ user.email }}</span>
                                    </Label>
                                </div>

                                <!-- 9. Show a message if no users exist -->
                                <div v-if="allUsers.length === 0">
                                    <p class="text-gray-500">No users found to enroll.</p>
                                </div>
                            </div>

                            <!-- 10. The submit button -->
                            <div class="mt-8 flex justify-end border-t pt-6">
                                <Button :disabled="form.processing">
                                    {{ form.processing ? 'Saving...' : 'Update Enrollment' }}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
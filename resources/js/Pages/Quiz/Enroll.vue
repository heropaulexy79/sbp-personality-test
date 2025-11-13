<script setup lang="ts">
import TeacherLayout from '@/Layouts/TeacherLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3'; 
// Import axios and utility functions
import { defineProps, computed, onMounted, ref, watch } from 'vue'; 
import axios from 'axios';
// FIX: Changed to modular import path for better tree-shaking and to resolve the Vite import error
import debounce from 'lodash/debounce'; 

// Import components
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Label } from '@/Components/ui/label';
import { Checkbox } from '@/Components/ui/checkbox';
import { useToast } from '@/Components/ui/toast/use-toast'; 
import Toaster from '@/Components/ui/toast/Toaster.vue'; 
import InputError from '@/Components/InputError.vue'; // Added InputError for validation
import TextInput from '@/Components/TextInput.vue'; // Added TextInput for search

interface Course {
    id: number;
    title: string;
    slug: string | null;
}

interface User {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    course: Course;
    enrolledUserIds: number[];
}>();

const { toast } = useToast();
const page = usePage();

// --- NEW STATE FOR SEARCH AND DATA FETCHING ---
const users = ref<User[]>([]); // Reactive list of users displayed in the search list
const loadingUsers = ref(false);
const searchTerm = ref('');
// --- END NEW STATE ---

const form = useForm({
    user_ids: props.enrolledUserIds,
});

// Computed list of users with their current enrollment status (uses the locally fetched 'users' list)
const userList = computed(() => {
    // FIX: Removed filter that excludes authenticated user (Teacher/Admin can also be a student)
    // const authenticatedUserId = (page.props.auth as any).user.id;
    
    return users.value
        // .filter(user => user.id !== authenticatedUserId) // Removed this filter
        .map(user => ({
            ...user,
            isEnrolled: form.user_ids.includes(user.id),
        }))
        // Sort the list so enrolled users appear first
        .sort((a, b) => (b.isEnrolled as any) - (a.isEnrolled as any));
});

// --- NEW FUNCTIONALITY: SERVER-SIDE SEARCH ---

async function fetchUsers(searchQuery: string = '') {
    loadingUsers.value = true;
    try {
        // Use an assumed API route to fetch users based on the search term
        const response = await axios.get(route('api.quizzes.users.search', { quiz: props.course.slug }), {
            params: {
                search: searchQuery
            }
        });
        
        users.value = response.data.users; // Assuming the API returns data under the 'users' key
    } catch (error) {
        console.error('Error fetching users:', error);
        toast({
            title: 'Search Error',
            description: 'Failed to fetch users from the server.',
            duration: 3000,
            variant: 'destructive',
        });
        users.value = [];
    } finally {
        loadingUsers.value = false;
    }
}

// Debounce the fetch function to avoid spamming the server while the user types
const debouncedFetchUsers = debounce(fetchUsers, 300);

// Watch the search term and call the debounced function
watch(searchTerm, (newTerm) => {
    // Only fetch if the search term is empty (to load all initially) or if it has content.
    // The debounce handles the rate limiting.
    debouncedFetchUsers(newTerm);
}, { immediate: false }); // immediate: false means it won't run on mount

// --- END NEW FUNCTIONALITY ---

// Handle checkbox change to add or remove user IDs from the form data
const toggleEnrollment = (userId: number, isChecked: boolean) => {
    if (isChecked) {
        if (!form.user_ids.includes(userId)) {
            form.user_ids.push(userId);
        }
    } else {
        form.user_ids = form.user_ids.filter(id => id !== userId);
    }
    // Clear any user_ids related validation errors immediately on change
    if (form.errors.user_ids) {
        form.clearErrors('user_ids');
    }
};

const submit = () => {
    if (!props.course.slug) {
        toast({
            title: 'Error',
            description: 'Cannot update enrollment: Quiz slug is missing.',
            duration: 5000,
            variant: 'destructive',
        });
        return;
    }

    form.post(route('quizzes.enroll.update', { quiz: props.course.slug }), {
        preserveScroll: true,
        // FIX: Remove fetchUsers here as the page is redirecting anyway, preventing potential race conditions 
        // that hide the toast. We will rely on the Inertia flash message handled in the layout.
        onSuccess: () => {
            // Display the toast notification on successful save.
            toast({
                title: 'Enrollment Updated!',
                description: `Successfully updated enrollment for ${props.course.title}.`,
                duration: 3000,
            });
        },
        onError: (errors) => {
             const errorMessages = Object.values(errors).flat().join(' ');
             toast({
                 title: 'Error updating enrollment',
                 description: errorMessages || 'Please check the form for errors.',
                 duration: 5000,
                 variant: 'destructive',
             });
             console.error(errors);
        },
    });
};

onMounted(async () => {
    // Sync the initial form data with props.
    form.user_ids = [...props.enrolledUserIds];

    // Initial fetch to populate the list when the page loads (equivalent to previous prop fetch)
    await fetchUsers(); 
    
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
                        <CardContent class="p-6 space-y-4">
                            
                            <div>
                                <Label for="search-users">Search Users</Label>
                                <TextInput
                                    id="search-users"
                                    v-model="searchTerm"
                                    type="text"
                                    placeholder="Search by name or email..."
                                    class="mt-1 block w-full"
                                />
                            </div>

                            <InputError class="mt-2" :message="form.errors.user_ids" />
                            
                            <div class="space-y-2 max-h-[60vh] overflow-y-auto border rounded-md p-4">
                                <div class="flex items-center justify-between font-semibold sticky top-0 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 py-2 -mx-2 px-2">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">User</span>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Enrolled</span>
                                </div>

                                <div v-if="loadingUsers" class="text-center text-primary py-4">
                                    <svg class="animate-spin h-5 w-5 mr-3 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Searching...
                                </div>

                                <div v-else-if="userList.length > 0" class="divide-y divide-gray-100 dark:divide-gray-800">
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
                                </div>

                                <div v-else class="text-center text-gray-500 py-4">
                                    No users found matching your criteria.
                                </div>
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

        <Toaster />
    </TeacherLayout>
</template>
<script setup lang="ts">
import LaravelPagination from "@/Components/ui/LaravelPagination.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Course, Paginated } from "@/types";
import { Head } from "@inertiajs/vue3";
import CreateOrganisationForm from "./Organisation/Partials/CreateOrganisationForm.vue";

const props = defineProps<{
    courses: Paginated<Course>;
}>();
console.log(props);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="py-12">
            <!-- <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 ">
                        You're logged in!
                    </div>
                </div>
            </div> -->

            <div class="container">
                <CreateOrganisationForm
                    v-if="!$page.props.auth.user.organisation_id"
                />
                <div v-else class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <!-- <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                    >
                    <div class="p-6 ">
                        You're logged in!
                    </div>
                </div> -->
                    Here are your courses

                    <div v-for="course in courses.data">
                        {{ course.title }}
                    </div>

                    <LaravelPagination :items="courses" class="py-6" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

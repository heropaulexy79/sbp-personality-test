<script setup lang="ts">
import LaravelPagination from "@/Components/ui/LaravelPagination.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Course, Paginated } from "@/types";
import { Head, Link, usePage } from "@inertiajs/vue3";
import CreateOrganisationForm from "./Organisation/Partials/CreateOrganisationForm.vue";
import CourseCard from "./Course/Partials/CourseCard.vue";

const page = usePage();

const props = defineProps<{
    courses: Paginated<Course>;
}>();
console.log(props);

const courseNav = [
    {
        active:
            page.props.query?.["status"] === "progress" ||
            !page.props.query?.["status"],

        href: route("dashboard", {
            _query: {
                ...page.props.query,
                status: "progress",
            },
        }),
        label: "In progress",
    },
    {
        active: page.props.query?.["status"] === "completed",

        href: route("dashboard", {
            _query: {
                ...page.props.query,
                status: "completed",
            },
        }),
        label: "Completed",
    },
];
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
                    <!-- <h2 class="text-lg font-medium">Here are your courses</h2> -->

                    <Transition name="fade" mode="out-in" appear>
                        <div
                            :key="$page.props.query?.['status']"
                            class="mb-6 inline-flex h-9 items-center justify-center rounded-lg border border-primary bg-muted p-1 text-muted-foreground"
                        >
                            <Link
                                v-for="item in courseNav"
                                :data-state="
                                    item.active ? 'active' : 'inactive'
                                "
                                :href="item.href"
                                preserve-scroll
                                class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-primary px-3 py-1 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-primary data-[state=active]:text-primary-foreground data-[state=active]:shadow"
                            >
                                {{ item.label }}
                            </Link>
                        </div>
                    </Transition>

                    <ul class="space-y-4">
                        <li v-for="course in courses.data">
                            <CourseCard :course="course" />
                        </li>
                    </ul>

                    <LaravelPagination
                        v-if="courses.total > courses.data.length"
                        :items="courses"
                        class="py-6 flex justify-center"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

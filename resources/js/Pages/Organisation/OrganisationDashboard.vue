<script setup lang="ts">
import LaravelPagination from "@/Components/ui/LaravelPagination.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Course, Paginated } from "@/types";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import CreateOrganisationForm from "./Partials/CreateOrganisationForm.vue";
import CourseCard from "../Course/Partials/CourseCard.vue";

const page = usePage();

const props = defineProps<{
  courses: Paginated<Course>;
}>();
// console.log(props);

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
  // page.props.auth.user.role === "ADMIN"
  //     ? {
  //           active: page.props.query?.["status"] === "all_enrolled",

  //           href: route("dashboard", {
  //               _query: {
  //                   ...page.props.query,
  //                   status: "all_enrolled",
  //               },
  //           }),
  //           label: "All enrolled",
  //       }
  //     : undefined,
].filter(Boolean) as {
  active: boolean;
  href: string;
  label: string;
}[];
</script>

<template>
  <Head title="Dashboard" />
  <AuthenticatedLayout>
    <div class="py-12">
      <!-- <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xs sm:rounded-lg"
                >
                    <div class="p-6 ">
                        You're logged in!
                    </div>
                </div>
            </div> -->
      <div class="container">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
          <h2 class="mb-6 text-3xl font-bold">Your Courses</h2>
          <Transition name="fade" mode="out-in" appear>
            <div
              :key="$page.props.query?.['status']"
              class="border-primary bg-muted text-muted-foreground mb-6 inline-flex h-9 items-center justify-center rounded-lg border p-1"
            >
              <Link
                v-for="item in courseNav"
                :data-state="item.active ? 'active' : 'inactive'"
                :href="item.href"
                preserve-scroll
                class="text-primary ring-offset-background hover:bg-muted hover:text-muted-foreground focus-visible:ring-ring data-[state=active]:bg-primary data-[state=active]:text-primary-foreground hover:data-[state=active]:bg-primary/70 inline-flex items-center justify-center rounded-md px-3 py-1 text-sm font-medium whitespace-nowrap transition-all focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-hidden disabled:pointer-events-none disabled:opacity-50 data-[state=active]:shadow-sm"
              >
                {{ item.label }}
              </Link>
            </div>
          </Transition>
          <ul
            class="grid grid-cols-[repeat(auto-fill,minmax(min(100%,300px),1fr))] gap-4"
          >
            <li v-for="course in courses.data">
              <CourseCard
                :course="course"
                :can-view-lesson="
                  $page.props.query['status'] !== 'all_enrolled'
                "
              />
            </li>

            <li v-if="courses.data.length === 0" class="text-center">
              No enrolled courses at the moment
            </li>
          </ul>
          <LaravelPagination
            v-if="courses.total > courses.data.length"
            :items="courses"
            class="flex justify-center py-6"
          />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

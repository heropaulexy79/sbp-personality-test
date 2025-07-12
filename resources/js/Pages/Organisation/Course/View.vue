<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { Course, Lesson } from "@/types";
import ManageCourseLayout from "./Partials/ManageCourseLayout.vue";
import BaseDataTable from "@/Components/ui/BaseDataTable.vue";
import { lessonColumns } from "./Lesson/Partials/lesson-column";
import { cn } from "@/lib/utils";
import { buttonVariants } from "@/Components/ui/button";
import LessonDataTable from "./Lesson/Partials/LessonDataTable.vue";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import UpdateCourseResourcesForm from "./Partials/UpdateCourseResourcesForm.vue";

defineProps<{ course: Course; lessons: Lesson[] }>();
</script>

<template>
  <Head :title="course.title" />

  <AuthenticatedLayout>
    <ManageCourseLayout :course="course">
      <div class="py-4">
        <div class="container">
          <div class="space-y-6">
            <Card>
              <CardHeader>
                <header class="flex items-center justify-between gap-6">
                  <div>
                    <CardTitle>Lessons</CardTitle>

                    <!-- <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
Update your account's profile information and email address.
</p> -->
                  </div>

                  <div>
                    <Link
                      :href="
                        route('lesson.create', {
                          course: course.id,
                        })
                      "
                      :class="cn(buttonVariants())"
                    >
                      Create lesson
                    </Link>
                  </div>
                </header>
              </CardHeader>

              <CardContent>
                <LessonDataTable :columns="lessonColumns" :data="lessons" />
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <div>
                  <CardTitle>Resources</CardTitle>
                  <!-- <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
Update your account's profile information and email address.
</p> -->
                </div>
              </CardHeader>

              <CardContent>
                <UpdateCourseResourcesForm :course="course" />
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </ManageCourseLayout>
  </AuthenticatedLayout>
</template>

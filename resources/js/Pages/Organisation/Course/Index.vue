<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import CourseTable from "./Partials/CourseTable.vue";
import { Course } from "@/types";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/Components/ui/dialog";
import { VisuallyHidden } from "radix-vue";
import { Button } from "@/Components/ui/button";
import CreateCourseForm from "./Partials/CreateCourseForm.vue";

defineProps<{ courses: Course[] }>();
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="container">
                <div class="space-y-6">
                    <header class="flex items-start gap-6 justify-between">
                        <div>
                            <h2 class="text-lg font-medium">Courses</h2>

                            <!-- <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
Update your account's profile information and email address.
</p> -->
                        </div>

                        <Dialog>
                            <DialogTrigger>
                                <Button>Create course</Button>
                            </DialogTrigger>
                            <DialogContent class="max-w-[768px]">
                                <VisuallyHidden>
                                    <DialogHeader
                                        aria-hidden="true"
                                        hidden
                                        class="invisible h-0 w-0"
                                    >
                                        <DialogTitle>
                                            Create a course
                                        </DialogTitle>
                                        <DialogDescription>
                                            <!-- Create a course -->
                                        </DialogDescription>
                                    </DialogHeader>
                                </VisuallyHidden>
                                <CreateCourseForm
                                    :organisation_id="
                                    $page.props.auth.user
                                    .organisation_id!
                                    "
                                />
                            </DialogContent>
                        </Dialog>
                    </header>

                    <CourseTable :courses="courses" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

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
import BaseBreadcrumb from "@/Components/BaseBreadcrumb.vue";

defineProps<{ courses: Course[] }>();
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <header class="bg-white dark:bg-gray-800 shadow">
            <div class="container py-6 flex items-center justify-between">
                <div>
                    <BaseBreadcrumb
                        :items="[
                            {
                                label: 'Courses',
                            },
                        ]"
                    />
                </div>
                <Dialog>
                    <DialogTrigger>
                        <Button size="sm">Create course</Button>
                    </DialogTrigger>
                    <DialogContent class="max-w-[768px]">
                        <VisuallyHidden>
                            <DialogHeader
                                aria-hidden="true"
                                hidden
                                class="invisible h-0 w-0"
                            >
                                <DialogTitle> Create a course </DialogTitle>
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
            </div>
        </header>
        <div class="py-12">
            <div class="container">
                <div class="space-y-6">
                    <CourseTable :courses="courses" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

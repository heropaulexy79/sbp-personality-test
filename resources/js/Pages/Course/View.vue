<script lang="ts" setup>
import { Button } from "@/Components/ui/button";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Course, Lesson } from "@/types";
import { Head, router, useForm } from "@inertiajs/vue3";
import { ArrowRightIcon } from "lucide-vue-next";

const props = defineProps<{
    course: Course;
    enrolled_count: number;
    lessons: Pick<Lesson, "title" | "slug" | "id" | "type">[];
}>();
const form = useForm({});

function enrollInCourse() {
    form.post(route("course.enroll", { course: props.course.slug }), {
        onSuccess(E) {
            router.visit(
                route("classroom.lesson.index", {
                    course: props.course.slug,
                }),
            );
        },
    });
}
</script>

<template>
    <Head :title="course.title" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="container">
                <h2 class="mb-5 text-xl font-bold lg:text-2xl">
                    {{ course.title }}
                </h2>

                <!-- Todo: slugify -->
                <div class="grid gap-10 lg:grid-cols-[1fr_350px]">
                    <div v-html="course.description" class="prose" />

                    <div
                        class="sticky top-0 mt-5 w-full self-start bg-background px-4 py-4"
                    >
                        <div class="mb-4">
                            Last updated on
                            {{
                                Intl.DateTimeFormat(undefined, {
                                    month: "long",
                                    day: "2-digit",
                                    year: "numeric",
                                }).format(
                                    new Date(
                                        course.updated_at ?? course.created_at,
                                    ),
                                )
                            }}
                        </div>

                        <form @submit.prevent="enrollInCourse">
                            <Button
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                <span>Enroll now</span
                                ><ArrowRightIcon :size="16" />
                            </Button>
                            <div v-if="enrolled_count > 5">
                                {{
                                    Intl.NumberFormat(undefined).format(
                                        enrolled_count,
                                    )
                                }}
                                already enrolled
                            </div>
                        </form>
                    </div>
                </div>

                <div class="pt-12">
                    <h3 class="mb-5 text-base font-semibold lg:text-lg">
                        What you would learn
                    </h3>

                    <ul>
                        <li
                            v-for="(lesson, index) in lessons"
                            class="flex items-center gap-2"
                        >
                            <div
                                class="grid size-10 place-content-center rounded-full border-2 border-border"
                            >
                                {{ index + 1 }}
                            </div>
                            <div>{{ lesson.title }}</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script lang="ts" setup>
import { Button } from "@/Components/ui/button";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Course, Lesson } from "@/types";
import { Head, useForm } from "@inertiajs/vue3";
import { ArrowRightIcon } from "lucide-vue-next";

const props = defineProps<{
    course: Course;
    enrolled_count: number;
    lessons: Pick<Lesson, "title" | "id" | "type">[];
}>();
const form = useForm({});

function enrollInCourse() {
    form.post(route("course.enroll", { course: props.course.id }));
}
</script>

<template>
    <Head :title="course.title" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="container">
                <h2 class="text-xl lg:text-2xl font-bold mb-5">
                    {{ course.title }}
                </h2>

                <!-- Todo: slugify -->
                <div v-html="course.description" class="prose lg:prose-xl" />

                <form class="mt-5" @submit.prevent="enrollInCourse">
                    <Button
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        <span>Enroll now</span><ArrowRightIcon :size="16" />
                    </Button>
                    <div v-if="enrolled_count > 5">
                        {{
                            Intl.NumberFormat(undefined).format(enrolled_count)
                        }}
                        already enrolled
                    </div>
                </form>

                <div class="pt-12">
                    <h3 class="text-base lg:text-lg font-semibold mb-5">
                        What you would learn
                    </h3>

                    <ul>
                        <li
                            v-for="(lesson, index) in lessons"
                            class="flex items-center gap-2"
                        >
                            <div
                                class="size-10 grid place-content-center border-2 border-border rounded-full"
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

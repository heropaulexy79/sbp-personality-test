<script lang="ts" setup>
import { Course, Lesson } from "@/types";
import { WithCompleted } from "./types";
import { Link } from "@inertiajs/vue3";
import { BookOpenText, Check, Star } from "lucide-vue-next";

defineProps<{
    course: Course;
    lessons: WithCompleted<Omit<Lesson, "content" | "content_json">>[];
}>();
</script>

<template>
    <div>
        <nav class="px-0 py-6">
            <ul class="space-y-2">
                <li v-for="lesson in lessons">
                    <Link
                        :href="
                            route('classroom.lesson.show', {
                                course: course.id,
                                lesson: lesson.id,
                            })
                        "
                        :data-active="
                            route().current('classroom.lesson.show', {
                                course: course.id,
                                lesson: lesson.id,
                            })
                        "
                        class="group flex w-full items-center gap-2 border-l-[6px] border-l-transparent px-3 py-1 data-[active='true']:border-l-primary data-[active='true']:bg-accent data-[active='true']:text-accent-foreground"
                    >
                        <span
                            class="flex size-7 items-center justify-center rounded-full leading-none data-[completed='true']:bg-primary data-[completed='true']:text-primary-foreground group-data-[active='true']:bg-primary group-data-[active='true']:text-primary-foreground"
                            :data-completed="lesson.completed"
                        >
                            <Check v-if="lesson.completed" class="size-4" />
                            <template v-else>
                                <Star
                                    v-if="lesson.type === 'QUIZ'"
                                    class="size-4"
                                />
                                <BookOpenText v-else class="size-4" />
                            </template>
                        </span>
                        <span>{{ lesson.title }}</span>
                    </Link>
                </li>
            </ul>
        </nav>
    </div>
</template>

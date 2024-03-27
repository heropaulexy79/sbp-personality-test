<script lang="ts" setup>
import { buttonVariants } from "@/Components/ui/button";
import { cn } from "@/lib/utils";
import { Course } from "@/types";
import { Link } from "@inertiajs/vue3";
import { ArrowRight } from "lucide-vue-next";

defineProps<{ course: Pick<Course, "id" | "title" | "slug"> }>();
</script>

<template>
    <div
        class="flex items-center justify-between rounded-lg bg-background p-4 shadow transition-all"
    >
        <div class="flex items-center gap-5">
            <div class="size-32 rounded-md bg-gray-400"></div>
            <Link
                :href="route('classroom.lesson.index', { course: course.slug })"
                :class="
                    cn(
                        buttonVariants({
                            variant: 'link',
                            size: 'lg',
                            class: 'px-0',
                        }),
                    )
                "
            >
                <h3 class="text-lg font-medium">{{ course.title }}</h3>
            </Link>
        </div>

        <div>
            <!--  -->
            <Link
                :href="route('classroom.lesson.index', { course: course.slug })"
                :class="cn(buttonVariants({ size: 'lg' }))"
            >
                <span>Go to course</span>
                <ArrowRight :size="16" />
            </Link>
        </div>
    </div>
</template>

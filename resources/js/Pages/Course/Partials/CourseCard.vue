<script lang="ts" setup>
import { buttonVariants } from "@/Components/ui/button";
import { cn } from "@/lib/utils";
import { Course } from "@/types";
import { Link } from "@inertiajs/vue3";
import { ArrowRight } from "lucide-vue-next";

withDefaults(
    defineProps<{
        course: Pick<Course, "id" | "title" | "slug" | "banner_image">;
        canViewLesson: boolean;
    }>(),
    {
        canViewLesson: true,
    },
);
</script>

<template>
    <div
        class="flex items-center justify-between rounded-lg bg-background p-4 shadow transition-all"
    >
        <div class="flex items-center gap-5">
            <div
                class="size-32 rounded-md bg-gray-400 bg-cover"
                :style="{
                    backgroundImage: course.banner_image
                        ? `url(${course.banner_image})`
                        : 'unset',
                }"
            ></div>
            <Link
                :href="
                    canViewLesson
                        ? route('classroom.lesson.index', {
                              course: course.slug,
                          })
                        : route('public.course.show', { course: course.slug })
                "
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

        <div
            class="flex flex-col"
            v-if="$page.props.auth.user.role === 'ADMIN'"
        >
            <!--  -->
            <Link
                :href="
                    canViewLesson
                        ? route('classroom.lesson.index', {
                              course: course.slug,
                          })
                        : route('public.course.show', { course: course.slug })
                "
                :class="cn(buttonVariants({ size: 'lg' }))"
            >
                <span>Go to course</span>
                <ArrowRight :size="16" />
            </Link>
            <Link
                :href="
                    route('organisation.course.leaderboard', {
                        course: course.slug,
                    })
                "
                :class="cn(buttonVariants({ size: 'lg', variant: 'link' }))"
            >
                <span>View leaderboard</span>
            </Link>
        </div>
    </div>
</template>

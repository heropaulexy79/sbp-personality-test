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
    <div class="@container">
        <div class="@md:p-4 rounded-lg bg-background p-2 shadow transition-all">
            <div class="@md:items-center @lg:flex-row flex flex-col gap-5">
                <div
                    class="@lg:size-32 aspect-video w-full rounded-md bg-gray-400 bg-cover"
                    :style="{
                        backgroundImage: course.banner_image
                            ? `url(${course.banner_image})`
                            : 'unset',
                    }"
                ></div>
                <div
                    class="@lg:flex-row @md:justify-between flex flex-1 flex-col gap-6"
                >
                    <Link
                        :href="
                            canViewLesson
                                ? route('classroom.lesson.index', {
                                      course: course.slug,
                                  })
                                : route('public.course.show', {
                                      course: course.slug,
                                  })
                        "
                    >
                        <h3 class="text-lg font-medium">{{ course.title }}</h3>
                    </Link>

                    <div
                        class="@md:max-w-52 @xs:flex-row @lg:flex-col @md:mt-0 mt-4 flex flex-col"
                        v-if="$page.props.auth.user.role === 'ADMIN'"
                    >
                        <Link
                            :href="
                                canViewLesson
                                    ? route('classroom.lesson.index', {
                                          course: course.slug,
                                      })
                                    : route('public.course.show', {
                                          course: course.slug,
                                      })
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
                            :class="
                                cn(
                                    buttonVariants({
                                        size: 'lg',
                                        variant: 'link',
                                    }),
                                )
                            "
                        >
                            <span>View leaderboard</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

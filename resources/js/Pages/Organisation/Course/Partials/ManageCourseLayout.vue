<script lang="ts" setup>
import { buttonVariants } from "@/Components/ui/button";
import { cn } from "@/lib/utils";
import { Course } from "@/types";
import { Link } from "@inertiajs/vue3";

defineProps<{ course: Course }>();
</script>

<template>
    <header class="bg-white dark:bg-gray-800 shadow">
        <div
            class="max-w-7xl py-6 mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between"
        >
            <Link
                :href="
                    route('course.show', {
                        course: course.id,
                    })
                "
            >
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    {{ course.title }}
                </h2>
            </Link>

            <div>
                <Link
                    :href="
                        route('course.edit', {
                            course: course.id,
                        })
                    "
                    :class="cn(buttonVariants())"
                >
                    Settings
                </Link>
            </div>
        </div>
    </header>

    <div class="mt-6">
        <slot />
    </div>
</template>

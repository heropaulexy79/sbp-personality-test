<script lang="ts" setup>
import BaseBreadcrumb from "@/Components/ui/BaseBreadcrumb.vue";
import { buttonVariants } from "@/Components/ui/button";
import { cn } from "@/lib/utils";
import { Course } from "@/types";
import { Link } from "@inertiajs/vue3";

defineProps<{ course: Course }>();
</script>

<template>
    <header class="bg-white dark:bg-gray-800 shadow">
        <div class="container py-6 flex items-center justify-between">
            <div>
                <BaseBreadcrumb
                    :items="[
                        {
                            href: route('course.index'),
                            label: 'Courses',
                        },
                        { label: course.title },
                    ]"
                />
            </div>

            <div>
                <Link
                    :href="
                        route('course.edit', {
                            course: course.id,
                        })
                    "
                    :class="cn(buttonVariants({ size: 'sm' }))"
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

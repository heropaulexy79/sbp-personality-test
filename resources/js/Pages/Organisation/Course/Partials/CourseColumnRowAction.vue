<script setup lang="ts">
import { Button } from "@/Components/ui/button";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";
import { Course } from "@/types";
import { router, usePage } from "@inertiajs/vue3";
import { EllipsisIcon } from "lucide-vue-next";

const props = defineProps<{
    course: Course;
}>();

const page = usePage();
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon">
                <EllipsisIcon class="size-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent class="w-48" align="end">
            <DropdownMenuLabel>
                <div
                    class="text-sm font-medium text-gray-800 dark:text-gray-200"
                >
                    Actions
                </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem
                @select="
                    () =>
                        router.visit(
                            route('organisation.course.leaderboard', {
                                course,
                            }),
                        )
                "
            >
                View leaderboard
            </DropdownMenuItem>
            <DropdownMenuItem
                @select="() => router.visit(route('course.edit', { course }))"
            >
                Manage course
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>

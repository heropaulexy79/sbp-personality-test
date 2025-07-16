<script lang="ts" setup>
import { Course, Lesson } from "@/types";
import { WithUserLesson } from "./types";
import { Link } from "@inertiajs/vue3";
import {
  BookOpen,
  BookOpenText,
  Check,
  CheckCircle,
  Circle,
  Star,
} from "lucide-vue-next";
import {
  SidebarGroup,
  SidebarGroupContent,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from "@/Components/ui/sidebar";

const props = defineProps<{
  course: Course;
  lessons: WithUserLesson<Omit<Lesson, "content" | "content_json">>[];
}>();
</script>

<template>
  <SidebarGroup>
    <SidebarGroupLabel class="flex items-center gap-2">
      <BookOpen class="h-4 w-4" />
      Course Lessons
    </SidebarGroupLabel>
    <SidebarGroupContent>
      <SidebarMenu>
        <SidebarMenuItem v-for="lesson in lessons" :key="lesson.id">
          <SidebarMenuButton
            :data-active="
              route().current('classroom.lesson.show', {
                course: course.slug,
                lesson: lesson.slug,
              })
            "
            class="group flex w-full items-center gap-2"
            as-child
          >
            <Link
              :href="
                route('classroom.lesson.show', {
                  course: course.slug,
                  lesson: lesson.slug,
                })
              "
              class="group flex w-full items-center gap-2"
            >
              <CheckCircle
                v-if="lesson.completed"
                class="text-primary h-3 w-3"
              />
              <template v-else>
                <Star v-if="lesson.type === 'QUIZ'" />
                <Circle class="border-muted-foreground" v-else />
                <!-- <BookOpenText  /> -->
              </template>

              <span class="truncate">{{ lesson.title }}</span>

              <!-- <span
                class="data-[completed='true']:text-primary group-data-[active='true']:bg-primary group-data-[active='true']:text-primary-foreground flex size-6 items-center justify-center rounded-full leading-none [&_svg]:size-4"
                :data-completed="lesson.completed"
              >
                <Check v-if="lesson.completed" />
                <template v-else>
                  <Star v-if="lesson.type === 'QUIZ'" />
                  <BookOpenText v-else />
                </template>
              </span>
              <span class="truncate">{{ lesson.title }}</span> -->
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarGroupContent>
  </SidebarGroup>
</template>

<script lang="ts" setup>
import { Course, Lesson } from "@/types";
import { WithUserLesson } from "./types";
import { Link } from "@inertiajs/vue3";
import { ExternalLink, FileText } from "lucide-vue-next";
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
      <FileText class="h-4 w-4" />
      Course Resources
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
              <ExternalLink className="h-4 w-4" />
              <span class="truncate">{{ lesson.title }}</span>
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarGroupContent>
  </SidebarGroup>
</template>

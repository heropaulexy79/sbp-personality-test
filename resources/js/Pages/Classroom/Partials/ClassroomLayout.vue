<script lang="ts" setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Course, Lesson } from "@/types";
import { Head, Link } from "@inertiajs/vue3";
import LessonContent from "./LessonContent.vue";
import LessonsSidenav from "./LessonsSidenav.vue";
import { computed } from "vue";
import { WithUserLesson } from "./types";
import {
  Sidebar,
  SidebarContent,
  SidebarHeader,
  SidebarInset,
  SidebarMenu,
  SidebarMenuItem,
  SidebarMenuButton,
  SidebarProvider,
  SidebarRail,
  SidebarTrigger,
} from "@/Components/ui/sidebar";
import { ChevronLeft, ChevronLeftIcon } from "lucide-vue-next";
import { buttonVariants } from "@/Components/ui/button";
import { Separator } from "@/Components/ui/separator";
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbSeparator,
  BreadcrumbPage,
  BreadcrumbLink,
  BreadcrumbList,
} from "@/Components/ui/breadcrumb";
import { Progress } from "@/Components/ui/progress";
import ResourcesSidenav from "./ResourcesSidenav.vue";

const props = defineProps<{
  course: Course;
  lessons: WithUserLesson<Omit<Lesson, "content" | "content_json">>[];
  lesson?: WithUserLesson<Lesson>;
}>();

const completed = computed(() => {
  return props.lesson
    ? props.lessons.findIndex((r) => r.id === props.lesson?.id) + 1
    : props.lessons.length;
});

const progress = computed(() => {
  return Math.abs(Math.floor((completed.value / props.lessons.length) * 100));
});
</script>

<template>
  <Head :title="lesson?.title || course.title" />

  <div class="relative">
    <!-- class="min-h-[calc(100svh-65px)]" -->
    <SidebarProvider>
      <!-- class="top-[65px] h-[calc(100svh-65px)]" -->
      <!-- collapsible="icon" -->
      <Sidebar off-canvas-class="h-full">
        <SidebarHeader>
          <SidebarMenu>
            <SidebarMenuItem>
              <SidebarMenuButton
                size="lg"
                class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
              >
                <div class="grid flex-1 text-left text-sm leading-tight">
                  <h2 class="text-lg font-semibold">
                    {{ course.title }}
                  </h2>
                </div>
              </SidebarMenuButton>
            </SidebarMenuItem>
          </SidebarMenu>
          <div class="space-y-2">
            <div class="space-y-1">
              <div class="text-muted-foreground flex justify-between text-sm">
                <span>Progress</span>
                <span>{{ progress }}%</span>
              </div>
              <Progress :modelValue="progress" class="h-2" />
            </div>
          </div>
        </SidebarHeader>

        <SidebarContent>
          <LessonsSidenav :course="course" :lessons="lessons" />
          <ResourcesSidenav :resources="course.metadata?.resources ?? []" />
        </SidebarContent>

        <SidebarRail />
      </Sidebar>

      <SidebarInset>
        <header
          className="flex h-16 sticky top-0 bg-sidebar shrink-0 items-center gap-2 border-b px-4"
        >
          <SidebarTrigger class="-ml-1" />
          <Separator orientation="vertical" className="mr-2 h-4" />
          <Breadcrumb>
            <BreadcrumbList>
              <BreadcrumbItem>
                <BreadcrumbPage>{{
                  lesson?.title || course.title
                }}</BreadcrumbPage>
              </BreadcrumbItem>
            </BreadcrumbList>
          </Breadcrumb>
        </header>

        <div class="bg-white">
          <slot />
        </div>
      </SidebarInset>
    </SidebarProvider>
  </div>
</template>

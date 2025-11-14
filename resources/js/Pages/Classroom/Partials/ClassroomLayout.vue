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
  course: Course | null; // Updated prop type to allow null
  lessons: WithUserLesson<Omit<Lesson, "content" | "content_json">>[];
  lesson?: WithUserLesson<Lesson>;
}>();

// Safely provide a title when course is null
const displayedCourseTitle = computed(() => {
    return props.course ? props.course.title : 'Standalone Quiz';
});

// Safely provide resources (empty array if course/metadata is missing)
const courseResources = computed(() => {
    return props.course?.metadata?.resources ?? [];
});

// Calculate progress (Handles empty lessons array if course is null)
const completed = computed(() => {
  return props.lesson
    ? props.lessons.findIndex((r) => r.id === props.lesson?.id) + 1
    : props.lessons.length;
});

const progress = computed(() => {
  // Use 1 as denominator if lessons.length is 0 to avoid division by zero
  const totalLessons = props.lessons.length || 1; 
  return Math.abs(Math.floor((completed.value / totalLessons) * 100));
});
</script>

<template>
  <!-- Use computed title for safety -->
  <Head :title="lesson?.title || displayedCourseTitle" />

  <div class="relative">
    <SidebarProvider>
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
                    <!-- FIX: Use computed property here -->
                    {{ displayedCourseTitle }}
                  </h2>
                </div>
              </SidebarMenuButton>
            </SidebarMenuItem>
          </SidebarMenu>
          <!-- Only show progress if a course is present and there are lessons -->
          <div v-if="course && lessons.length > 0" class="space-y-2">
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
          <!-- Only render LessonsSidenav if there is a course or lessons are provided (as per original logic) -->
          <LessonsSidenav v-if="course" :course="course" :lessons="lessons" />
          
          <!-- FIX: Use computed property for resources -->
          <ResourcesSidenav :resources="courseResources" />
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
                <BreadcrumbPage>
                  <!-- FIX: Use computed property here -->
                  {{ lesson?.title || displayedCourseTitle }}
                </BreadcrumbPage>
              </BreadcrumbItem>
            </BreadcrumbList>
          </Breadcrumb>
        </header>

        <div class="bg-background">
          <slot />
        </div>
      </SidebarInset>
    </SidebarProvider>
  </div>
</template>
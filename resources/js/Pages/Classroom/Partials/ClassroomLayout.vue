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

const completed = props.lessons.reduce((p, c) => {
  return c.completed ? p + 1 : p;
}, 0);

const progress = Math.floor((completed / props.lessons.length) * 100);
</script>

<template>
  <Head :title="lesson?.title || course.title" />
  <AuthenticatedLayout :is-fullscreen="true">
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
                  <Link
                    :href="route('dashboard')"
                    :class="
                      buttonVariants({
                        // size: 'icon',
                        size: 'sm',
                        variant: 'ghost',
                        class: 'group/back-btn shrink-0',
                      })
                    "
                  >
                    <ChevronLeft
                      class="transition-all group-hover/back-btn:-translate-x-2"
                    />
                    <span class=""> My Courses </span>
                  </Link>

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
                  <BreadcrumbLink as-child>
                    <Link :href="route('dashboard')"> My Courses </Link>
                  </BreadcrumbLink>
                </BreadcrumbItem>
                <BreadcrumbSeparator className="hidden md:block" />
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
  </AuthenticatedLayout>
</template>

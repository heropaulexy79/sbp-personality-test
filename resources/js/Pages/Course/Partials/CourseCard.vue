<script lang="ts" setup>
import { buttonVariants } from "@/Components/ui/button";
import { cn } from "@/lib/utils";
import { Course } from "@/types";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Link } from "@inertiajs/vue3";
import { ArrowRight } from "lucide-vue-next";
import CardFooter from "@/Components/ui/card/CardFooter.vue";

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
  <Card class="group relative pt-0">
    <div class="bg-alternate aspect-video overflow-clip rounded-lg">
      <img
        :src="course.banner_image || undefined"
        :v-if="course.banner_image"
        class="w-full bg-cover transition-transform duration-500 group-hover:scale-[1.1]"
      />
    </div>

    <CardHeader>
      <!-- <Link
        :href="
          canViewLesson
            ? route('classroom.lesson.index', {
                course: course.slug,
              })
            : route('public.course.show', {
                course: course.slug,
              })
        "
      > -->
      <CardTitle>{{ course.title }}</CardTitle>
      <!-- </Link> -->
    </CardHeader>
    <!-- <CardContent> hi </CardContent> -->
    <CardFooter class="flex-col">
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
        class="group w-full"
        :class="cn(buttonVariants({ size: 'lg' }))"
      >
        <span class="absolute inset-0" />
        <span>Go to course</span>
        <ArrowRight
          :size="16"
          class="transition-all duration-500 group-hover:translate-x-2"
        />
      </Link>

      <!-- <Link
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
      </Link> -->
    </CardFooter>
  </Card>
</template>

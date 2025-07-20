<script setup lang="ts">
import { Button, buttonVariants } from "@/Components/ui/button";
import { Course } from "@/types";
import { Head, Link } from "@inertiajs/vue3";
import { ArrowRight, ChevronRight } from "lucide-vue-next";

defineProps<{
  course: Course;
  quiz: {
    title: string;
    description: string;
  };
}>();
</script>

<template>
  <Head :title="course.title" />
  <div>
    <div class="flex min-h-svh items-center justify-center">
      <div
        class="container mx-auto px-4 py-24 md:px-6 lg:py-32 2xl:max-w-[1400px]"
      >
        <div class="flex justify-center">
          <div
            class="inline-flex items-center gap-x-2 rounded-full border p-1 ps-3 text-sm transition"
          >
            Personality Quiz

            <span
              class="bg-muted-foreground/15 inline-flex items-center justify-center gap-x-2 rounded-full px-2.5 py-1.5 text-sm font-semibold"
            >
              <ChevronRight :size="16" />
            </span>
          </div>
        </div>

        <div class="mx-auto mt-5 max-w-2xl text-center">
          <h1
            class="scroll-m-20 text-4xl font-extrabold tracking-tight lg:text-5xl"
          >
            {{ quiz.title }}
          </h1>
        </div>

        <div class="mx-auto mt-5 max-w-3xl text-center">
          <div
            class="text-muted-foreground prose prose-sm mx-auto text-center"
            v-html="quiz.description"
          />
          <!-- {{ quiz.description }} -->
          <!-- </div> -->
        </div>

        <div class="mt-8 flex justify-center gap-3">
          <Link
            :href="route('classroom.personality.show', { course: course.slug })"
            :class="
              buttonVariants({
                size: 'lg',
                class: 'h-12 px-8 text-lg font-medium',
              })
            "
          >
            Take Quiz
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { Button, buttonVariants } from "@/Components/ui/button";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import { Course, Lesson } from "@/types";
import { Head, router, useForm } from "@inertiajs/vue3";
import { ArrowRightIcon } from "lucide-vue-next";
import EnrollTeamInCourseForm from "../Organisation/Course/EnrollTeamInCourseForm.vue";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/Components/ui/dialog";
import { ref } from "vue";
import RegisterForm from "../Auth/Partials/RegisterForm.vue";
import { usePage } from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import {
  Card,
  CardContent,
  CardHeader,
  CardFooter,
  CardTitle,
  CardDescription,
} from "@/Components/ui/card";
import { Separator } from "@/Components/ui/separator";
import { Link } from "@inertiajs/vue3";

const page = usePage();
const props = defineProps<{
  course: Course;
  enrolled_count: number;
  lessons: Pick<Lesson, "title" | "slug" | "id" | "type">[];
}>();
</script>

<template>
  <Head :title="course.title" />

  <PublicLayout>
    <section
      v-if="!$page.props.auth.user"
      class="relative flex w-full items-center bg-white py-8"
    >
      <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <nav class="flex items-center justify-center">
          <div class="flex items-center">
            <ApplicationLogo
              class="text-primary dark:text-primary-foreground block h-6 max-h-9 w-auto fill-current"
            />
          </div>
        </nav>
      </div>
    </section>

    <div class="py-12">
      <div>
        <div class="relative container">
          <div class="grid gap-10 md:grid-cols-[1fr_350px]">
            <div>
              <div class="group mb-6 overflow-clip rounded-lg">
                <img
                  :src="course.banner_image || undefined"
                  :v-if="course.banner_image"
                  class="w-full object-cover transition-transform duration-500 group-hover:scale-[1.1]"
                />
              </div>

              <h2 class="mb-6 text-xl font-bold lg:text-3xl">
                {{ course.title }}
              </h2>
              <div
                v-html="course.description"
                class="prose prose-lg dark:prose-invert max-w-none"
              />
            </div>

            <Card class="sticky top-16 self-start">
              <CardHeader>
                <CardTitle> Ready to Dive In? </CardTitle>
                <CardDescription> Take course to get started! </CardDescription>
              </CardHeader>
              <Separator />
              <CardContent> </CardContent>
              <Separator />
              <CardFooter class="bor">
                <Link
  :href="route('course.enroll', { course: course.slug })"
  method="post"
  as="button"
  class="group w-full gap-2"
  :class="buttonVariants({ size: 'lg' })"
>
  <span>Take course</span>
  <ArrowRightIcon
    class="size-4 transition-transform group-hover:scale-110"
  />
</Link>
              </CardFooter>
            </Card>
          </div>
        </div>
      </div>
    </div>

    <footer class="">
      <div class="container">
        <div class="mb-10 flex justify-between gap-6">
          <div class="hidden max-w-[500px] sm:block">
            <!-- <Link
              class="text-muted-foreground hover:text-foreground focus:text-foreground inline-flex gap-x-2 focus:outline-hidden"
              :href="route('website.terms')"
            >
            </Link> -->

            <!-- <p class="text-muted-foreground mt-3 text-xs sm:text-sm">
            © {{ new Date().getFullYear() }} Neukleos Studios.
          </p> -->
          </div>
        </div>
      </div>
    </footer>
  </PublicLayout>
</template>

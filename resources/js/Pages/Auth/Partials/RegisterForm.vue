<script setup lang="ts">
import InputError from "@/Components/InputError.vue";
import { Button, buttonVariants } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { cn } from "@/lib/utils";
import { Course } from "@/types";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";

const props = defineProps<{
  prefilled?: {
    email?: string;
    course?: Pick<Course, "id" | "slug">;
  };
}>();

const form = useForm({
  name: "",
  email:
    (props?.prefilled?.email?.trim().length ?? 0) > 0
      ? props.prefilled?.email?.toLowerCase()
      : "",
  password: "",
  password_confirmation: "",
  course_id: props.prefilled?.course?.id || null,
});

const submit = () => {
  form.post(route("register"), {
    onFinish: () => {
      form.reset("password", "password_confirmation");
    },
  });
};
</script>

<template>
  <form @submit.prevent="submit">
    <div>
      <Label for="name"> Name </Label>

      <Input
        id="name"
        type="text"
        class="mt-1"
        v-model="form.name"
        required
        autofocus
        autocomplete="name"
      />

      <InputError class="mt-2" :message="form.errors.name" />
    </div>

    <div class="mt-4">
      <Label for="email"> Email </Label>

      <Input
        id="email"
        type="email"
        class="mt-1"
        v-model="form.email"
        required
        autocomplete="username"
        :disabled="(props?.prefilled?.email?.trim?.()?.length ?? 0) > 1"
      />

      <InputError class="mt-2" :message="form.errors.email" />
    </div>

    <div class="mt-4">
      <Label for="password"> Password </Label>

      <Input
        id="password"
        type="password"
        class="mt-1"
        v-model="form.password"
        required
        autocomplete="new-password"
      />

      <InputError class="mt-2" :message="form.errors.password" />
    </div>

    <div class="mt-4">
      <Label for="password_confirmation"> Confirm Password </Label>

      <Input
        id="password_confirmation"
        type="password"
        class="mt-1"
        v-model="form.password_confirmation"
        required
        autocomplete="new-password"
      />

      <InputError class="mt-2" :message="form.errors.password_confirmation" />
    </div>

    <div class="mt-8">
      <Button
        class="w-full"
        :class="{ 'opacity-25': form.processing }"
        :disabled="form.processing"
      >
        Get Started
      </Button>
    </div>
  </form>
</template>

<script setup lang="ts">
import { Button } from "@/Components/ui/button";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps<{
  status?: string;
}>();

const form = useForm({});

const submit = () => {
  form.post(route("verification.send"));
};

const verificationLinkSent = computed(
  () => props.status === "verification-link-sent",
);
</script>

<template>
  <GuestLayout>
    <div
      class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0 dark:bg-gray-900"
    >
      <div>
        <Link href="/">
          <ApplicationLogo
            class="text-primary dark:text-primary-foreground h-9 max-h-9 fill-current"
          />
        </Link>
      </div>

      <div
        class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg dark:bg-gray-800"
      >
        <Head title="Email Verification" />

        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
          Thanks for signing up! Before getting started, could you verify your
          email address by clicking on the link we just emailed to you? If you
          didn't receive the email, we will gladly send you another.
        </div>

        <div
          class="mb-4 text-sm font-medium text-green-600 dark:text-green-400"
          v-if="verificationLinkSent"
        >
          A new verification link has been sent to the email address you
          provided during registration.
        </div>

        <form @submit.prevent="submit">
          <div class="mt-4 flex items-center justify-between">
            <Button
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
            >
              Resend Verification Email
            </Button>

            <Link
              :href="route('logout')"
              method="post"
              as="button"
              class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-hidden dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
              >Log Out</Link
            >
          </div>
        </form>
      </div>
    </div>
  </GuestLayout>
</template>

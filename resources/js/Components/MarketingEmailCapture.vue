<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { toast } from "vue-sonner";
import { Input } from "./ui/input";
import { Label } from "./ui/label";
import InputError from "./InputError.vue";
import { setCookie } from "@/Pages/Classroom/Partials/cookie";
import { Button } from "./ui/button";

const emailForm = useForm({
  // New form for email collection
  email: "",
});

const props = withDefaults(
  defineProps<{
    ctaText?: string;
  }>(),
  {
    ctaText: "View Results",
  },
);
const emit = defineEmits(["on-success", "on-error"]);

function submitEmail() {
  // Example of an Inertia request (uncomment and modify for your backend):
  emailForm.post(route("marketing.captureEmail"), {
    // You need to define this route
    onSuccess: (data) => {
      setCookie("email_captured", "true", 7);
      // toast.success("Email submitted successfully!");
      // resultsDialog.value = true; // Show results after email submission
      // showEmailCollection.value = false; // Hide email collection form
      emit("on-success", data);
    },
    onError: (errors) => {
      // console.error("Email submission error:", errors);
      toast.error("Failed to submit email. Please try again.");
      emit("on-error", errors);
    },
  });
}
</script>

<template>
  <form @submit.prevent="submitEmail">
    <!-- <div class="grid gap-4">
      <Label for="name">Email</Label>
      <Input
        id="name"
        type="text"
        v-model="emailForm.name"
        placeholder="Johnny"
        required
      />
      <InputError :message="emailForm.errors.email" />
    </div> -->
    <div class="grid gap-4">
      <Label for="email">Email</Label>
      <Input
        id="email"
        type="email"
        v-model="emailForm.email"
        placeholder="your.email@example.com"
        required
      />
      <InputError :message="emailForm.errors.email" />
    </div>
    <Button type="submit" class="mt-6 w-full" :disabled="emailForm.processing">
      {{ props.ctaText }}
    </Button>
  </form>
</template>

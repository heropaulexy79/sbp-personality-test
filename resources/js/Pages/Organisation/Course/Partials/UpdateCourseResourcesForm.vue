<script setup lang="ts">
import InputError from "@/Components/InputError.vue";
import { RichEditor } from "@/Components/RichEditor";
import UploadMediaForm from "@/Components/UploadMediaForm.vue";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/Components/ui/popover";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/Components/ui/select";
import { Textarea } from "@/Components/ui/textarea";
import { Course } from "@/types";
import { useForm, usePage } from "@inertiajs/vue3";
import { XIcon } from "lucide-vue-next";
import { ref, watch } from "vue";
import { toast } from "vue-sonner";

const props = defineProps<{
  course: Course;
}>();

const form = useForm({
  resources: props.course.metadata?.resources || [],
});

function addResource() {
  form.resources.push({ label: "", url: "" });
}

function removeResource(index: number) {
  form.resources.splice(index, 1);
}

function submit() {
  form.patch(route("courses.update-resources", props.course.id), {
    onSuccess: () => {},
    onError: (errors) => {},
    preserveScroll: true,
  });
}
</script>

<template>
  <form @submit.prevent="submit" class="space-y-6">
    <div
      v-if="form.resources.length === 0"
      class="text-muted-foreground text-sm"
    >
      No resources added yet. Click "Add Resource" to get started.
    </div>

    <div
      v-for="(resource, index) in form.resources"
      :key="index"
      class="relative flex w-full items-center gap-4"
    >
      <div class="w-full">
        <Label :for="`label-${index}`">Title</Label>
        <Input
          :id="`label-${index}`"
          v-model="resource.label"
          placeholder="Enter the title of your resource"
          class="mt-2"
        />
        <InputError
          class="mt-2"
          :message="form.errors[`resources.${index}.label`]"
        />
      </div>

      <div class="w-full">
        <Label :for="`url-${index}`">Link</Label>
        <Input
          :id="`url-${index}`"
          v-model="resource.url"
          placeholder="Enter the link of your resource"
          class="mt-2"
        />
        <InputError
          class="mt-2"
          :message="form.errors[`resources.${index}.url`]"
        />
      </div>

      <Button
        type="button"
        variant="destructive"
        @click="removeResource(index)"
        class="mt-5"
        aria-label="Remove resource"
      >
        <XIcon />
      </Button>
    </div>

    <Button type="button" @click="addResource" variant="secondary">
      Add Resource
    </Button>

    <div class="flex justify-end pt-5">
      <Button type="submit" :disabled="form.processing">
        <span v-if="form.processing">Updating...</span>
        <span v-else>Update Resources</span>
      </Button>
    </div>
  </form>
</template>

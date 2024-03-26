<script setup lang="ts">
import InputError from "@/Components/InputError.vue";
import { RichEditor } from "@/Components/RichEditor";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { Textarea } from "@/Components/ui/textarea";
import { Course } from "@/types";
import { useForm } from "@inertiajs/vue3";

const props = defineProps<{
    course: Course;
}>();

const form = useForm({
    title: props.course.title ?? "",
    description: props.course.description ?? "",
    is_published: props.course.is_published + "" ?? "false",
});

function submit() {
    form.patch(route("course.update", { course: props.course.id }), {
        onSuccess() {},
        onError(error) {},
    });
}
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium">Course information</h2>

            <!-- <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Once your organisation is setup, you can invite other members to
                join you!
            </p> -->
        </header>

        <form @submit.prevent="submit" class="mt-6">
            <div class="space-y-6">
                <div>
                    <Label for="title">Title</Label>
                    <Input
                        id="title"
                        placeholder="Enter the title of your course"
                        v-model="form.title"
                        class="mt-2"
                    />
                    <InputError class="mt-2" :message="form.errors.title" />
                </div>

                <div>
                    <Label for="type">Status</Label>
                    <Select id="type" v-model:model-value="form.is_published">
                        <SelectTrigger class="mt-2">
                            <SelectValue placeholder="Select status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="true"> Published </SelectItem>
                            <SelectItem value="false"> Draft </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError
                        class="mt-2"
                        :message="form.errors.is_published"
                    />
                </div>

                <div>
                    <Label for="description">Description</Label>
                    <RichEditor
                        v-model="form.description"
                        id="description"
                        placeholder="Enter the description of your course"
                        class="mt-2"
                    />
                    <InputError
                        class="mt-2"
                        :message="form.errors.description"
                    />
                </div>

                <div>
                    <Button
                        type="submit"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Save
                    </Button>
                </div>
            </div>
        </form>
    </section>
</template>

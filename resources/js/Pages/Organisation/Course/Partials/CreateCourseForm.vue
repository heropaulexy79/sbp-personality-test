<script setup lang="ts">
import InputError from "@/Components/InputError.vue";
import RichEditor from "@/Components/RichEditor.vue";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Textarea } from "@/Components/ui/textarea";
import { Organisation } from "@/types";
import { useForm } from "@inertiajs/vue3";

const props = defineProps<{
    organisation_id: Organisation["id"];
}>();

const form = useForm({
    title: "",
    description: "",
});

function createCourse() {
    form.post(route("course.store"), {
        onSuccess() {},
        onError(error) {
            console.log(error);
        },
    });
}
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium">Create course</h2>

            <!-- <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Once your organisation is setup, you can invite other members to
                join you!
            </p> -->
        </header>

        <form @submit.prevent="createCourse">
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
                        Create
                    </Button>
                </div>
            </div>
        </form>
    </section>
</template>

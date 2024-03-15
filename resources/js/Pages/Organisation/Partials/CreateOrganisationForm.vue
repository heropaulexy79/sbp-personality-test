<script setup lang="ts">
import InputError from "@/Components/InputError.vue";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const form = useForm({
    name: "",
});

const createOrganisation = () => {
    form.post(route("organisation.store"), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => {
            // if (form.errors.password) {
            //     form.reset("password", "password_confirmation");
            //     passwordInput.value?.focus();
            // }
        },
    });
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium">Setup organisation</h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Once your organisation is setup, you can invite other members to
                join you!
            </p>
        </header>

        <form @submit.prevent="createOrganisation">
            <div class="space-y-6">
                <div>
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        placeholder="Enter the name of your organisation"
                        v-model="form.name"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <Button type="submit">Create organisation</Button>
                </div>
            </div>
        </form>
    </section>
</template>

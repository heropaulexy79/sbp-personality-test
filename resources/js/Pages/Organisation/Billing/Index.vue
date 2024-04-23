<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
const props = defineProps<{
    payment_methods: any[];
    history: any[];
}>();
console.log(props);
</script>

<template>
    <Head title="Organisation Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Billing
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div
                    class="bg-white p-4 shadow dark:bg-gray-800 sm:rounded-lg sm:p-8"
                ></div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

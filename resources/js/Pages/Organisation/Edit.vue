<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import UpdateOrganisationForm from "@/Pages/Organisation/Partials/UpdateOrganisationForm.vue";
import { Organisation, OrganisationInvite, User } from "@/types";
import { Head } from "@inertiajs/vue3";
import TeamSettings from "./Partials/TeamSettings.vue";

const props = defineProps<{
    organisation: Organisation;
    employees: User[];
    invites: OrganisationInvite[];
}>();
</script>

<template>
    <Head title="Organisation Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            ></h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div
                    class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg"
                >
                    <UpdateOrganisationForm
                        :organisation="props.organisation"
                        class="max-w-xl"
                    />
                </div>

                <div
                    class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg"
                >
                    <!-- class="max-w-xl"  -->
                    <TeamSettings
                        :organisation="organisation"
                        :employees="employees"
                        :invites="invites"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

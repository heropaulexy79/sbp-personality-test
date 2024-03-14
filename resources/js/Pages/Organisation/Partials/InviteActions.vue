<script lang="ts" setup>
import { Button } from "@/Components/ui/button";
import { Organisation, OrganisationInvite } from "@/types";
import { useForm } from "@inertiajs/vue3";
import { X } from "lucide-vue-next";

const props = defineProps<{
    organisation_id: Organisation["id"];
    id: OrganisationInvite["id"];
}>();

const form = useForm({});

function uninviteUser() {
    form.delete(
        route("organisation.uninvite", {
            organisation: props.organisation_id,
            invitation: props.id,
        }),
        {
            preserveScroll: true,
            onError(errors) {
                // TODO: TOAST ERROR
                console.log(errors);
            },
        }
    );
}
</script>

<template>
    <Button type="button" variant="ghost" size="icon" @click="uninviteUser">
        <X :size="16" />
    </Button>
</template>

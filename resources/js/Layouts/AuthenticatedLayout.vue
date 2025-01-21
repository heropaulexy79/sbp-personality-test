<script lang="ts" setup>
import GlobalLayout from "./GlobalLayout.vue";
import OrganisationLayout from "./OrganisationLayout.vue";
</script>

<template>
    <GlobalLayout>
        <OrganisationLayout>
            <slot />
        </OrganisationLayout>
    </GlobalLayout>
</template>

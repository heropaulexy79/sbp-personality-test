<script lang="ts" setup>
import AuthenticatedLayout from "./AuthenticatedLayout.vue";
import GuestLayout from "./GuestLayout.vue";
</script>

<template>
  <AuthenticatedLayout v-if="Boolean($page.props.auth.user)">
    <slot />
  </AuthenticatedLayout>
  <GuestLayout v-else>
    <slot />
  </GuestLayout>
</template>

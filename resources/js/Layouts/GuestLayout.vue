<script setup lang="ts">
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Link } from "@inertiajs/vue3";
import GlobalLayout from "./GlobalLayout.vue";
</script>

<template>
  <GlobalLayout>
    <slot />
  </GlobalLayout>
</template>

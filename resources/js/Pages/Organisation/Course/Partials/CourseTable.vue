<script setup lang="ts">
import BaseDataTable from "@/Components/ui/BaseDataTable.vue";
import { Course } from "@/types";
import { courseColumns } from "./course-column";

defineProps<{
    courses: Course[];
}>();
</script>

<template>
    <section>
        <!--  -->
        <BaseDataTable :columns="courseColumns" :data="courses" />
    </section>
</template>

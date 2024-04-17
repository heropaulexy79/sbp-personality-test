<script lang="ts" setup>
import GlobalLayout from "./GlobalLayout.vue";
import OrganisationLayout from "./OrganisationLayout.vue";
import TeacherLayout from "./TeacherLayout.vue";
</script>

<template>
    <GlobalLayout>
        <TeacherLayout v-if="$page.props.auth.user.account_type === 'TEACHER'">
            <slot />
        </TeacherLayout>
        <OrganisationLayout v-else>
            <slot />
        </OrganisationLayout>
    </GlobalLayout>
</template>

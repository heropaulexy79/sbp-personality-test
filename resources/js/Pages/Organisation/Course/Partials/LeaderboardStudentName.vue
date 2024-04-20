<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";
import { Student } from "./leaderboard-column";

defineProps<{
    student: Student;
}>();
</script>

<template>
    <div class="inline-flex items-center justify-center gap-4">
        <Avatar>
            <AvatarImage
                :src="`https://unavatar.io/${student.user.email}?ttl=1d`"
            />
            <AvatarFallback>{{ student.user.name[0] }}</AvatarFallback>
        </Avatar>
        {{ student.user.name }}
    </div>
</template>

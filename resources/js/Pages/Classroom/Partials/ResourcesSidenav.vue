<script lang="ts" setup>
import { ExternalLink, FileText } from "lucide-vue-next";
import {
  SidebarGroup,
  SidebarGroupContent,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from "@/Components/ui/sidebar";

const props = defineProps<{
  resources: { label: string; url: string }[];
}>();
</script>

<template>
  <SidebarGroup>
    <SidebarGroupLabel class="flex items-center gap-2">
      <FileText class="h-4 w-4" />
      Course Resources
    </SidebarGroupLabel>
    <SidebarGroupContent>
      <SidebarMenu>
        <SidebarMenuItem v-for="(resource, idx) in resources" :key="idx">
          <SidebarMenuButton
            class="group flex w-full items-center gap-2"
            as-child
          >
            <a
              :href="resource.url"
              target="_blank"
              class="group flex w-full items-center gap-2"
            >
              <ExternalLink className="h-4 w-4" />
              <span class="truncate">{{ resource.label }}</span>
            </a>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarGroupContent>
  </SidebarGroup>
</template>

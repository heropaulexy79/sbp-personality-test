<script lang="ts" setup>
import { Link } from "@inertiajs/vue3";
import { Paginated } from "@/types";
import {
  Pagination,
  PaginationEllipsis,
  PaginationFirst,
  PaginationLast,
  PaginationContent,
  PaginationItem,
  PaginationNext,
  PaginationPrevious,
} from "@/Components/ui/pagination";
import { cn } from "@/lib/utils";
import { buttonVariants } from "@/Components/ui/button";
import {
  ChevronLeft,
  ChevronRight,
  ChevronsLeft,
  ChevronsRight,
} from "lucide-vue-next";

defineProps<{
  items: Paginated<any>;
}>();
</script>

<template>
  <Pagination
    v-model:page="items.current_page"
    :items-per-page="items.per_page"
    :total="items.total"
    show-edges
  >
    <PaginationContent class="flex items-center gap-1">
      <PaginationFirst as-child>
        <Link
          preserve-scroll
          :href="items.first_page_url"
          :class="
            cn(
              buttonVariants({
                variant: 'outline',
                class: 'h-10 w-10 p-0',
              }),
            )
          "
        >
          <ChevronsLeft class="h-4 w-4" />
        </Link>
      </PaginationFirst>
      <PaginationPrevious>
        <Link
          preserve-scroll
          :href="items.prev_page_url ?? '#'"
          :class="
            cn(
              buttonVariants({
                variant: 'outline',
                class: 'h-10 w-10 p-0',
              }),
            )
          "
        >
          <ChevronLeft class="h-4 w-4" />
        </Link>
      </PaginationPrevious>

      <template v-for="(item, index) in items.links.slice(1, -1)">
        <PaginationItem :value="Number(item.label)" as-child>
          <Link
            preserve-scroll
            :href="item.url ?? '#'"
            :class="
              cn(
                buttonVariants({
                  variant: item.active ? 'default' : 'outline',
                  class: 'h-10 w-10 p-0',
                }),
              )
            "
          >
            {{ item.label }}
          </Link>
        </PaginationItem>
        <!-- <PaginationEllipsis
                                    v-else
                                    :key="item.type"
                                    :index="index"
                                /> -->
      </template>

      <PaginationNext as-child>
        <Link
          preserve-scroll
          :href="items.next_page_url ?? '#'"
          :class="
            cn(
              buttonVariants({
                variant: 'outline',
                class: 'h-10 w-10 p-0',
              }),
            )
          "
        >
          <ChevronRight class="h-4 w-4" />
        </Link>
      </PaginationNext>
      <PaginationLast as-child>
        <Link
          preserve-scroll
          :href="items.last_page_url"
          :class="
            cn(
              buttonVariants({
                variant: 'outline',
                class: 'h-10 w-10 p-0',
              }),
            )
          "
        >
          <ChevronsRight class="h-4 w-4" />
        </Link>
      </PaginationLast>
    </PaginationContent>
  </Pagination>
</template>

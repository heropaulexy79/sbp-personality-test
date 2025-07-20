<script setup lang="ts">
import { buttonVariants } from "@/Components/ui/button";
import {
  Table,
  TableBody,
  TableCell,
  TableHeader,
  TableRow,
} from "@/Components/ui/table";
import TableHead from "@/Components/ui/table/TableHead.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Link } from "@inertiajs/vue3";
import { Head } from "@inertiajs/vue3";
import { defineProps } from "vue";

// Define the props that will be passed from the Laravel controller
const props = defineProps<{
  leads: Array<{
    id: number;
    name: string | null;
    email: string;
    context: string | null;
    created_at: string;
    updated_at: string;
  }>;
}>();
</script>

<template>
  <Head title="Captured Leads" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl leading-tight font-semibold text-gray-800">
        Captured Leads
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <div class="mb-4 flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900">
                All Captured Leads
              </h3>
              <a
                :href="route('leads.download.all')"
                :class="buttonVariants({ variant: 'secondary' })"
                target="_blank"
              >
                Download as CSV
              </a>
            </div>

            <div v-if="leads.length > 0" class="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead scope="col"> ID </TableHead>
                    <TableHead scope="col"> Name </TableHead>
                    <TableHead scope="col"> Email </TableHead>
                    <TableHead scope="col"> Context </TableHead>
                    <TableHead scope="col"> Captured At </TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="lead in leads" :key="lead.id">
                    <TableCell
                      class="px-6 py-4 text-sm whitespace-nowrap text-gray-900"
                    >
                      {{ lead.id }}
                    </TableCell>
                    <TableCell
                      class="px-6 py-4 text-sm whitespace-nowrap text-gray-900"
                    >
                      {{ lead.name || "N/A" }}
                    </TableCell>
                    <TableCell
                      class="px-6 py-4 text-sm whitespace-nowrap text-gray-900"
                    >
                      {{ lead.email }}
                    </TableCell>
                    <TableCell
                      class="px-6 py-4 text-sm whitespace-nowrap text-gray-900"
                    >
                      {{ lead.context || "N/A" }}
                    </TableCell>
                    <TableCell
                      class="px-6 py-4 text-sm whitespace-nowrap text-gray-900"
                    >
                      {{ new Date(lead.created_at).toLocaleString() }}
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
            <div v-else class="text-gray-600">No leads captured yet.</div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script lang="ts" setup>
import { ref } from "vue";
import { Label } from "@/Components/ui/label";
import { Input } from "@/Components/ui/input";
import { Button } from "@/Components/ui/button";
import InputError from "@/Components/InputError.vue";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/Components/ui/dialog";
import { X, Plus } from "lucide-vue-next";
import { PersonalityTrait } from "./types"; // Ensure correct path to types
import { toast } from "vue-sonner";
import PersonalityTraitCreator from "./PersonalityTraitCreator.vue";

const props = defineProps<{
  traits: PersonalityTrait[];
  errors: { [key: string]: string } | undefined;
  addTrait: (name: string, description?: string) => void;
  deleteTrait: (index: number) => void;
}>();

const addModal = ref(false);

const handleCreateTrait = (name: string, description: string) => {
  if (name.trim()) {
    const isDuplicate = props.traits.some(
      (t) => t.name.toLowerCase() === name.trim().toLowerCase(),
    );

    if (!isDuplicate) {
      props.addTrait(name.trim(), description.trim());
      addModal.value = false;
    } else {
      toast.warning("Trait with this name already exists.");
    }
  }
};
</script>

<template>
  <div class="">
    <div class="mb-4 flex items-center justify-between gap-4">
      <Label class="font-semibold">Personality Archetype</Label>

      <Dialog v-model:open="addModal">
        <DialogTrigger as-child>
          <Button variant="outline"> Add Archetype </Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-[625px]">
          <DialogHeader>
            <DialogTitle>Add Archetype</DialogTitle>
            <DialogDescription>
              Define the personality types that users can be matched with
            </DialogDescription>
          </DialogHeader>
          <div class="py-4">
            <PersonalityTraitCreator
              :errors="errors"
              @create-trait="handleCreateTrait"
            />
          </div>
        </DialogContent>
      </Dialog>
    </div>

    <div v-if="traits.length">
      <ul class="space-y-2">
        <li
          v-for="(trait, index) in traits"
          :key="trait.id"
          class="flex items-center justify-between gap-6 rounded-md border p-3"
        >
          <!-- class="flex items-center justify-between rounded-md bg-gray-100 px-3 py-2 text-sm" -->
          <div>
            <h4 class="font-medium">{{ trait.name }}</h4>
            <p v-if="trait.description" class="text-muted-foreground text-xs">
              {{ trait.description }}
            </p>
          </div>
          <Button
            type="button"
            variant="ghost"
            size="icon"
            class="size-7"
            @click="deleteTrait(index)"
          >
            <X :size="14" />
          </Button>
        </li>
      </ul>
    </div>
    <p v-else class="mt-4 text-sm text-gray-500">
      No traits defined yet. Add some above!
    </p>
  </div>
</template>

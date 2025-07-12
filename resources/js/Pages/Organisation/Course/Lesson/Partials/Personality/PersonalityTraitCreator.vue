<script lang="ts" setup>
import { ref } from "vue";
import { Label } from "@/Components/ui/label";
import { Input } from "@/Components/ui/input";
import { Button } from "@/Components/ui/button";
import InputError from "@/Components/InputError.vue";
import { Plus } from "lucide-vue-next";
import { Textarea } from "@/Components/ui/textarea";

const props = defineProps<{
  errors: { [key: string]: string } | undefined;
}>();

const emit = defineEmits<{
  (e: "create-trait", name: string, description: string): void;
}>();

const newTraitName = ref("");
const newTraitDescription = ref("");

const handleAddTrait = () => {
  if (newTraitName.value.trim()) {
    emit(
      "create-trait",
      newTraitName.value.trim(),
      newTraitDescription.value.trim(),
    );
    newTraitName.value = ""; // Clear input after emitting
    newTraitDescription.value = ""; // Clear description input
  }
};
</script>

<template>
  <div class="space-y-2">
    <div>
      <Label for="new-trait-name">Archetype</Label>
      <Input
        type="text"
        id="new-trait-name"
        v-model="newTraitName"
        placeholder="e.g., The Explorer"
        class="mt-1"
        @keyup.enter="handleAddTrait"
      />
    </div>
    <div>
      <Label for="new-trait-description">Trait Description (Optional):</Label>
      <Textarea
        type="text"
        id="new-trait-description"
        v-model="newTraitDescription"
        placeholder="Brief description of this personality archetype..."
        class="mt-1"
        @keyup.enter="handleAddTrait"
      />
    </div>
    <Button type="button" @click="handleAddTrait" class="mt-2 w-full">
      <Plus :size="16" class="mr-2" /> Add Trait
    </Button>
    <InputError class="mt-2" :message="errors?.['newTraitName']" />
  </div>
</template>

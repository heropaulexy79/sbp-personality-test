<script setup lang="ts">
import { Button } from "@/Components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/Components/ui/dialog";
import { Label } from "@/Components/ui/label";
import { RadioGroup, RadioGroupItem } from "@/Components/ui/radio-group";
import { Course, Lesson, PersonalityQuiz } from "@/types";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { toast } from "vue-sonner";
import { WithUserLesson } from "../types";
import { usePersonalityQuizAnswerManager } from "./use-personality-quiz-answer-manager";
import { cn } from "@/lib/utils";
import { ArrowLeft, ArrowRight } from "lucide-vue-next";
import { Progress } from "@/Components/ui/progress";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { PersonalityTrait } from "@/Pages/Organisation/Course/Lesson/Partials/Personality/types";

const props = defineProps<{
  finalPersonalityResults: { [traitId: string]: number } | null;
  personalityQuizTraits: PersonalityTrait[];
}>();

const getTraitName = (traitId: string) => {
  return (
    props.personalityQuizTraits.find((t) => t.id === traitId)?.name || traitId
  );
};

const getTraitDescription = (traitId: string) => {
  return (
    props.personalityQuizTraits.find((t) => t.id === traitId)?.description || ""
  );
};

const topTraitResult = computed(() => {
  if (
    !props.finalPersonalityResults ||
    Object.keys(props.finalPersonalityResults).length === 0
  ) {
    return null;
  }

  let topTraitId: string | null = null;
  let maxScore = -1;

  for (const traitId in props.finalPersonalityResults) {
    const score = props.finalPersonalityResults[traitId];
    if (score > maxScore) {
      maxScore = score;
      topTraitId = traitId;
    }
  }

  if (topTraitId) {
    const traitDetails = props.personalityQuizTraits.find(
      (t) => t.id === topTraitId,
    );
    return {
      traitId: topTraitId,
      name: traitDetails?.name || topTraitId,
      description: traitDetails?.description || "",
      score: maxScore,
    };
  }
  return null;
});
</script>

<template>
  <Card v-if="Boolean(topTraitResult)" class="border-none shadow-none">
    <CardHeader class="text-center">
      <CardTitle class="text-3xl">{{ topTraitResult?.name }}</CardTitle>
    </CardHeader>
    <CardContent class="text-center">
      <!-- <Progress :model-value="topTraitResult?.score || 0" class="mb-2 h-4" />
      <span class="text-xl font-bold">{{ topTraitResult?.score || 0 }} %</span> -->
      <p class="text-muted-foreground mt-1 text-sm">
        {{ topTraitResult?.description }}
      </p>
    </CardContent>
  </Card>
  <p v-else class="text-muted-foreground text-sm">No results available yet.</p>
</template>

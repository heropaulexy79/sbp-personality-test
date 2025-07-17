<script setup lang="ts">
import { Button, buttonVariants } from "@/Components/ui/button";
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
import {
  ArrowLeft,
  ArrowRight,
  BookOpen,
  Check,
  Copy,
  ExternalLink,
  Facebook,
  Gift,
  Linkedin,
  Share2,
  Twitter,
} from "lucide-vue-next";
import { Progress } from "@/Components/ui/progress";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/Components/ui/card";
import { PersonalityTrait } from "@/Pages/Organisation/Course/Lesson/Partials/Personality/types";
import { Separator } from "@/Components/ui/separator";

const props = defineProps<{
  finalPersonalityResults: { [traitId: string]: number } | null;
  personalityQuizTraits: PersonalityTrait[];
  resources: { label: string; url: string }[];
  course: Course;
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

const copiedShare = ref(false);

const shareMessage = computed(() => {
  return `I'm ${topTraitResult.value?.name.replace(/\s+/g, "")}${topTraitResult.value?.description ? ", " + topTraitResult.value?.description : ""}. Ready to discover your personality? Take the quiz now!`;
});

const handleShare = (platform: string) => {
  const message = `${shareMessage.value} #PersonalityQuiz #${topTraitResult.value?.name!.replace(/\s+/g, "")}`;
  const url = window.location.href;

  let shareUrl = "";

  switch (platform) {
    case "twitter":
      shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(message)}&url=${encodeURIComponent(url)}`;
      break;
    case "facebook":
      shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}&quote=${encodeURIComponent(message)}`;
      break;
    case "linkedin":
      shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}&summary=${encodeURIComponent(message)}`;
      break;
  }

  if (shareUrl) {
    window.open(shareUrl, "_blank", "width=600,height=400");
  }
};

const copyToClipboard = async () => {
  const message = `${shareMessage.value} Check it out: ${window.location.href}`;
  try {
    await navigator.clipboard.writeText(message);
    copiedShare.value = true;
    setTimeout(() => {}, 2000);
  } catch (err) {
    console.error("Failed to copy:", err);
  }
};
</script>

<template>
  <Card v-if="Boolean(topTraitResult)" class="w-full">
    <CardHeader class="mb-0 text-center">
      <CardTitle class="text-3xl">You're {{ topTraitResult?.name }}</CardTitle>
      <CardDescription class="text-muted-foreground mt-1 text-sm">
        {{ topTraitResult?.description }}
      </CardDescription>
    </CardHeader>
    <CardContent class="space-y-10">
      <Card class="">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Share2 class="h-5 w-5" />
            Share Your Results
          </CardTitle>
          <CardDescription class="">
            Let your friends discover their personality archetype too!
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div class="rounded-lg border p-4">
              <p class="mb-3 text-sm font-medium">Share message:</p>
              <p class="font-medium">{{ shareMessage }}</p>
            </div>

            <div class="flex flex-wrap gap-3">
              <Button
                @click="() => handleShare('twitter')"
                class="bg-black transition-all hover:scale-105 hover:bg-black/80"
              >
                <img
                  height="16"
                  width="16"
                  src="https://cdn.simpleicons.org/x/fff"
                />
                Twitter
              </Button>
              <Button
                @click="() => handleShare('facebook')"
                class="bg-[#0866FF] transition-all hover:scale-105 hover:bg-blue-700"
              >
                <img
                  height="16"
                  width="16"
                  src="https://cdn.simpleicons.org/facebook/fff"
                />
                Facebook
              </Button>
              <Button
                @click="() => handleShare('linkedin')"
                class="bg-blue-700 transition-all hover:scale-105 hover:bg-blue-800"
              >
                <Linkedin class="h-4 w-4" />
                LinkedIn
              </Button>
              <Button
                @click="copyToClipboard"
                variant="outline"
                class="border-primary bg-transparent transition-all hover:scale-105 hover:bg-blue-50"
              >
                <Check v-if="copiedShare" class="h-4 w-4 text-green-600" />
                <Copy v-else class="h-4 w-4" />

                <span v-if="copiedShare"> Copied! </span>
                <span v-else> Copy Link </span>
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Resources -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <BookOpen class="h-5 w-5" />
            Recommended Resources for {{ topTraitResult?.name || "you" }}
          </CardTitle>
          <CardDescription>
            Curated content to help you better
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div
              v-for="resource in resources"
              :key="resource.label"
              class="rounded-lg border bg-white p-4 transition-all duration-300 hover:scale-105 hover:shadow-md"
            >
              <div class="flex items-start gap-3">
                <div class="flex-1">
                  <h4 class="mb-1 font-medium">{{ resource.label }}</h4>
                  <a
                    href="{resource.url}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="w-full"
                    :class="buttonVariants({ variant: 'outline', size: 'sm' })"
                  >
                    <ExternalLink class="mr-1 h-3 w-3" />
                    View
                  </a>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Course -->
      <Card class="">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Gift class="h-5 w-5" />
            {{ course.title }}
          </CardTitle>
          <CardDescription class="prose" v-html="course.description">
          </CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          <!-- <p class="text-gray-700">{data.individualCourse.description}</p> -->

          <!-- <div class="space-y-2">
            <h5 class="flex items-center gap-2 font-medium text-gray-900">
              <Sparkles class="h-4 w-4" />
              What you'll gain:
            </h5>
            <ul class="space-y-1">
              {data.individualCourse.benefits.map((benefit, index) => (
              <li
                key="{index}"
                class="flex items-center gap-2 text-sm text-gray-600"
              >
                <Star class="h-3 w-3 text-yellow-500" />
                {benefit}
              </li>
              ))}
            </ul>
          </div> -->

          <Separator />

          <div class="flex items-center justify-end gap-2">
            <!-- <div>
              <span class="text-2xl font-bold"
                >{data.individualCourse.price}</span
              >
              <span class="ml-1 text-sm text-gray-500">one-time</span>
            </div> -->

            <a
              :href="route('classroom.lesson.index', { course: course.slug })"
              target="_blank"
              rel="noopener noreferrer"
              class="transition-all hover:scale-105"
              :class="buttonVariants()"
            >
              Take course
            </a>
          </div>
        </CardContent>
      </Card>
    </CardContent>
  </Card>
  <p v-else class="text-muted-foreground text-sm">No results available yet.</p>
</template>

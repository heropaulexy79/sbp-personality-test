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
  FileText,
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
import { Badge } from "@/Components/ui/badge";
import { extractParenthesizedText, removeParenthesizedText } from "../utils";

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
  return `I'm the ${topTraitResult.value?.name.replace(/\s+/g, "")}. Ready to discover your personality? Take the quiz now!`;
});

const extraTags = computed(() => {
  return extractParenthesizedText(topTraitResult.value?.name ?? "");
});

const handleShare = (platform: string) => {
  const message = `${shareMessage.value} #PersonalityQuiz #${topTraitResult.value?.name!.replace(/\s+/g, "")}`;
  const url = window.location.href;

  let shareUrl = "";

  switch (platform) {
    case "twitter":
      shareUrl = `https://x.com/intent/tweet?text=${encodeURIComponent(message)}&url=${encodeURIComponent(url)}`;
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
  <div class="py-12">
    <div>
      <div class="relative container">
        <div class="grid gap-10 md:grid-cols-[1fr_350px]">
          <div class="space-y-10 self-end">
            <div>
              <Badge variant="secondary"> Quiz Results </Badge>
              <h2 class="mt-2 mb-4 text-4xl font-bold lg:text-5xl">
                {{ removeParenthesizedText(topTraitResult?.name || "") }}
              </h2>

              <div class="flex items-center gap-2">
                <Badge v-for="ta in extraTags"> {{ ta }} </Badge>
              </div>

              <div class="text-muted-foreground mt-2 text-lg lg:text-xl">
                {{ topTraitResult?.description }}
              </div>

              <!-- <div
              v-html="course.description"
              class="prose prose-lg dark:prose-invert max-w-none"
            /> -->
            </div>

            <div>
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
                <Separator />
                <CardContent>
                  <div class="space-y-5">
                    <div class="rounded-lg">
                      <p class="mb-3 text-sm font-medium">Share message:</p>
                      <p class="font-medium">{{ shareMessage }}</p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                      <Button
                        @click="() => handleShare('twitter')"
                        class="bg-black text-white transition-all hover:scale-105 hover:bg-black/80"
                      >
                        <img
                          height="16"
                          width="16"
                          src="https://cdn.simpleicons.org/x/fff"
                        />
                        X (Twitter)
                      </Button>
                      <Button
                        @click="() => handleShare('facebook')"
                        class="bg-[#0866FF] text-white transition-all hover:scale-105 hover:bg-blue-700"
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
                        class="bg-blue-700 text-white transition-all hover:scale-105 hover:bg-blue-800"
                      >
                        <Linkedin class="h-4 w-4" />
                        LinkedIn
                      </Button>
                      <Button
                        @click="copyToClipboard"
                        variant="outline"
                        class="bg-transparent transition-all hover:scale-105"
                      >
                        <Check
                          v-if="copiedShare"
                          class="h-4 w-4 text-green-600"
                        />
                        <Copy v-else class="h-4 w-4" />

                        <span v-if="copiedShare"> Copied! </span>
                        <span v-else> Copy Link </span>
                      </Button>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>
          </div>

          <div class="sticky top-16 space-y-6 self-start">
            <!--  Resources -->

            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <FileText class="h-5 w-5" />
                  Resources for you!
                </CardTitle>
                <!-- <CardDescription>
                  Curated content to help you better
                </CardDescription> -->
              </CardHeader>
              <Separator />
              <CardContent>
                <div class="space-y-4">
                  <div v-for="(resource, idx) in resources" :key="idx">
                    <a
                      :href="resource.url"
                      target="_blank"
                      class="group flex w-full items-center gap-2"
                    >
                      <ExternalLink :size="16" className="h-4 w-4" />
                      <span class="truncate">{{ resource.label }}</span>
                    </a>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Take course -->

            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <BookOpen class="h-5 w-5" />
                  Learn More
                </CardTitle>
              </CardHeader>
              <Separator />
              <CardContent>
                <CardDescription>
                  Wanna learn more about the archetypes, the team and what we
                  do?
                </CardDescription>
                <div class="mt-4 space-y-4">
                  <a
                    href="/"
                    target="_blank"
                    class="group flex w-full items-center gap-2"
                    :class="buttonVariants({ variant: 'outline' })"
                  >
                    Get started
                  </a>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

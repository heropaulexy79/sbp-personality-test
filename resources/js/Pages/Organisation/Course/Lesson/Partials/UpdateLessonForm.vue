<script setup lang="ts">
import InputError from "@/Components/InputError.vue";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/Components/ui/select";
import { Textarea } from "@/Components/ui/textarea";
import { Question, Lesson, PersonalityQuiz } from "@/types";
import { useForm } from "@inertiajs/vue3";
import QuizBuilder from "./QuizBuilder.vue";
import { generateId } from "./utils";
import { RichEditor } from "@/Components/RichEditor";
import { slugify } from "@/lib/utils";
import { WandSparklesIcon } from "lucide-vue-next";
import PersonallityQuizBuilder from "./Personality/PersonallityQuizBuilder.vue";

const props = defineProps<{ lesson: Lesson }>();

// TODO: PARSE WITH ZOD before hand
const quizData = props.lesson.content_json as Question[];
const personalityQuizData = props.lesson.content_json as PersonalityQuiz;

const form = useForm({
  title: props.lesson.title,
  slug: props.lesson.slug,
  content: props.lesson.content,
  quiz:
    quizData && typeof quizData !== "string"
      ? quizData
      : [
          {
            id: generateId(),
            text: "",
            type: "single_choice",
            options: [
              { id: generateId(), text: "" },
              { id: generateId(), text: "" },
            ],
          },
        ],
  personality_quiz:
    personalityQuizData && typeof personalityQuizData !== "string"
      ? (personalityQuizData as any)
      : {
          traits: [],
          questions: [],
        },
  type: props.lesson.type ?? "DEFAULT",
  is_published: props.lesson.is_published
    ? props.lesson.is_published + ""
    : "false",
});

function submit() {
  form.patch(
    route("lesson.update", {
      course: props.lesson.course_id,
      lesson: props.lesson.id,
    }),
    {
      onSuccess() {},
      onError(error) {},
      preserveScroll: true,
    },
  );
}

function updateType(value: string) {
  value = value.toUpperCase();
  // form.content = "";

  if (value === "DEFAULT") {
    form.quiz = [];
  } else {
    form.content = "";
  }
}

function generateSlug() {
  form.slug = slugify(form.title);
}
</script>

<template>
  <form @submit.prevent="submit">
    <div
      class="relative grid gap-6 md:grid-cols-[1fr_200px] md:gap-10 lg:grid-cols-[1fr_250px]"
    >
      <!-- Left -->
      <div class="bg-background grid gap-6 rounded-md px-4 py-4 md:grid-cols-2">
        <div>
          <Label for="title">Title</Label>
          <Input
            id="title"
            placeholder="Enter the title of this lesson"
            v-model="form.title"
            class="mt-2"
          />
          <InputError class="mt-2" :message="form.errors.title" />
        </div>

        <div>
          <Label for="type">Type</Label>
          <Select
            id="type"
            v-model:model-value="form.type"
            @update:model-value="
              (s) => {
                updateType(s as string);
              }
            "
            disabled
          >
            <SelectTrigger class="mt-2 w-full">
              <SelectValue placeholder="Select lesson type" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="DEFAULT"> Default </SelectItem>
              <SelectItem value="QUIZ"> Quiz </SelectItem>
              <SelectItem value="PERSONALITY_QUIZ">
                Personality Quiz
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="md:col-span-2">
          <div class="mt-2">
            <div v-if="form.type === 'DEFAULT'">
              <Label for="content">Content</Label>
              <RichEditor id="content" v-model="form.content" class="mt-2" />
            </div>

            <div v-if="form.type === 'QUIZ'" class="">
              <QuizBuilder
                :errors="form.errors"
                v-model:model-value="form.quiz as Question[]"
              />
            </div>
            <div v-if="form.type === 'PERSONALITY_QUIZ'" class="">
              <PersonallityQuizBuilder
                :errors="form.errors"
                v-model:model-value="
                  form.personality_quiz
                    .questions as PersonalityQuiz['questions']
                "
                v-model:traits="
                  form.personality_quiz.traits as PersonalityQuiz['traits']
                "
              />
            </div>
          </div>
          <InputError class="mt-2" :message="form.errors.content" />
        </div>
      </div>

      <!-- Right -->
      <aside class="sticky top-4 space-y-6 self-start rounded-md px-4">
        <Button
          type="submit"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
        >
          Save
        </Button>

        <div>
          <Label for="slug">Slug</Label>
          <div class="mt-2 flex items-center justify-center">
            <Input id="slug" v-model="form.slug" />
            <Button
              type="button"
              variant="outline"
              size="icon"
              class="shrink-0"
              :disabled="!form.title"
              @click="generateSlug"
            >
              <WandSparklesIcon class="size-4" />
            </Button>
          </div>
          <InputError class="mt-2" :message="form.errors.slug" />
        </div>

        <div>
          <Label for="type">Status</Label>
          <Select id="type" v-model:model-value="form.is_published">
            <SelectTrigger class="mt-2 w-full">
              <SelectValue placeholder="Select status" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="true"> Published </SelectItem>
              <SelectItem value="false"> Draft </SelectItem>
            </SelectContent>
          </Select>
          <InputError class="mt-2" :message="form.errors.is_published" />
        </div>
      </aside>
    </div>
  </form>
</template>

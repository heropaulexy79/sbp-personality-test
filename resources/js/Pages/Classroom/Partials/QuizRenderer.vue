<script lang="ts" setup>
import { Button } from "@/Components/ui/button";
import { Course, Lesson } from "@/types";
import { useForm } from "@inertiajs/vue3";
import { WithCompleted } from "./types";
import { useQuizAnswerManager } from "./use-quiz-answer-manager";
import { RadioGroup, RadioGroupItem } from "@/Components/ui/radio-group";
import { Label } from "@/Components/ui/label";

const props = defineProps<{
    course: Course;
    lesson: WithCompleted<Lesson>;
}>();

const {
    answerQuestion,
    nextQuestion,
    previousQuestion,
    currentQuesion,
    currentQuestionIdx,
    currentAnswer,
    hasNextQuesion,
    hasPreviousQuesion,
    answers,
} = useQuizAnswerManager(
    `${props.course.id}:${props.lesson.id}`,
    props.lesson.content_json,
);

const completionForm = useForm({
    answers: [],
});

function submit() {
    completionForm
        .transform((d) => {
            const ans = Array.from(answers.value.values());
            return {
                // When console.log ing it shows as a proxy
                // answers: ans.map(({ ...rest }) => ({
                //     ...rest,
                // })),
                answers: ans,
            };
        })
        .patch(
            route("classroom.lesson.answerQuiz", {
                course: props.course.id,
                lesson: props.lesson.id,
            }),
            {
                onSuccess() {},
                onError() {},
            },
        );
}
</script>

<template>
    <div
        class="relative mx-auto max-w-screen-md rounded-md border border-border p-6"
    >
        <div
            class="radial-progress absolute right-4 top-4 size-11 rounded-full text-xs"
            :style="{
                '--progress':
                    (currentQuestionIdx + 1 / lesson.content_json.length) * 100,
            }"
        >
            <span
                class="flex size-9 items-center justify-center rounded-full bg-white"
            >
                {{ currentQuestionIdx + 1 }} / {{ lesson.content_json.length }}
            </span>
        </div>

        <div class="mb-6 max-w-screen-sm text-lg font-bold">
            {{ currentQuesion.text }}
        </div>

        <div class="">
            <RadioGroup
                :key="currentQuesion.id"
                :default-value="currentAnswer as string | undefined"
                @update:model-value="
                    (v) => {
                        answerQuestion(currentQuesion.id, v);
                    }
                "
                v-if="currentQuesion.type === 'single_choice'"
            >
                <div
                    v-for="option in currentQuesion.options"
                    :key="option.id"
                    class="flex items-center space-x-2"
                >
                    <RadioGroupItem :id="option.id" :value="option.id" />
                    <Label :for="option.id"> {{ option.text }} </Label>
                    <!-- <RadioGroupItem
        v-bind="forwardedProps"
        :class="
            cn(
                'aspect-square h-4 w-4 rounded-full border border-primary text-primary ring-offset-background focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
                props.class,
            )
        "
    >
        <RadioGroupIndicator class="flex items-center justify-center">
            <Circle class="h-2.5 w-2.5 fill-current text-current" />
        </RadioGroupIndicator>
    </RadioGroupItem> -->
                </div>
            </RadioGroup>
        </div>

        <div class="mt-6 flex items-center gap-4">
            <Button
                variant="secondary"
                size="sm"
                @click="previousQuestion"
                :disabled="!hasPreviousQuesion"
            >
                Previous
            </Button>
            <Button
                variant="secondary"
                size="sm"
                @click="nextQuestion"
                :disabled="!hasNextQuesion"
                v-if="hasNextQuesion"
            >
                Next
            </Button>

            <Button size="sm" @click="submit" v-if="!hasNextQuesion">
                Submit
            </Button>
        </div>
    </div>
</template>

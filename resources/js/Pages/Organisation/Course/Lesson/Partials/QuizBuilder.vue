<script lang="ts" setup>
import { Label } from "@/Components/ui/label";
import { useQuizManager } from "./use-quiz-manager";
import { Input } from "@/Components/ui/input";
import { Button } from "@/Components/ui/button";
import { watch } from "vue";
import InputError from "@/Components/InputError.vue";
import { RadioGroup, RadioGroupItem } from "@/Components/ui/radio-group";
// import {
//     Select,
//     SelectContent,
//     SelectItem,
//     SelectTrigger,
//     SelectValue,
// } from "@/Components/ui/select";
import { X } from "lucide-vue-next";
import { Question } from "@/types";

defineProps<{ errors: { [key: string]: string } | undefined }>();

const model = defineModel<Question[]>();
const {
    questions,
    addOption,
    addQuestion,
    deleteOption,
    deleteQuestion,
    // updateOption,
    // updateQuestion,
} = useQuizManager(model.value ?? []);

watch(
    () => questions.value,
    (n) => {
        model.value = n;
    },
    { deep: true }
);
</script>

<template>
    <ul class="space-y-6">
        <li
            v-for="(question, index) in questions"
            class="border-border border-b last:border-transparent pb-6"
        >
            <div>
                <div class="flex gap-4 justify-between items-center">
                    <Label :for="'q.' + question.id"
                        >Question {{ index + 1 }}:</Label
                    >

                    <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        class="text-xs size-9"
                        @click="deleteQuestion(index)"
                    >
                        <X :size="16" />
                    </Button>
                </div>

                <div>
                    <Input
                        type="text"
                        :id="'q.' + question.id"
                        v-model="question.text"
                        placeholder="Enter question text"
                        class="mt-2"
                    />
                    <InputError
                        class="mt-2"
                        :message="errors?.[`quiz.${index}.text`]"
                    />
                    <InputError
                        class="mt-2"
                        :message="errors?.[`quiz.${index}.correctOption`]"
                    />
                </div>
            </div>
            <RadioGroup
                v-model="question.correctOption"
                v-if="question.type === 'single_choice'"
            >
                <ul class="grid md:grid-cols-2 gap-4 mt-4">
                    <li v-for="(option, optionIndex) in question.options">
                        <div>
                            <div
                                class="flex gap-4 justify-between items-center"
                            >
                                <Label
                                    :for="
                                        'q.' + question.id + '.o.' + option.id
                                    "
                                    >Option {{ optionIndex + 1 }}:</Label
                                >
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    class="text-xs size-9"
                                    @click="deleteOption(index, optionIndex)"
                                >
                                    <X :size="16" />
                                </Button>
                            </div>
                            <div class="flex items-center gap-2">
                                <RadioGroupItem
                                    :value="option.id"
                                    :id="'rb-' + option.id"
                                    v-if="question.type === 'single_choice'"
                                />

                                <Input
                                    type="text"
                                    :id="'q.' + question.id + '.o.' + option.id"
                                    v-model="option.text"
                                    placeholder="Enter option text"
                                    class="mt-2 flex-1"
                                />
                            </div>
                            <InputError
                                class="mt-2"
                                :message="
                                    errors?.[
                                        `quiz.${index}.options.${optionIndex}.text`
                                    ]
                                "
                            />
                        </div>
                    </li>
                    <li>
                        <div>
                            <div class="h-9"></div>

                            <Button
                                type="button"
                                variant="outline"
                                class="w-full mt-2"
                                @click="addOption(index)"
                                >Add option</Button
                            >
                        </div>
                    </li>
                </ul>
            </RadioGroup>
        </li>
    </ul>

    <Button class="mt-8" type="button" variant="outline" @click="addQuestion">
        Add question
    </Button>
</template>

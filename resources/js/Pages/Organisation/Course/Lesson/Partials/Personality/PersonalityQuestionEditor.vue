<script setup lang="ts">
import { ref } from 'vue'; // <-- Import ref
import { router } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card';
import { Label } from '@/Components/ui/label';
import { toast } from 'vue-sonner';
import { Lesson } from '@/types';
import { DEFAULT_12_ARCHETYPES, defaultArchetypes } from './constants';
import PersonalityTraitManager from './PersonalityTraitManager.vue';
import PersonalityQuestionEditor from './PersonalityQuestionEditor.vue';
import { usePersonalityQuizManager } from './use-personality-quiz-manager';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/Components/ui/dialog';
import { TagsInput, TagsInputInput, TagsInputItem, TagsInputItemDelete, TagsInputItemText } from '@/Components/ui/tags-input';
import { WandSparkles, Loader2 } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3'; // <-- We still need useForm for the AI modal form
import InputError from '@/Components/InputError.vue';
import axios from 'axios'; // <-- Import axios

const props = defineProps<{
    lesson: Lesson;
}>();

// This is the main form for the whole page
const { form, addQuestion, removeQuestion, addTrait, removeTrait, addOption, removeOption } =
    usePersonalityQuizManager(props.lesson.content_json?.personality_quiz);

// This is the small form just for the AI generation modal
const aiForm = useForm({
    archetypes: defaultArchetypes,
});

const isGenerating = ref(false); // <-- Use ref for axios loading state
const showAiModal = ref(false);

function submit() {
    form.patch(route('quizzes.update', props.lesson.id), {
        onSuccess: () => {
            toast.success('Quiz saved successfully');
        },
        onError: () => {
            toast.error('Error saving quiz', {
                description: 'Please check the form for errors and try again.',
            });
        },
    });
}

// This function is now completely changed to use axios
async function generateQuiz() {
    aiForm.clearErrors();
    isGenerating.value = true;

    try {
        const response = await axios.post(route('ai.generate-personality-quiz'), {
            archetypes: aiForm.archetypes
        });

        // IMPORTANT: Update the *main* form with the AI-generated data
        form.personality_quiz.archetypes = response.data.archetypes;
        form.personality_quiz.questions = response.data.questions;

        toast.success('Quiz Generated!', {
            description: 'Archetypes and questions have been populated.'
        });

        // Close the modal
        showAiModal.value = false;

    } catch (error: any) {
        console.error('AI Generation Error:', error);
        let errorMessage = 'An unknown error occurred.';

        if (error.response && error.response.data && error.response.data.error) {
            errorMessage = error.response.data.error;
        } else if (error.message) {
            errorMessage = error.message;
        }

        toast.error('Generation Failed', {
            description: errorMessage
        });
        
        // Set form error on the aiForm
        aiForm.setError('archetypes', 'AI generation failed. Please check your inputs or try again.');

    } finally {
        isGenerating.value = false;
    }
}

function resetAiForm() {
    aiForm.reset();
    aiForm.clearErrors();
}

function resetQuiz() {
    if (confirm('Are you sure you want to clear the entire quiz? This will remove all archetypes and questions.')) {
        form.reset();
        toast.info('Quiz cleared. Remember to save your changes.');
    }
}
</script>

<template>
    <form @submit.prevent="submit">
        <div class="space-y-6">
            <!-- Header Card -->
            <Card>
                <CardHeader>
                    <CardTitle>Quiz Details</CardTitle>
                    <CardDescription>
                        Basic settings for your personality quiz. The title and slug are managed on the main edit page.
                    </CardDescription>
                </CardHeader>
                <CardContent class="flex justify-between items-center">
                    <Dialog v-model:open="showAiModal">
                        <DialogTrigger as-child>
                            <Button type="button" variant="outline" @click="resetAiForm">
                                <WandSparkles class="w-4 h-4 mr-2" />
                                Generate with AI
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-[600px]">
                            <form @submit.prevent="generateQuiz">
                                <DialogHeader>
                                    <DialogTitle>Generate Quiz with AI</DialogTitle>
                                    <DialogDescription>
                                        Enter 12 personality archetypes (e.g., "The Innovator", "The Mentor"). The AI
                                        will generate descriptions and 12 questions based on them.
                                    </DialogDescription>
                                </DialogHeader>
                                <div class="py-6 space-y-4">
                                    <div class="space-y-2">
                                        <Label for="archetypes">12 Nigerian Corporate Archetypes</Label>
                                        <TagsInput id="archetypes" v-model="aiForm.archetypes" :max="12">
                                            <TagsInputItem v-for="(item, index) in aiForm.archetypes" :key="index"
                                                :value="item">
                                                <TagsInputItemText />
                                                <TagsInputItemDelete />
                                            </TagsInputItem>
                                            <TagsInputInput
                                                :placeholder="aiForm.archetypes.length < 12 ? 'Add archetype...' : '12 archetypes added.'"
                                                :disabled="aiForm.archetypes.length >= 12" />
                                        </TagsInput>
                                        <p class="text-sm text-muted-foreground">
                                            You have {{ aiForm.archetypes.length }} of 12 archetypes.
                                        </p>
                                        <InputError :message="aiForm.errors.archetypes" />
                                    </div>
                                    <Button type="button" variant="link" class="p-0 h-auto"
                                        @click="aiForm.archetypes = DEFAULT_12_ARCHETYPES">
                                        Use default archetypes
                                    </Button>
                                </div>
                                <DialogFooter>
                                    <Button type="button" variant="ghost" @click="showAiModal = false">
                                        Cancel
                                    </Button>
                                    <Button type="submit" :disabled="isGenerating || aiForm.archetypes.length < 12">
                                        <Loader2 v-if="isGenerating" class="w-4 h-4 mr-2 animate-spin" />
                                        Generate Quiz
                                    </Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>

                    <Button type="button" variant="destructive-outline" @click="resetQuiz">
                        Clear Quiz
                    </Button>
                </CardContent>
            </Card>

            <!-- Archetypes / Traits Card -->
            <PersonalityTraitManager :form="form" :addTrait="addTrait" :removeTrait="removeTrait" />

            <!-- Questions Card -->
            <PersonalityQuestionEditor :form="form" :addQuestion="addQuestion" :removeQuestion="removeQuestion"
                :addOption="addOption" :removeOption="removeOption" />

            <!-- Save Card -->
            <Card>
                <CardHeader>
                    <CardTitle>Save Changes</CardTitle>
                    <CardDescription>
                        Don't forget to save your progress.
                    </CardDescription>
                </CardHeader>
                <CardFooter class="flex justify-end">
                    <Button type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                        Save Quiz
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </form>
</template>
<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
// FIX: Import Link explicitly for use in the template
import { Head, usePage, Link } from '@inertiajs/vue3'; 
import { computed, ref, onMounted } from 'vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card';
import { Progress } from '@/Components/ui/progress';
import { Button } from '@/Components/ui/button';
import PersonalityQuizRenderer from './Partials/PersonalityQuiz/PersonalityQuizRenderer.vue';
import PersonalityQuizFinalResults from './Partials/PersonalityQuiz/PersonalityQuizFinalResults.vue';
import { Lesson, QuizContent, UserLesson } from '@/types/app-types'; // Assuming this defines the Lesson structure

interface QuizProps {
    lesson: Lesson;
    userLesson?: UserLesson | null; // Optional prop if results already exist
}

const props = defineProps<QuizProps>();

// Convert the JSON content to a reactive object
const quizContent = ref<QuizContent>(props.lesson.content_json as QuizContent || { questions: [], traits: [], archetypes: [] });

const userLesson = ref<UserLesson | null>(props.userLesson || null);

// Reactive state for the quiz
const currentQuestionIndex = ref(0);
const isQuizSubmitted = ref(false); // Flag to track submission state

const totalQuestions = computed(() => quizContent.value.questions.length);
const currentQuestion = computed(() => quizContent.value.questions[currentQuestionIndex.value]);

// === FIX FOR NaN PROGRESS BAR ===
// The console error "Invalid prop `value` of value `NaN`" occurs when totalQuestions is 0.
const currentProgress = computed(() => {
    if (totalQuestions.value === 0) {
        return 0;
    }
    // Calculate progress based on zero-indexed current question
    return Math.min(100, Math.round(((currentQuestionIndex.value + 1) / totalQuestions.value) * 100));
});
// ==================================

// State for answer tracking (managed by the child component)
// We only need the current answer here to enable/disable the "Next" button.
const currentAnswer = ref<number | null>(null); 

const handleAnswerSelected = (questionIndex: number, answerId: number | null) => {
    // The Renderer updates the internal answer state (in the hook), 
    // but we need to track if *this* question has an answer selected
    // to enable the "Next" button.
    currentAnswer.value = answerId;
};

const goToNextQuestion = () => {
    if (currentAnswer.value !== null) {
        if (currentQuestionIndex.value < totalQuestions.value - 1) {
            currentQuestionIndex.value++;
            currentAnswer.value = null; // Reset answer state for the next question
        } else if (currentQuestionIndex.value === totalQuestions.value - 1) {
            // Last question answered, trigger submission/results in the child
            isQuizSubmitted.value = true;
        }
    }
};

// Handle successful final submission from the PersonalityQuizRenderer
const handleSubmissionComplete = (result: UserLesson) => {
    userLesson.value = result;
    isQuizSubmitted.value = true; // Ensure results are displayed
    // Optional: Redirect or show confirmation, but here we just update state
};

// If the quiz has already been taken, immediately show results
onMounted(() => {
    if (props.userLesson && props.userLesson.result_json) {
        isQuizSubmitted.value = true;
    }
});

// Helper for dynamic labels
const nextButtonLabel = computed(() => 
    currentQuestionIndex.value === totalQuestions.value - 1 
        ? (isQuizSubmitted.value ? 'View Results' : 'Submit Quiz')
        : 'Next Question'
);

</script>

<template>
    <PublicLayout>
        <Head :title="lesson.title" />
        
        <div class="py-12 bg-gray-50 dark:bg-gray-900 min-h-screen">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                
                <Card class="shadow-xl">
                    <CardHeader class="border-b">
                        <CardTitle class="text-3xl font-extrabold text-primary-600 dark:text-primary-400">
                            {{ lesson.title }}
                        </CardTitle>
                        <CardDescription>
                            {{ isQuizSubmitted && userLesson ? 'Your Archetype Results' : 'Find your corporate personality archetype.' }}
                        </CardDescription>
                    </CardHeader>
                    
                    <CardContent class="p-6 space-y-8">
                        
                        <!-- Progress Bar & Navigation Status (Only show if not submitted) -->
                        <div v-if="!isQuizSubmitted || !userLesson">
                            <div class="mb-4">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Question {{ currentQuestionIndex + 1 }} of {{ totalQuestions }}
                                </span>
                            </div>
                            
                            <!-- FIX: Use the fixed Progress component -->
                            <Progress :model-value="currentProgress" class="h-2 bg-gray-200 dark:bg-gray-700" />
                        </div>
                        
                        <!-- Quiz Content Renderer -->
                        <div v-if="!isQuizSubmitted || !userLesson">
                            <!-- Renders questions/options and handles local state -->
                            <PersonalityQuizRenderer
                                :lesson="lesson"
                                :initial-quiz-content="quizContent"
                                :current-index="currentQuestionIndex"
                                :current-question="currentQuestion"
                                @answer-selected="handleAnswerSelected"
                                @submission-complete="handleSubmissionComplete"
                            />
                        </div>

                        <!-- Results View -->
                        <div v-else>
                            <PersonalityQuizFinalResults 
                                :lesson="lesson" 
                                :user-lesson="userLesson" 
                            />
                        </div>
                        
                        <!-- Navigation Buttons (Only show if quiz not fully completed/submitted) -->
                        <div v-if="!isQuizSubmitted || !userLesson" class="flex justify-between pt-4 border-t">
                            <Button 
                                variant="outline"
                                :disabled="currentQuestionIndex === 0"
                                @click="currentQuestionIndex--"
                            >
                                Previous
                            </Button>
                            
                            <Button
                                :disabled="currentAnswer === null"
                                @click="goToNextQuestion"
                            >
                                {{ nextButtonLabel }}
                            </Button>
                        </div>
                        
                        <!-- Navigation after results -->
                        <div v-if="isQuizSubmitted && userLesson" class="flex justify-end pt-4 border-t">
                            <!-- FIX: Link component is now imported -->
                            <Link :href="route('dashboard')">
                                <Button variant="secondary">
                                    Go to Dashboard
                                </Button>
                            </Link>
                        </div>
                        
                    </CardContent>
                </Card>
            </div>
        </div>
    </PublicLayout>
</template>
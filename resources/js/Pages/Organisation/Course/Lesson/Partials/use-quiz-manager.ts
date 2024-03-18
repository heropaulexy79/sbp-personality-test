import { ref, watch } from "vue";
import { generateId } from "./utils";
import { Question } from "@/types";

export function useQuizManager(initialQuestions?: Question[]) {
    const questions = ref<Array<Question>>(initialQuestions ?? []);

    const addQuestion = () => {
        questions.value.push({
            id: generateId(),
            text: "",
            type: "single_choice",
            options: [
                { id: generateId(), text: "" },
                { id: generateId(), text: "" },
            ],
        });
    };

    // const updateQuestion = (index: number, updatedQuestion: Question) => {
    //     questions.value[index] = updatedQuestion;
    // };

    const deleteQuestion = (index: number) => {
        questions.value.splice(index, 1);
    };

    const addOption = (questionIndex: number) => {
        questions.value[questionIndex].options.push({
            id: generateId(),
            text: "",
            // isCorrect: false,
        });
    };

    // const updateOption = (
    //     questionIndex: number,
    //     optionIndex: number,
    //     updatedOption: QuestionOption
    // ) => {
    //     questions.value[questionIndex].options[optionIndex] = updatedOption;
    // };

    const deleteOption = (questionIndex: number, optionIndex: number) => {
        questions.value[questionIndex].options.splice(optionIndex, 1);
    };

    // const setCorrectAnswer = (questionIndex: number, optionIndex: number) => {

    // };

    // Helper to get a specific question's correct answer index
    // const getCorrectAnswer = (question: Question) => {
    //     return question.correctOption;
    // };

    // // Watch for changes in 'questions' and update the correct answer index
    // watch(questions, () => {
    //     questions.value.forEach((question) => {
    //         const correctIndex = getCorrectAnswerIndex(question);
    //         if (correctIndex !== -1) {
    //             question.options[correctIndex].isCorrect = true;
    //         }
    //     });
    // });

    return {
        questions,
        addQuestion,
        // updateQuestion,
        deleteQuestion,
        addOption,
        // updateOption,
        deleteOption,
        // setCorrectAnswer,
    };
}

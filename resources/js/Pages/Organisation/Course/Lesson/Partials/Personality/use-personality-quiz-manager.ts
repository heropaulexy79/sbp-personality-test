import { ref, watch } from "vue";
import {
  PersonalityQuestion,
  PersonalityAnswerOption,
  PersonalityLikertQuestion,
  PersonalityMultipleChoiceQuestion,
  PersonalityTrait,
} from "./types";
import { nanoid } from "nanoid";
import { slugify } from "@/lib/utils";

interface UsePersonalityQuizManagerOptions {
  initialQuestions?: PersonalityQuestion[];
  initialTraits?: PersonalityTrait[];
}

export function usePersonalityQuizManager(
  options: UsePersonalityQuizManagerOptions = {},
) {
  const questions = ref<PersonalityQuestion[]>(options.initialQuestions || []);
  const traits = ref<PersonalityTrait[]>(options.initialTraits || []);

  const defaultLikertOptions: PersonalityAnswerOption[] = [
    { id: nanoid(), text: "Strongly Disagree", scores: {} },
    { id: nanoid(), text: "Disagree", scores: {} },
    { id: nanoid(), text: "Neutral", scores: {} },
    { id: nanoid(), text: "Agree", scores: {} },
    { id: nanoid(), text: "Strongly Agree", scores: {} },
  ];

  const addQuestion = (type: "likert_scale" | "multiple_choice") => {
    if (type === "likert_scale") {
      const newQuestion: PersonalityLikertQuestion = {
        id: nanoid(),
        text: "",
        type: "likert_scale",
        options: [...defaultLikertOptions],
      };
      questions.value.push(newQuestion);
    } else if (type === "multiple_choice") {
      const newQuestion: PersonalityMultipleChoiceQuestion = {
        id: nanoid(),
        text: "",
        type: "multiple_choice",
        options: [{ id: nanoid(), text: "", scores: {} }],
      };
      questions.value.push(newQuestion);
    }
  };

  const deleteQuestion = (index: number) => {
    questions.value.splice(index, 1);
  };

  const addOption = (questionIndex: number) => {
    const question = questions.value[questionIndex];
    if (question) {
      question.options.push({
        id: nanoid(),
        text: "",
        scores: {},
      });
    }
  };

  const deleteOption = (questionIndex: number, optionIndex: number) => {
    const question = questions.value[questionIndex];
    if (question && question.options.length > 1) {
      question.options.splice(optionIndex, 1);
    }
  };

  // Updated: addTrait now accepts an optional description
  const addTrait = (name: string, description?: string) => {
    const newTrait: PersonalityTrait = {
      id: slugify(name),
      name: name,
      description: description || undefined, // Store description
    };
    traits.value.push(newTrait);
  };

  const deleteTrait = (index: number) => {
    const traitToDeleteId = traits.value[index].id;
    traits.value.splice(index, 1);

    questions.value.forEach((question) => {
      question.options.forEach((option) => {
        if (option.scores && option.scores[traitToDeleteId] !== undefined) {
          delete option.scores[traitToDeleteId];
        }
      });
    });
  };

  return {
    questions,
    traits,
    addQuestion,
    deleteQuestion,
    addOption,
    deleteOption,
    addTrait,
    deleteTrait,
  };
}

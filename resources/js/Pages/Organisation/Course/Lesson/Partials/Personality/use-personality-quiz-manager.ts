import { ref } from "vue";
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

// ======================================================
// FIX START: Data Normalization with robust checks
// ======================================================

/**
 * Normalizes question options from the old flat format (trait_id/points)
 * to the required nested format (scores).
 */
function normalizeOptions(options: any[]): PersonalityAnswerOption[] {
  return options.map((option) => {
    // If the old flat format is detected (but not the new scores format)
    if (option && option.trait_id && option.points !== undefined && !option.scores) {
      const scores: { [key: string]: number } = {};
      // Convert old flat fields into the required nested scores object
      scores[option.trait_id] = option.points;

      return {
        id: option.id || nanoid(),
        text: option.text,
        scores: scores, // <-- Required format for the editor
      } as PersonalityAnswerOption;
    }

    // If it's the new, correct format (or a manually added option), return it
    return option as PersonalityAnswerOption;
  });
}

/**
 * Ensures initial questions are correctly mapped and converts any non-array/null
 * options property into an empty array to prevent rendering errors.
 */
function normalizeQuestions(initialQuestions: any[]): PersonalityQuestion[] {
  // 1. Filter out null/bad questions, and map to enforce the question structure
  return initialQuestions
    .filter((q) => q && q.id)
    .map((question) => ({
      ...question,
      // 2. Ensure 'options' is always an array
      options: Array.isArray(question.options)
        ? normalizeOptions(question.options)
        : [],
    })) as PersonalityQuestion[];
}

// ======================================================
// FIX END
// ======================================================

export function usePersonalityQuizManager(
  options: UsePersonalityQuizManagerOptions = {},
) {
  // Normalize initial data
  const normalizedInitialQuestions = normalizeQuestions(options.initialQuestions || []);

  const questions = ref<PersonalityQuestion[]>(normalizedInitialQuestions);
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
      name,
      description: description || undefined,
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

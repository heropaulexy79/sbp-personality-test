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
// FIX START: Robust Data Normalization
// ======================================================

/**
 * Normalizes question options from multiple possible formats
 * (new 'scores', old 'trait_id', raw 'maps_to_archetype')
 * into the required nested format (scores).
 */
function normalizeOptions(
  options: any[],
  initialTraits: PersonalityTrait[],
): PersonalityAnswerOption[] {
  // Robustness check: Ensure options is an array before mapping
  if (!Array.isArray(options)) {
    return [];
  }

  return options.map((option) => {
    // Case 1: Already in correct format (or a new blank option)
    if (option && typeof option.scores === "object" && option.scores !== null) {
      return option as PersonalityAnswerOption;
    }

    // Create a base option to populate.
    // Use 'text' from new/old format, or 'option_text' from raw AI format
    const newOption: PersonalityAnswerOption = {
      id: option?.id || nanoid(), // Add optional chaining
      text: option?.text || option?.option_text || "", // Add optional chaining
      scores: {},
    };

    // Case 2: Old DB format ('trait_id' and 'points')
    if (option && option.trait_id && option.points !== undefined) {
      newOption.scores[option.trait_id] = option.points;
    }
    // Case 3: Raw AI format ('maps_to_archetype')
    else if (option && option.maps_to_archetype) {
      const traitName = option.maps_to_archetype.toLowerCase();
      // Find the corresponding trait from the traits list
      const matchedTrait = initialTraits.find(
        (t) => t.name.toLowerCase() === traitName,
      );
      if (matchedTrait) {
        // Assign 1 point by default, using the trait's ID
        newOption.scores[matchedTrait.id] = 1;
      }
    }

    // Return the normalized option
    return newOption;
  });
}

/**
 * Ensures initial questions are correctly mapped and converts any non-array/null
 * options property into an empty array to prevent rendering errors.
 */
function normalizeQuestions(
  initialQuestions: any[],
  initialTraits: PersonalityTrait[],
): PersonalityQuestion[] {
  // Robustness check: Ensure initialQuestions is an array
  if (!Array.isArray(initialQuestions)) {
    return [];
  }

  // 1. Filter out null/bad questions
  return initialQuestions
    .filter((q) => q) // <-- CORRECT: Only filter nulls
    .map((question) => {
      // 2. Map to the correct structure, adding an ID if it's missing
      return {
        id: question.id || nanoid(), // <-- CORRECT: Add ID if missing
        text: question.text || question.question_text || "", // <-- CORRECT: Handle AI 'question_text'
        type: question.type || "multiple_choice", // <-- CORRECT: Default AI questions

        // 3. CRITICAL FIX:
        //    We must check if options is an array before normalizing it.
        //    My last suggestion removed this, which caused the error.
        options: Array.isArray(question.options)
          ? normalizeOptions(question.options, initialTraits)
          : [],
      };
    }) as PersonalityQuestion[];
}

// ======================================================
// FIX END
// ======================================================

export function usePersonalityQuizManager(
  options: UsePersonalityQuizManagerOptions = {},
) {
  // Normalize initial data
  // We MUST pass both questions and traits to the normalization functions
  const normalizedInitialQuestions = normalizeQuestions(
    options.initialQuestions || [],
    options.initialTraits || [],
  );

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
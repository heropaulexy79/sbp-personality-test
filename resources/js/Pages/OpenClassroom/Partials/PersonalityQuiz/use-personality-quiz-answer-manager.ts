import { computed, ref, watch } from "vue";

import { Answer } from "../types";
import { PersonalityQuestion } from "@/Pages/Organisation/Course/Lesson/Partials/Personality/types";

export function usePersonalityQuizAnswerManager(
  key: string,
  initialQuestions: PersonalityQuestion[] = [],
  initialAnswers?: Answer[],
) {
  const ansPersistKey = ref(`personality:answers:${key}`);
  const lastQuestPersistKey = ref(`personality:question:${key}`);

  const currentQuestionIdx = ref(0);
  const storedLastQuestionId = retrieveLastQuestion();

  if (storedLastQuestionId) {
    const foundIndex = initialQuestions.findIndex(
      (q) => q.id === storedLastQuestionId,
    );
    if (foundIndex !== -1) {
      currentQuestionIdx.value = foundIndex;
    }
  }

  const questions = ref<PersonalityQuestion[]>(initialQuestions);
  const answers = ref(
    initialAnswers
      ? new Map(initialAnswers.map((obj) => [obj.question_id, obj]))
      : retrieveAnswers() ?? new Map<Answer["question_id"], Answer>(),
  );

  const nextQuestion = () => {
    if (hasNextQuestion.value) {
      currentQuestionIdx.value++;
    }
  };

  const previousQuestion = () => {
    if (hasPreviousQuestion.value) {
      currentQuestionIdx.value--;
    }
  };

  const answerQuestion = (
    question_id: Answer["question_id"],
    selected_option_id: string,
  ) => {
    answers.value.set(question_id, {
      question_id,
      selected_option_id,
    });
  };

  function persistAnswers(value: typeof answers.value) {
    localStorage.setItem(
      ansPersistKey.value,
      JSON.stringify(Array.from(value.entries())),
    );
  }

  function retrieveAnswers() {
    if (!localStorage) return null;

    const v = localStorage.getItem(ansPersistKey.value);
    const parsed = v
      ? new Map<Answer["question_id"], Answer>(JSON.parse(v))
      : null;

    return parsed;
  }

  function persistLastQuestion(value: string) {
    localStorage.setItem(lastQuestPersistKey.value, JSON.stringify(value));
  }

  function retrieveLastQuestion(): string | null {
    if (!localStorage) return null;

    const v = localStorage.getItem(lastQuestPersistKey.value);

    const parsed = v ? JSON.parse(v) : null;
    return parsed;
  }

  const hasSelectedAnswer = computed(() =>
    answers.value.has(currentQuestion.value?.id || ""),
  );

  const totalQuestions = computed(() => questions.value.length);

  const hasNextQuestion = computed(
    () => currentQuestionIdx.value < questions.value.length - 1,
  );
  const hasPreviousQuestion = computed(() => currentQuestionIdx.value > 0);

  const currentQuestion = computed(() => {
    return questions.value[currentQuestionIdx.value];
  });

  const currentAnswer = computed(() => {
    const newAnswer = answers.value.get(
      currentQuestion.value?.id || "",
    )?.selected_option_id;
    return newAnswer;
  });

  watch(
    answers,
    (newValue) => {
      persistAnswers(newValue);
    },
    { deep: true },
  );

  watch(
    currentQuestion,
    (newValue) => {
      if (newValue) {
        persistLastQuestion(newValue.id);
      }
    },
    { deep: true },
  );

  return {
    currentQuestionIdx,
    currentQuestion,
    currentAnswer,
    nextQuestion,
    previousQuestion,
    answerQuestion,
    hasSelectedAnswer,
    hasNextQuestion,
    hasPreviousQuestion,
    answers,
    totalQuestions,
  };
}

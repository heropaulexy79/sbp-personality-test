export type Answer = {
  question_id: string;
  selected_option_id: string | string[];
};

export type WithUserLesson<T> = T & {
  completed: boolean;
  answers: Answer[];
};

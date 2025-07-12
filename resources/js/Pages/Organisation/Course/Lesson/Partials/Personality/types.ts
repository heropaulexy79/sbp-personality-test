export interface PersonalityTrait {
  id: string;
  name: string;
  description?: string;
}

export interface PersonalityAnswerOption {
  id: string;
  text: string;
  scores: { [traitId: string]: number }; // Maps trait ID to score impact
}

interface BasePersonalityQuestion {
  id: string;
  text: string;
  options: PersonalityAnswerOption[];
}

export interface PersonalityLikertQuestion extends BasePersonalityQuestion {
  type: "likert_scale";
  // Likert scale questions typically have predefined options
  // You might want to enforce a fixed set of options here or handle in logic
}

export interface PersonalityMultipleChoiceQuestion
  extends BasePersonalityQuestion {
  type: "multiple_choice";
  // Multiple choice questions have custom options
}

export type PersonalityQuestion =
  | PersonalityLikertQuestion
  | PersonalityMultipleChoiceQuestion;

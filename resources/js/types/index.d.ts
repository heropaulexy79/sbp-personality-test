import { Question } from "@/Pages/Organisation/Course/Lesson/Partials/use-quiz-manager";

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string;
    organisation_id: number | null;
    role: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    query: { [key: string]: string };
    flash: {
        [key: string]: any;
        message?: {
            status: "success" | "error";
            message: string;
            action?: {
                "cta:link": string;
                "cta:text": string;
            };

            [k: string]: any;
        };
        "global:message"?: {
            status: "success" | "error";
            message: string;
            action?: {
                "cta:link": string;
                "cta:text": string;
            };
        };
    };
};

export interface Organisation {
    id: number;
    name: string;
}

export interface OrganisationInvite {
    id: number;
    email: string;
    role: string;
    organisation_id: Organisation["id"];
}

export interface Course {
    id: number;
    title: string;
    description: string;
    organisation_id: Organisation["id"];
    is_published: boolean;

    created_at: Date;
    deleted_at: Date | null;
}

export interface Lesson {
    id: number;
    title: string;
    content: string;
    content_json: Question[];
    course_id: Course["id"];
    type: "DEFAULT" | "QUIZ" | (string & {});

    is_published: boolean;

    created_at: Date;
    deleted_at: Date | null;
}

export type Question = SingleChoice | MultipleChoice;

type MultipleChoice = {
    id: string;
    text: string;
    type: "multiple_choice";
    options: Array<QuestionOption>;
    correct_option: string[];
};
type SingleChoice = {
    id: string;
    text: string;
    type: "single_choice";
    options: Array<QuestionOption>;
    correct_option: string;
};

type QuestionOption = {
    id: string;
    text: string;
};

type Paginated<T> = {
    current_page: number;
    data: T[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: {
        url?: string;
        label: string;
        active: boolean;
    }[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
};

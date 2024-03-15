export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string;
    organisation_id: number | null;
    role: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>
> = T & {
    auth: {
        user: User;
    };
    query: { [key: string]: string };
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
    is_published: boolean | 0 | 1;

    created_at: Date;
    deleted_at: Date | null;
}

export type Author = {
    id: number,
    display_name: string,
    avatar: string,
};

export type CreateNoteInput = {
    title: string,
    workspaces?: string,
};

export type Note = {
    title: string,
    id: number,
    author: Author,
    content: string,
    created_at: string,
    updated_at: string,
};

export type User = {
    id: number,
    display_name: string,
    avatar: string,
};

export type Workspace = {
    id: number,
    name: string,
};


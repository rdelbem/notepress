export type Author = {
    display_name: string,
    id: number,
};

export type Note = {
    title: string,
    id: number,
    author: Author,
    content: string,
    created_at: string,
    updated_at: string,
};


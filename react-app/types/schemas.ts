export type AuthHeader = {
    jwt: JWT,
    refreshToken: RefreshToken,
};

export type Author = {
    id: number,
    display_name: string,
    avatar: string,
};

export type CreateNoteInput = {
    title: string,
    workspaces: string,
};

export type JWT = {
    iat: string,
    iss: string,
    exp: string,
    uid: string,
};

export type Note = {
    title: string,
    id: number,
    author: Author,
    content: string,
    workspaces: string,
    created_at: string,
    updated_at: string,
};

export type RefreshToken = {
    refresh_token: string,
    exp: string,
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


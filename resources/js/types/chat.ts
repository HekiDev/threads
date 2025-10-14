import { PaginationLinks, PaginationMeta } from "./pagination";

export interface ChatUser {
    id: number;
    name: string;
    username: string;
    avatar: string;
    status?: string;
}

export interface Chat {
    data: SingleChat[];
    meta: PaginationMeta;
    links: PaginationLinks;
}

export interface ChatMessage {
    data: SingleMessage[];
    meta: PaginationMeta;
    links: PaginationLinks;
}

export interface SingleMessage {
    id: number;
    status: string;
    is_mine: boolean;
    created_at: string;
    message: string;
    user?: {
        id: number;
        name: string;
        avatar: string | null;
    };
    datetime?: string;
    is_readed?: boolean;
}

export interface SingleChat {
    id: number;
    members: {
        id: number;
        name: string;
        username: string;
        avatar: string;
    }[];
    last_message: {
        id: number;
        status: string;
        is_mine: boolean;
        created_at: string;
        datetime?: string;
        message: string;
        user: {
            id: number;
            name: string;
        };
    }
}
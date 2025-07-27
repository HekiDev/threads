export interface ThreadList {
    data: Thread[];
    meta: PaginationMeta;
    links: PaginationLinks;
}

export interface SingleThread {
    data: Thread;
}

export interface Thread {
    uuid: string;
    title: string;
    description: string;
    topic?: string;
    user: ThreadUser;
    created_at: string;
}

export interface ThreadUser {
    id: number;
    name: string;
    username: string;
    avatar: string;
}

export interface PaginationLinks {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
}

export interface PaginationMeta {
    current_page: number | null;
    from: number | null;
    last_page: number | null;
    path?: string | null;
    per_page: number | null;
    to: number | null;
    total: number | null;
    links: PaginationMetaLinks[];
}

export interface PaginationMetaLinks {
    active?: boolean;
    label?: string;
    url?: string;
}
import { PaginationLinks, PaginationMeta } from "./pagination";
export interface ThreadList {
    data: Thread[];
    meta: PaginationMeta;
    links: PaginationLinks;
}

export interface SingleThread {
    data: Thread;
}

export interface Thread extends ThreadComment{
    uuid: string;
    title: string;
    description: string;
    topic?: ThreadTopic;
    user: ThreadUser;
    created_at: string;
    attachments: ThreadAttachment[];
    comments_count: number;
    reactions_count: number;
    reacted: boolean;
}

export interface ThreadComment {
    id: number;
    comment: string;
    replies: ThreadCommentReply[];
    replies_count: number;
}

export interface ThreadCommentReply {
    id: number;
    comment: string;
    user: ThreadUser;
    attachments: ThreadAttachment[];
    created_at: string;
    sub_replies_count: number;
    main_reply?: MainReply;
    reactions_count: number;
    reacted: boolean;
}

export interface MainReply {
    id: number;
    comment: string;
    user: ThreadUser;
}

export interface ThreadUser {
    id: number;
    name: string;
    username: string;
    avatar: string;
    followed: boolean;
}

export interface ThreadTopic {
    id: number;
    name: string;
}

export interface ThreadAttachment {
    id: number;
    url: string;
    file_path: string;
    file_extension: string;
    file_name: string;
    type: string;
}

export interface ThreadFilter {
    by_followers: boolean;
    by_following: boolean;
}
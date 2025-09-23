<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button'
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { ChevronRight, ArrowDown, LoaderCircle } from 'lucide-vue-next';
import { type SingleThread, type ThreadList, type ThreadCommentReply } from '@/types/thread';
import Heading from '@/components/Heading.vue';
import HomeLayout from '@/components/home/HomeLayout.vue';
import Post from '@/components/home/Post.vue';
import { toast } from 'vue-sonner'
import { debounce } from "@/lib/debounce";
import { useCreateThreadStore } from '@/store/useCreateThreadStore';
const threadStore = useCreateThreadStore();

const { post, comments, sorting, subReplyLimit } = defineProps<{
    post: SingleThread;
    comments: ThreadList;
    sorting: string;
    subReplyLimit: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
const user = usePage().props.auth.user;
const page = ref<number>(1);
const activeToComment = ref<number|null>(null);
const isSubmitting = ref<boolean>(false);
const commentSorting = ref<string>(sorting);
const sortingOptions = ref([
    {
        value: 'top',
        label: 'Top',
    },
    {
        value: 'recent',
        label: 'Recent',
    },
])

const handleSendComment = ({uuid, comment}: { uuid: number|string, comment: string }) => {
    page.value = 1
    isSubmitting.value = true
    threadStore.handleSubmitComment({uuid, comment})
    .then((data: any) => {
        toast.success(data.message)
        handleCommentSorting()
    })
    .catch(error => {})
    .finally(() => {
        isSubmitting.value = false
    })
}

const handleSendReply = ({comment_id, comment}: { comment_id: number|string, comment: string }) => {
    isSubmitting.value = true
    threadStore.handleSubmitCommentReply({comment_id, comment})
    .then((data: any) => {
        toast.success(data.message)
        handleCommentSorting()
    })
    .catch(error => {})
    .finally(() => {
        isSubmitting.value = false
    })
}

const handleSendCommentSubReply = ({reply_id, comment}: { reply_id: number|string, comment: string }) => {
    isSubmitting.value = true
    threadStore.handleSubmitCommentSubReply({reply_id, comment})
    .then((data: any) => {
        toast.success(data.message)
        handleCommentSorting()
    })
    .catch(error => {})
    .finally(() => {
        isSubmitting.value = false
    })
}

const handleLoadMoreComments = () => {
    page.value = page.value + 1
    isSubmitting.value = true
    refreshComments()
}

const refreshComments = () => {
    router.get(route('threads.show', {
        username: post.data.user.username,
        uuid: post.data.uuid,
    }), {
        page: page.value,
    },
    {
        only: ['comments'],
        onSuccess: () => {
            isSubmitting.value = false
        },
        preserveScroll: true,
        preserveUrl: true,
        preserveState: true,
    })
}

const setThreadToComment = ({ index }: { index: number|null }) => {
    activeToComment.value = null;
    activeToComment.value = index
}

const handleToggleReact = debounce(({ uuid, reaction, isComment }: { uuid: number|string, reaction: string, isComment: boolean }) => {
    if (isComment) {
        threadStore.handleSubmitCommentReaction({uuid, reaction})
        .then((data: any) => {})
        .catch(error => {
            toast.error('Unable to react.')
        })
    } else {
        threadStore.handleSubmitThreadReaction({uuid, reaction})
        .then((data: any) => {})
        .catch(error => {
            toast.error('Unable to react.')
        })
    }
})

const handleToggleSubReplyReact = debounce(({ sub_reply, reaction }: { sub_reply: ThreadCommentReply, reaction: string }) => {
    threadStore.handleSubmitCommentSubReplyReaction({
        sub_reply_id: sub_reply.id,
        reaction: reaction
    })
    .then((data: any) => {})
    .catch(error => {
        toast.error('Unable to react.')
    })
})

const handleCommentSorting = () => {
    router.get(route('threads.show', {
        username: post.data.user.username,
        uuid: post.data.uuid,
    }), {
        sorting: commentSorting.value,
    }, {
        reset: ['comments'],
        only: ['comments', 'sorting'],
        preserveUrl: true,
        preserveScroll: true,
    })
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6">
            <HomeLayout>
                <template v-slot:header>
                    <Heading title="Thread" description="24.2k views" />
                </template>
                <template v-slot:content>
                    <Post
                        :user
                        className="border rounded-lg"
                        :post="post.data"
                        :index="null"
                        :isCommented="activeToComment === null"
                        :isSubmitting
                        @sendComment="handleSendComment($event)"
                        @toggleComment="setThreadToComment($event)"
                        @toggleReact="handleToggleReact($event)"
                    />
                    <div class="flex flex-col gap-3 py-4">
                        <div class="flex flex-wrap gap-2 justify-between items-center">
                            <Select v-model="commentSorting" @update:modelValue="handleCommentSorting()">
                                <SelectTrigger>
                                    <SelectValue placeholder="Sort By" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem v-for="option in sortingOptions" :value="option.value" :key="option.value">
                                            {{ option.label }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <Button variant="outline">
                                View activity <ChevronRight class="size-4 text-muted-foreground" />
                            </Button>
                        </div>
                        <div v-if="comments.data.length > 0">
                            <Post
                                :user
                                v-for="(comment, index) in comments.data" :key="comment.uuid"
                                :post="comment"
                                :index="index"
                                :isCommented="activeToComment === index"
                                :isComment="true"
                                :subReplyLimit
                                :isSubmitting
                                className="border not-first:border-t not-last:border-b-0 first:rounded-t-lg last:rounded-b-lg"
                                @sendReply="handleSendReply($event)"
                                @sendCommentSubReply="handleSendCommentSubReply($event)"
                                @toggleComment="setThreadToComment($event)"
                                @toggleReact="handleToggleReact($event)"
                                @toggleSubReplyReact="handleToggleSubReplyReact($event)"
                            />
                        </div>
                        <div v-else class="px-5 py-4 bg-accent/20 rounded-lg border text-muted-foreground text-sm text-center">
                            No comments yet.
                        </div>
                        <div class="flex items-center justify-center" v-if="(comments.meta.last_page ?? 0) > (comments.meta.current_page ?? 0)">
                            <Button class="cursor-pointer rounded-full" :disabled="isSubmitting" @click.stop="handleLoadMoreComments()">
                                <LoaderCircle v-if="isSubmitting" class="size-4 animate-spin" />
                                <ArrowDown v-else class="size-4" /> 
                                Load more comments
                            </Button>
                        </div>
                    </div>
                </template>
            </HomeLayout>
        </div>
    </AppLayout>
</template>

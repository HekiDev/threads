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
import { type SingleThread, type ThreadList } from '@/types/thread';
import Heading from '@/components/Heading.vue';
import HomeLayout from '@/components/home/HomeLayout.vue';
import Post from '@/components/home/Post.vue';
import { toast } from 'vue-sonner'
import { useCreateThreadStore } from '@/store/useCreateThreadStore';
const threadStore = useCreateThreadStore();

const { post, comments, subReplyLimit } = defineProps<{
    post: SingleThread;
    comments: ThreadList;
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
const isCommented = ref<boolean>(false);
const isSubmitting = ref<boolean>(false);
const sorting = ref('top');
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
    isCommented.value = false
    threadStore.handleSubmitComment({uuid, comment})
    .then((data: any) => {
        isCommented.value = true
        toast.success(data.message)
        router.reload()
    })
    .catch(error => {})
    .finally(() => {
        isSubmitting.value = false
    })
}

const handleSendReply = ({comment_id, comment}: { comment_id: number|string, comment: string }) => {
    isSubmitting.value = true
    isCommented.value = false
    threadStore.handleSubmitCommentReply({comment_id, comment})
    .then((data: any) => {
        isCommented.value = true
        toast.success(data.message)
        router.reload()
    })
    .catch(error => {})
    .finally(() => {
        isSubmitting.value = false
    })
}

const handleSendCommentSubReply = ({reply_id, comment}: { reply_id: number|string, comment: string }) => {
    isSubmitting.value = true
    isCommented.value = false
    threadStore.handleSubmitCommentSubReply({reply_id, comment})
    .then((data: any) => {
        isCommented.value = true
        toast.success(data.message)
        router.reload()
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
                        :isCommented
                        :isSubmitting
                        @sendComment="handleSendComment($event)"
                    />
                    <div class="flex flex-col gap-3 py-4">
                        <div class="flex flex-wrap gap-2 justify-between items-center">
                            <Select v-model="sorting">
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
                        <div>
                            <Post
                                :user
                                v-for="comment in comments.data" :key="comment.uuid"
                                :post="comment"
                                :isCommented
                                :isComment="true"
                                :subReplyLimit
                                :isSubmitting
                                className="border not-first:border-t not-last:border-b-0 first:rounded-t-lg last:rounded-b-lg"
                                @sendReply="handleSendReply($event)"
                                @sendCommentSubReply="handleSendCommentSubReply($event)"
                            />
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

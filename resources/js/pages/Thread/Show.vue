<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button'
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { ChevronRight } from 'lucide-vue-next';
import { type SingleThread, type ThreadList } from '@/types/thread';
import Heading from '@/components/Heading.vue';
import HomeLayout from '@/components/home/HomeLayout.vue';
import Post from '@/components/home/Post.vue';
import { toast } from 'vue-sonner'
import { useCreateThreadStore } from '@/store/useCreateThreadStore';
const threadStore = useCreateThreadStore();

const { post, comments } = defineProps<{
    post: SingleThread;
    comments: ThreadList;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const isCommented = ref<boolean>(false);
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
    isCommented.value = false
    threadStore.handleSubmitComment({uuid, comment})
    .then((data: any) => {
        isCommented.value = true
        toast.success(data.message)
        router.visit(route('threads.show', {
            username: post.data.user.username,
            uuid: post.data.uuid,
        }), {
            preserveScroll: true,
            only: ['comments'],
        })
    })
    .catch(error => {})
    .finally(() => {})
}

const handleSendReply = ({comment_id, comment}: { comment_id: number|string, comment: string }) => {
    console.log(comment_id, comment)
    threadStore.handleSubmitCommentReply({comment_id, comment})
    .then((data: any) => {
        toast.success(data.message)
    })
    .catch(error => {})
    .finally(() => {})
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
                        className="border rounded-lg"
                        :post="post.data"
                        :isCommented
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
                                v-for="comment in comments.data" :key="comment.uuid"
                                :post="comment"
                                :isComment="true"
                                className="border not-first:border-t not-last:border-b-0 first:rounded-t-lg last:rounded-b-lg"
                                @sendReply="handleSendReply($event)"
                            />
                        </div>
                    </div>
                </template>
            </HomeLayout>
        </div>
    </AppLayout>
</template>

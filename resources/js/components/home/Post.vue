<script setup lang="ts">
import { onUpdated, ref } from 'vue';
import Avatar from '@/components/Avatar.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible'
import { Carousel, CarouselContent, CarouselItem, CarouselNext, CarouselPrevious } from '@/components/ui/carousel'
import { Textarea } from '@/components/ui/textarea'
import { Heart, MessageCircle, Send, Ellipsis, Images, LoaderCircle, Plus } from 'lucide-vue-next';
import PostCommentReply from '@/components/home/PostCommentReply.vue';
import { type Thread, ThreadCommentReply } from '@/types/thread';
import { type User } from '@/types';
import { debounce } from "@/lib/debounce";
import { useCreateThreadStore } from '@/store/useCreateThreadStore';
import { useUserFollowStore } from '@/store/useUserFollowStore';
const threadStore = useCreateThreadStore();
const followStore = useUserFollowStore();

type PageProps = {
    user: User;
    className?: string;
    post: Thread;
    index: number|null;
    isCommented?: boolean;
    isComment?: boolean;
    subReplyLimit?: number;
    isSubmitting?: boolean;
}

const {
    user,
    className,
    post,
    index,
    isCommented = false,
    isComment = false,
    subReplyLimit = 3,
    isSubmitting = false
} = defineProps<PageProps>();

const isOpen = ref<boolean>(false);
const comment = ref<string>('');
const textareaUsername = ref<string>(post.user.name);
const isReplyComment = ref<boolean>(false);
const reply_id = ref<number|string>('');
const reacted = ref<boolean>(post.reacted);
const reactionCount = ref<number>(post.reactions_count);
const isLoading = ref<boolean>(false);
const repliesPage = ref<number>(2);
const repliesCurrentPage = ref<number>(0);
const repliesLastPage = ref<number>(3);
const isFollowing = ref<boolean>(post.user.followed);

const emits = defineEmits<{
    (e: 'sendComment', payload: { uuid: number|string; comment: string }): void
    (e: 'sendReply', payload: { comment_id: number|string; comment: string }): void
    (e: 'sendCommentSubReply', payload: { reply_id: number|string; comment: string }): void
    (e: 'toggleComment', payload: { index: number|null }): void
    (e: 'toggleReact', payload: { uuid: number|string; reaction: string, isComment: boolean }): void
    (e: 'toggleSubReplyReact', payload: { sub_reply: ThreadCommentReply, reaction: string }): void
}>();

const buttons = ref([
    {
        type: 'react',
        icon: Heart,
        label: reactionCount.value,
        onClick: debounce(() => {
            reacted.value = !reacted.value
            reacted.value ? reactionCount.value++ : reactionCount.value--
            emits('toggleReact', {
                uuid: isComment ? post.id : post.uuid,
                reaction: 'heart',
                isComment: isComment,
            })
        })
    },
    {
        type: 'comment',
        icon: MessageCircle,
        label: isComment ? post.replies_count : post.comments_count,
        onClick: () => {
            emits('toggleComment', { index: index })
            textareaUsername.value = post.user.name
            isReplyComment.value = false
            isOpen.value = !isOpen.value
        }
    },
    {
        type: 'send',
        icon: Send,
        label: '',
        onClick: (id: number|string) => {
            console.log('Send clicked', id)
        }
    }
])

const emitSendComment = (uuid: number|string) => {
    emits('sendComment', {
        uuid: uuid,
        comment: comment.value,
    })
}

const emitSendReply = (comment_id: number|string) => {
    emits('sendReply', {
        comment_id: comment_id,
        comment: comment.value,
    })
}

const handleAddCommentSubReply = ({reply}: { reply: ThreadCommentReply }) => {
    reply_id.value = reply.id
    isReplyComment.value = true
    textareaUsername.value = reply.user.name
    if (isOpen.value) return
    isOpen.value = !isOpen.value
}

const handleReactCommentSubReply = ({reply, reaction}: { reply: ThreadCommentReply, reaction: string }) => {
    emits('toggleSubReplyReact', {
        sub_reply: reply,
        reaction: reaction,
    })
}

const emitSendCommentSubReply = () => {
    emits('sendCommentSubReply', {
        reply_id: reply_id.value,
        comment: comment.value,
    })
}

const determineSendAction = () => {
    emits('toggleComment', { index: index })
    if (isReplyComment.value) {
        emitSendCommentSubReply()
    } else {
        isComment ? emitSendReply(post.id) : emitSendComment(post.uuid)
    }
}

const handleLoadMoreReply = () => {
    isLoading.value = true
    threadStore.getMoreReplies({
        comment_id: post.id,
        page: repliesPage.value,
    }).then((data: any) => {
        repliesPage.value++
        repliesCurrentPage.value = data.currentPage
        repliesLastPage.value = data.lastPage
        post.replies = post.replies.concat(data.data)
    }).finally(() => {
        isLoading.value = false
    })
}

const handleFollowUser = debounce((event?: { reply: ThreadCommentReply }) => {
    const userId = event ? event.reply.user.id : post.user.id
    isLoading.value = true
    followStore.handleFollowUser(userId)
    .then((data: any) => {
        console.log(data)
        isFollowing.value = !isFollowing.value
    })
    .catch(error => console.error(error))
    .finally(() => {
        isLoading.value = false
    })
})

onUpdated(() => {
    if (isCommented) {
        comment.value = ''
    }
})
</script>

<template>
    <div class="flex flex-col px-5 py-4 bg-accent/20" :class="className">
        <div class="flex gap-3 relative">
            <div class="flex flex-col gap-2 items-center">
                <DropdownMenu>
                    <DropdownMenuTrigger :as-child="true" @click.stop>
                        <Avatar :user="post.user" class="cursor-pointer" />
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="start" class="w-56">
                        <div class="flex flex-col gap-3 p-3">
                            <div class="flex gap-2 justify-between items-center">
                                <div>
                                    <p>{{ post.user.name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ post.user.username }}</p>
                                </div>
                                <div>
                                    <Avatar :user="post.user" className="size-10" />
                                </div>
                            </div>
                            <Button size="sm" class="w-full cursor-pointer"
                                :disabled="isLoading"
                                @click="handleFollowUser()"
                            >
                                {{ isFollowing ? 'Unfollow' : 'Follow' }}
                            </Button>
                        </div>
                    </DropdownMenuContent>
                </DropdownMenu>
                <Separator v-if="isOpen || post.replies?.length > 0" orientation="vertical" class="rounded-md"/>
            </div>

            <div class="flex gap-1 items-center w-full">
                <div class="flex flex-col gap-1 flex-1">
                    <p class="font-semibold flex flex-wrap items-center gap-1">
                        <span class="text-sm">{{ post.user.name }}</span>
                        <span v-if="post.topic">
                            <Badge variant="outline">
                                {{ post.topic.name }}
                            </Badge>
                        </span>
                        <span class="ml-1 text-xs text-muted-foreground font-medium">{{ post.created_at }}</span>
                    </p>
                    <p class="text-sm whitespace-pre-line break-all">{{ isComment ? post.comment : post.description }}</p>
                    <div class="flex gap-2" v-if="post.attachments.length > 0">
                        <Carousel
                            class="relative w-full"
                            :opts="{
                                align: 'start',
                            }"
                        >
                            <CarouselContent class="sm:w-[250px]">
                                <CarouselItem v-for="item, i in post.attachments" :key="i" class="sm:w-[250px]" :class="['py-1', i !== 0]">
                                    <div class="flex overflow-hidden rounded-md self-start"> 
                                        <img
                                            lazy
                                            :src="item.url"
                                            alt="image"
                                            width="250"
                                            height="300"
                                            class="h-auto w-auto object-cover transition-all hover:scale-105 aspect-[3/4]"
                                        />
                                    </div>
                                </CarouselItem>
                            </CarouselContent>
                        </Carousel>
                    </div>
                    <div class="flex items-center flex-wrap gap-2">
                        <Button
                            v-for="(btn, index) in buttons"
                            :key="index"
                            variant="ghost"
                            class="cursor-pointer text-muted-foreground rounded-full"
                            @click.stop="btn.onClick(isComment ? post.id : post.uuid)"
                        >
                            <component :is="btn.icon"
                                :stroke="reacted && btn.type == 'react' ? 'red' : 'currentColor'"
                                :fill="reacted && btn.type == 'react' ? 'red' : 'none'"
                            />
                            <span v-if="btn.label">{{ btn.type == 'react' ? reactionCount : btn.label }}</span>
                        </Button>
                    </div>
                </div>

                <div class="self-start">
                    <Button variant="ghost" class="cursor-pointer text-muted-foreground" @click.stop>
                        <Ellipsis />
                    </Button>
                </div>
            </div>
        </div>
        
        <div class="flex flex-col" v-if="isComment">
            <PostCommentReply
                v-for="(reply, i) in post.replies" :key="reply.id"
                :reply="reply"
                :isLast="i === post.replies.length - 1"
                :isOpen
                :subReplyLimit
                :isLoading
                @add="handleAddCommentSubReply($event)"
                @react="handleReactCommentSubReply($event)"
                @follow="handleFollowUser($event)"
            />
                <Button variant="link" class="cursor-pointer self-start h-5 px-0 text-muted-foreground"
                    v-if="post.replies_count > subReplyLimit && repliesCurrentPage < repliesLastPage"
                    :disabled="isLoading"
                    @click.stop="handleLoadMoreReply()"
                >
                    Load more reply <LoaderCircle v-if="isLoading" class="animate-spin text-muted-foreground"/>
                </Button>
        </div>

        <Collapsible v-model:open="isOpen">
            <CollapsibleContent class="cursor-auto" @click.stop>
                <div class="text-sm mt-2 flex gap-3">
                    <Avatar :user="user" />
                    <div class="flex flex-1 gap-2">
                        <div class="relative items-end mb-1 w-full">
                            <Textarea
                                @focus="emits('toggleComment', { index: index })"
                                v-model="comment"
                                name="reply"
                                :placeholder="`Reply to ${textareaUsername}`"
                                class="flex-1 min-h-1 pb-12 resize-none overflow-hidden focus-visible:ring-0 focus-visible:border-input break-all"
                            />
                            <div class="absolute bottom-2 w-full flex justify-between px-2">
                                <div class="">
                                    <Button class="cursor-pointer" variant="ghost" size="icon" @click="[]">
                                        <Plus />
                                    </Button>
                                    <Button class="cursor-pointer" variant="ghost" size="icon" @click="[]">
                                        <Images />
                                    </Button>
                                </div>
                                <Button
                                    class="cursor-pointer"
                                    size="icon"
                                    :disabled="!comment || isSubmitting"
                                    @click.stop="determineSendAction()"
                                >
                                    <Send />
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </CollapsibleContent>
        </Collapsible>
    </div>
</template>

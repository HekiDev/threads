<script setup lang="ts">
import { ref, watch } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible'
import { Carousel, CarouselContent, CarouselItem, CarouselNext, CarouselPrevious } from '@/components/ui/carousel'
import { Textarea } from '@/components/ui/textarea'
import { Heart, MessageCircle, Send, Ellipsis } from 'lucide-vue-next';
import PostCommentReply from '@/components/home/PostCommentReply.vue';
import { type Thread, ThreadCommentReply } from '@/types/thread';

const { className, post, isCommented = false, isComment = false, subReplyLimit = 3, isSubmitting = false } = defineProps<{
    className?: string;
    post: Thread;
    isCommented?: boolean;
    isComment?: boolean;
    subReplyLimit?: number;
    isSubmitting?: boolean;
}>();

const isOpen = ref<boolean>(false);
const comment = ref<string>('');
const textareaUsername = ref<string>(post.user.name);
const isReplyComment = ref<boolean>(false);
const reply_id = ref<number|string>('');

const buttons = ref([
    {
        icon: Heart,
        label: '101',
        onClick: (id: number|string) => {
            console.log('Heart clicked', id)
        }
    },
    {
        icon: MessageCircle,
        label: isComment ? post.replies_count : post.comments_count,
        onClick: (id: number|string) => {
            textareaUsername.value = post.user.name
            isReplyComment.value = false
            isOpen.value = !isOpen.value
        }
    },
    {
        icon: Send,
        label: '',
        onClick: (id: number|string) => {
            console.log('Send clicked', id)
        }
    }
])

const emits = defineEmits<{
    (e: 'sendComment', payload: { uuid: number|string; comment: string }): void
    (e: 'sendReply', payload: { comment_id: number|string; comment: string }): void
    (e: 'sendCommentSubReply', payload: { reply_id: number|string; comment: string }): void
}>();

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

const handleReactCommentSubReply = ({reply}: { reply: ThreadCommentReply }) => {
    console.log('react', reply)
}

const emitSendCommentSubReply = () => {
    emits('sendCommentSubReply', {
        reply_id: reply_id.value,
        comment: comment.value,
    })
}

watch(() => isCommented, (value) => {
    if (value) comment.value = '';
})
</script>

<template>
    <div class="flex flex-col px-5 py-4 bg-accent/20" :class="className">
        <div class="flex gap-3 relative">
            <div class="flex flex-col gap-2 items-center">
                <DropdownMenu>
                    <DropdownMenuTrigger :as-child="true" @click.stop>
                        <Avatar class="cursor-pointer">
                            <AvatarImage src="https://github.com/unovue.png" alt="@unovue" />
                            <AvatarFallback>CN</AvatarFallback>
                        </Avatar>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="start" class="w-56">
                        <div class="flex flex-col gap-3 p-3">
                            <div class="flex gap-2 justify-between items-center">
                                <div>
                                    <p>{{ post.user.name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ post.user.username }}</p>
                                </div>
                                <div>
                                    <Avatar class="size-10">
                                        <AvatarImage :src="post.user.avatar" alt="@unovue" />
                                        <AvatarFallback>CN</AvatarFallback>
                                    </Avatar>
                                </div>
                            </div>
                            <Button size="sm" class="w-full">Follow</Button>
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
                    <p class="text-sm">{{ isComment ? post.comment : post.description }}</p>
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
                            <component :is="btn.icon" />
                            <span v-if="btn.label">{{ btn.label }}</span>
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
                @add="handleAddCommentSubReply($event)"
                @react="handleReactCommentSubReply($event)"
            />
                <span v-if="post.replies_count > subReplyLimit">Load more reply</span>
        </div>

        <Collapsible v-model:open="isOpen">
            <CollapsibleContent class="cursor-auto" @click.stop>
                <div class="text-sm mt-2 flex gap-3">
                    <Avatar class="cursor-pointer">
                        <AvatarImage src="https://github.com/unovue.png" alt="@unovue" />
                        <AvatarFallback>CN</AvatarFallback>
                    </Avatar>
                    <div class="flex flex-1 gap-2">
                        <Textarea v-model="comment" rows="1" name="reply" :placeholder="`Reply to ${textareaUsername}`" />
                        <Button class="cursor-pointer rounded-full" :disabled="!comment || isSubmitting" size="icon" @click.stop="emitSendCommentSubReply()"
                            v-if="isReplyComment"
                        >
                            <Send />
                        </Button>
                        <Button v-else
                            class="cursor-pointer rounded-full" :disabled="!comment || isSubmitting" size="icon" @click.stop="isComment ? emitSendReply(post.id) : emitSendComment(post.uuid)"
                        >
                            <Send />
                        </Button>
                    </div>
                </div>
            </CollapsibleContent>
        </Collapsible>
    </div>
</template>

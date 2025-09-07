<script setup lang="ts">
import { ref } from 'vue';
import Avatar from '@/components/Avatar.vue'
import { Button } from '@/components/ui/button'
import { Separator } from '@/components/ui/separator'
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Carousel, CarouselContent, CarouselItem} from '@/components/ui/carousel'
import { Heart, MessageCircle, Send, Ellipsis } from 'lucide-vue-next';
import { type ThreadCommentReply } from '@/types/thread';
import { debounce } from "@/lib/debounce";

const { reply, isLast = false, isOpen = false } = defineProps<{
    reply: ThreadCommentReply;
    isLast: boolean;
    isOpen: boolean;
}>();

const reacted = ref<boolean>(reply.reacted);
const reactionCount = ref<number>(reply.reactions_count);

const emits = defineEmits<{
    (e: 'add', payload: { reply: ThreadCommentReply }): void
    (e: 'react', payload: { reply: ThreadCommentReply, reaction: string }): void
}>();

const buttons = ref([
    {
        type: 'react',
        icon: Heart,
        label: reply.reactions_count,
        onClick: debounce(() => {
            reacted.value = !reacted.value
            reacted.value ? reactionCount.value++ : reactionCount.value--
            emits('react', {
                reply: reply,
                reaction: 'heart',
            })
        })
    },
    {
        type: 'comment',
        icon: MessageCircle,
        label: reply.sub_replies_count,
        onClick: () => {
            emits('add', {
                reply: reply,
            })
        }
    },
])
</script>

<template>
    <div class="flex gap-3 relative mt-3">
        <div class="flex flex-col gap-2 items-center">
            <DropdownMenu>
                <DropdownMenuTrigger :as-child="true" @click.stop>
                    <Avatar :user="reply.user" class="cursor-pointer" />
                </DropdownMenuTrigger>
                <DropdownMenuContent align="start" class="w-56">
                    <div class="flex flex-col gap-3 p-3">
                        <div class="flex gap-2 justify-between items-center">
                            <div>
                                <p>{{ reply.user.name }}</p>
                                <p class="text-xs text-muted-foreground">{{ reply.user.username }}</p>
                            </div>
                            <div>
                                <Avatar :user="reply.user" class="size-10" />
                            </div>
                        </div>
                        <Button size="sm" class="w-full">Follow</Button>
                    </div>
                </DropdownMenuContent>
            </DropdownMenu>
            <Separator v-if="!isLast || isOpen" orientation="vertical" class="rounded-md"/>
        </div>

        <div class="flex gap-1 items-center w-full">
            <div class="flex flex-col gap-1 flex-1">
                <p class="font-semibold flex flex-wrap items-center gap-1">
                    <span class="text-sm">{{ reply.user.name }}</span>
                    <span class="ml-1 text-xs text-muted-foreground font-medium">{{ reply.created_at }}</span>
                </p>
                <p class="text-sm whitespace-pre-line break-all">
                    <span v-if="reply.main_reply" class="bg-muted-foreground/20 p-1 px-2 rounded-full"> @{{ reply.main_reply.user.name }} </span>
                    {{ reply.comment }}
                </p>
                <div class="flex gap-2" v-if="reply.attachments.length > 0">
                    <Carousel
                        class="relative w-full"
                        :opts="{
                            align: 'start',
                        }"
                    >
                        <CarouselContent class="sm:w-[250px]">
                            <CarouselItem v-for="item, i in reply.attachments" :key="i" class="sm:w-[250px]" :class="['py-1', i !== 0]">
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
                        @click.stop="btn.onClick()"
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
</template>

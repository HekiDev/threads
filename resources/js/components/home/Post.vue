<script setup lang="ts">
import { ref } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible'
import { Carousel, CarouselContent, CarouselItem, CarouselNext, CarouselPrevious } from '@/components/ui/carousel'
import { Textarea } from '@/components/ui/textarea'
import { Heart, MessageCircle, Send, Ellipsis } from 'lucide-vue-next';
import { type Thread } from '@/types/thread';

const { className, post } = defineProps<{
    className?: string;
    post: Thread;
}>();

const isOpen = ref<boolean>(false);
const comment = ref<string>('');

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
        label: '10',
        onClick: (id: number|string) => {
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

const handleSendComment = () => {
    console.log(comment.value)
}
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
                <Separator v-if="isOpen" orientation="vertical" class="rounded-md"/>
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
                    <p class="text-sm">{{ post.description }}</p>
                    <div class="flex gap-2">
                        <Carousel
                            class="relative w-full"
                            :opts="{
                                align: 'start',
                            }"
                        >
                            <CarouselContent class="lg:w-[250px]">
                                <CarouselItem v-for="i in Math.floor(Math.random() * 4)" class="lg:w-[250px]" :class="['py-1', i !== 0]">
                                    <div class="flex overflow-hidden rounded-md self-start"> 
                                        <img
                                            lazy
                                            src="https://images.unsplash.com/photo-1490300472339-79e4adc6be4a?w=300&dpr=2&q=80"
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
                            @click.stop="btn.onClick(post.uuid)"
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

        <Collapsible v-model:open="isOpen">
            <CollapsibleContent class="cursor-auto" @click.stop>
                <div class="text-sm mt-2 flex gap-3">
                    <Avatar class="cursor-pointer">
                        <AvatarImage src="https://github.com/unovue.png" alt="@unovue" />
                        <AvatarFallback>CN</AvatarFallback>
                    </Avatar>
                    <div class="flex flex-1 gap-2">
                        <Textarea v-model="comment" rows="1" name="reply" :placeholder="`Reply to ${post.user.name}`" />
                        <Button class="cursor-pointer rounded-full" :disabled="!comment" size="icon" @click.stop="handleSendComment()">
                            <Send />
                        </Button>
                    </div>
                </div>
            </CollapsibleContent>
        </Collapsible>
    </div>
</template>

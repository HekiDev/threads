<script setup lang="ts">
import { ref } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Heart, MessageCircle, Send, Ellipsis } from 'lucide-vue-next';
import { type Thread } from '@/types/thread';

const { className, post } = defineProps<{
    className?: string;
    post: Thread;
}>();

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
            console.log('Message clicked', id)
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
</script>

<template>
    <div class="flex gap-3 items-start px-5 py-4 bg-accent/20" :class="className">
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
</template>

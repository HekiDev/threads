<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { EllipsisVertical } from 'lucide-vue-next'
import { type ChatUser } from '@/types/chat';
import Avatar from '../Avatar.vue';
import TypingIndicator from './TypingIndicator.vue';

const { chat_id = null, user, isTyping = false, typingUserId = null } = defineProps<{
    chat_id: number | null,
    user: ChatUser,
    isTyping: boolean,
    typingUserId: number | null,
}>();
</script>

<template>
    <div class="flex w-full items-center">
        <div class="flex flex-row flex-1 gap-2 items-center">
            <Avatar :user="user" />
            <div class="">
                <p class="font-medium">{{ user.name }}</p>
                <div class="flex items-center gap-1">
                    <p class="text-xs capitalize"
                        :class="{
                            'text-green-500': user.status === 'online',
                            'text-muted-foreground': user.status === 'offline',
                        }"
                    >{{ user?.status ? user.status : '-' }}</p>

                    <p class="text-xs text-muted-foreground flex items-center"
                        v-if="isTyping && typingUserId === user.id"
                    >
                        <TypingIndicator />
                        <span class="text-xs italic text-muted-foreground">Typing...</span>
                    </p>
                </div>
            </div>
        </div>
        <Button variant="outline" v-if="chat_id">
            <EllipsisVertical />
        </Button>
    </div>
</template>
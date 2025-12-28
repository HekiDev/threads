<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { EllipsisVertical, Ban, RotateCcw } from 'lucide-vue-next'
import { type ChatUser, type ShowChat } from '@/types/chat';
import Avatar from '../Avatar.vue';
import TypingIndicator from './TypingIndicator.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger, DropdownMenuItem } from '@/components/ui/dropdown-menu';
import { useChatStore } from '@/store/useChatStore';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const { chat_id = null, user, isTyping = false, typingUserId = null, chat } = defineProps<{
    chat_id: number | null,
    user: ChatUser,
    isTyping: boolean,
    typingUserId: number | null,
    chat: ShowChat,
}>();

const chatStore = useChatStore();
const isChatBlocked = defineModel('isChatBlocked', { type: Boolean, default: false });
const auth = usePage().props.auth;

const handleBlockUser = () => {
    if (! chat_id) return;

    chatStore.blockUser({
        user_id: user.id,
        chat_id: chat_id,
    }).then(() => {
        if (blockedByMe.value) {
            isChatBlocked.value = ! isChatBlocked.value;
        }
    })
}

const blockedByMe = computed(() => isChatBlocked.value && chat.blocker_user_id === auth.user.id);
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
        <DropdownMenu>
            <DropdownMenuTrigger :as-child="true">
                <Button variant="outline" v-if="chat_id">
                    <EllipsisVertical />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" class="w-44">
                <DropdownMenuItem @select="handleBlockUser()">
                    <Ban v-if="!blockedByMe"/>
                    <RotateCcw v-else/>
                    {{ blockedByMe ? 'Unblock' : 'Block' }}
                    <span class="font-bold truncate text-ellipsis">{{ user.name }}</span>
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    </div>
</template>
<script setup lang="ts">
import { SingleChat } from '@/types/chat';
import Avatar from '../Avatar.vue';

const { chat, isActive } = defineProps<{
    chat: SingleChat
    isActive: boolean
}>()

const emits = defineEmits<{
    (e: 'whenClick', chat: SingleChat): void
}>();
</script>

<template>
    <div class="flex items-center gap-3 px-4 py-3 break-all select-none not-first:border-t not-last:border-b-0 hover:bg-accent cursor-pointer"
        @click="emits('whenClick', chat)"
        :class="{'bg-accent': isActive}"
    >
        <Avatar className="size-9"
            v-for="member in chat.members"
            :key="member.id"
            :user="member"
        />
        <div class="truncate text-ellipsis break-all w-full">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium truncate text-ellipsis">{{ chat.members[0].name }}</p>
                <p class="text-xs text-muted-foreground">{{ chat.last_message.datetime }}</p>
            </div>
            <p class="text-sm text-muted-foreground truncate text-ellipsis break-all">
                <span class="font-medium" v-if="chat.last_message.is_mine">You:</span> {{ chat.last_message.message }}
            </p>
        </div>
    </div>
</template>
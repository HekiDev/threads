<script setup lang="ts">
import { CheckCheck } from 'lucide-vue-next'
import Avatar from '../Avatar.vue';
import { type SingleMessage } from '@/types/chat';

interface ChatBubbleProps {
    message: SingleMessage
    previousMessage?: SingleMessage | null;
}

const { message, previousMessage } = defineProps<ChatBubbleProps>();

const sentInSameMinute = () => {
    if (!previousMessage) return true;

    const current = new Date(message.created_at).getTime();
    const previous = new Date(previousMessage.created_at).getTime();
    const diffInSeconds = Math.abs(current - previous) / 1000;

    const isSameUser = message.user && previousMessage.user ?
        message.user.id === previousMessage.user.id : true;

    return !(isSameUser && diffInSeconds < 60);
}
</script>

<template>
    <div
        class="flex py-1"
        :class="message.is_mine ? 'justify-end' : 'justify-start'"
    >
        <div class="flex flex-col w-max max-w-[75%] gap-1">
            <div class="flex">
                <div class="place-self-end min-w-[40px]">
                    <!-- Avatar -->
                    <Avatar v-if="message.user && !message.is_mine && sentInSameMinute()" :user="message.user" />
                </div>

                <!-- Message -->
                <div
                    class="rounded-lg px-3 py-2 text-sm whitespace-pre-line break-all"
                    :class="message.is_mine
                        ? 'bg-primary text-primary-foreground rounded-br-none'
                        : 'bg-muted text-foreground rounded-bl-none'
                    "
                >
                    {{ message.message }}
                </div>
            </div>

            <!-- Timestamp -->
            <div
                v-if="sentInSameMinute()"
                class="text-[11px] text-muted-foreground select-none flex"
                :class="message.is_mine ? 'self-end' : 'self-start'"
            >
                {{ message.datetime }}
                <span class="ml-1" v-if="message.is_mine">
                    <CheckCheck class="size-4" :class="{'text-green-500' : message.status === 'read'}" />
                </span>
            </div>
        </div>
    </div>
</template>
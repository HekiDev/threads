<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

import AppLayout from '@/layouts/AppLayout.vue';
import { ScrollArea } from '@/components/ui/scroll-area'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Plus, Search, ArrowLeft } from 'lucide-vue-next'
import ChatBubble from '@/components/chat/ChatBubble.vue';
import ChatInput from '@/components/chat/ChatInput.vue';
import { type BreadcrumbItem } from '@/types';
import { type ChatUser, type Chat, type SingleChat, type ChatMessage } from '@/types/chat';
import CreateChat from '@/components/chat/CreateChat.vue';
import ChatHeader from '@/components/chat/ChatHeader.vue';
import EmptyChat from '@/components/chat/EmptyChat.vue';
import Avatar from '@/components/Avatar.vue';

const { chats, messages, active_chat_id = null } = defineProps<{
    chats: Chat;
    messages?: ChatMessage;
    active_chat_id?: number | null;
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Chat',
        href: '/chats',
    },
];
const isLoading = ref<boolean>(false);
const newChatDialog = ref<boolean>(false);
const isNewChat = ref<boolean>(false);
const hasSelectedChat = ref<boolean>(false);
const chat_id = ref<number|null>(active_chat_id);
const user = ref<ChatUser>({
    id: 1,
    name: 'Jacquenetta Slowgrave',
    username: '@jacquenetta',
    avatar: 'https://bundui-images.netlify.app/avatars/01.png',
})

const handleGetChatMessages = (chat: SingleChat) => {
    chat_id.value = chat.id
    user.value = chat.members[0]
    isNewChat.value = false
    hasSelectedChat.value = true

    router.get(route('chat.messages', chat.id),
    {},{
        onStart: () => isLoading.value = true,
        onSuccess: () => isLoading.value = false,
        only: ['messages', 'active_chat_id'],
        preserveUrl: true,
        preserveScroll: true,
        preserveState: true,
    })
}

const toggleCreateNewChat = (event: { user: ChatUser }) => {
    user.value = event.user
    chat_id.value = null
    isNewChat.value = true
    newChatDialog.value = false
    hasSelectedChat.value = true
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Chats" />

        <div class="px-4 py-6">
            <div class="flex flex-col h-[calc(100vh-8rem)]">
                <div class="@container/main h-full">
                    <div class="flex flex-col h-full gap-3 @sm/main:flex-row">

                        <!-- Chat List Section -->
                        <section 
                            class="flex flex-col w-full @3xl/main:w-1/4 border rounded-md"
                            :class="{
                                'hidden @3xl/main:flex': hasSelectedChat, // hide when a chat is selected (mobile only)
                                '@3xl/main:flex': true,                   // always show on desktop
                            }"
                        >
                            <div class="flex flex-col gap-3 p-4 pb-5 overflow-hidden">
                                <div class="flex justify-between items-center">
                                    <h1 class="font-semibold text-lg">Chats</h1>
                                    <CreateChat
                                        v-model:dialog="newChatDialog"
                                        @createNewChat="toggleCreateNewChat($event)"
                                    >
                                        <template v-slot:createChatDialogTrigger>
                                            <Button variant="outline"><Plus /></Button>
                                        </template>
                                    </CreateChat>
                                </div>
                                <div class="flex">
                                    <div class="relative w-full items-center">
                                        <Input id="search" type="text" placeholder="Search chats" class="pl-8 focus:flex-1" />
                                        <span class="absolute start-0 inset-y-0 flex items-center justify-center px-2">
                                            <Search class="size-4 text-muted-foreground" />
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <ScrollArea class="w-full h-full overflow-auto">
                                <div class="flex items-center gap-3 px-4 py-3 break-all select-none not-first:border-t not-last:border-b-0 hover:bg-accent cursor-pointer"
                                    v-for="chat in chats.data"
                                    :key="chat.id"
                                    @click="handleGetChatMessages(chat)"
                                    :class="{'bg-accent': chat.id === chat_id}"
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
                            </ScrollArea>
                        </section>

                        <!-- Chat Messages Section -->
                        <section class="flex w-full @3xl/main:w-3/4"
                            :class="{
                                'hidden @3xl/main:flex': !hasSelectedChat, // hide when no chat selected (mobile only)
                                '@3xl/main:flex': true,                    // always show on desktop
                            }"
                        >
                            <div class="flex flex-col w-full gap-2" v-if="hasSelectedChat">
                                <div class="flex gap-2 items-center">
                                    <Button class="@3xl/main:hidden" variant="outline" @click="hasSelectedChat = false">
                                        <ArrowLeft />
                                    </Button>
                                    <ChatHeader
                                        :chat_id="chat_id"
                                        :user="user"
                                    />
                                </div>
                                <ScrollArea class="flex flex-1 overflow-auto" v-if="!isNewChat && messages">
                                    <ChatBubble
                                        v-for="(message, index) in messages.data"
                                        :key="message.id"
                                        :message="message"
                                        :previousMessage="messages.data[index + 1] ?? null"
                                    />
                                </ScrollArea>
                                <EmptyChat
                                    v-else
                                    :title="`Chat with ${user.name }`"
                                    description="Start a conversation with this user."
                                />
                                <ChatInput :disabled="isLoading" />
                            </div>
                            <EmptyChat
                                v-else
                                title="No Selected Chat"
                                description="Select a chat to start a conversation."
                            />
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
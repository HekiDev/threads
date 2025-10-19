<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, watch, watchEffect } from 'vue';
import { scrollToBottom, updateAndResortChats } from '@/lib/chats';
import { toggleChatChannel } from '@/lib/listeners';
import { eventMessageHandler } from '@/lib/listeners';

import AppLayout from '@/layouts/AppLayout.vue';
import { ScrollArea } from '@/components/ui/scroll-area'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Plus, Search, ArrowLeft } from 'lucide-vue-next'
import { type BreadcrumbItem } from '@/types';
import { type ChatUser, type Chat, type SingleChat, type ChatMessage, SingleMessage } from '@/types/chat';
import ChatBubble from '@/components/chat/ChatBubble.vue';
import ChatInput from '@/components/chat/ChatInput.vue';
import CreateChat from '@/components/chat/CreateChat.vue';
import ChatHeader from '@/components/chat/ChatHeader.vue';
import EmptyChat from '@/components/chat/EmptyChat.vue';
import ChatList from '@/components/chat/ChatList.vue';
import { useChatStore } from '@/store/useChatStore';

const { chats, messages, active_chat_id = null } = defineProps<{
    chats: Chat;
    messages?: ChatMessage;
    active_chat_id?: number | null;
}>();
const chatStore = useChatStore();
const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Chat',
        href: '/chats',
    },
];
const message = ref<string>('');
const isLoading = ref<boolean>(false);
const newChatDialog = ref<boolean>(false);
const isNewChat = ref<boolean>(false);
const hasSelectedChat = ref<boolean>(false);
const chat_id = ref<number|null>(active_chat_id);
const user = ref<ChatUser>({
    id: 0,
    name: 'Jacquenetta Slowgrave',
    username: '@jacquenetta',
    avatar: 'https://bundui-images.netlify.app/avatars/01.png',
})
const scrollAreaRef = ref(null);
const localChats = ref<SingleChat[]>([]);
const localMessages = ref<SingleMessage[]>([]);
const chatInputRef = ref<InstanceType<typeof ChatInput> | null>(null)

const handleGetChatMessages = (chat: SingleChat) => {
    chat_id.value = chat.id
    user.value = chat.members[0]
    isNewChat.value = false
    hasSelectedChat.value = true

    router.get(route('chat.messages', chat.id),
    {},{
        preserveUrl: true,
        preserveState: true,
        preserveScroll: true,
        only: ['messages', 'active_chat_id'],
        onStart: () => isLoading.value = true,
        onSuccess: () => {
            isLoading.value = false;
            scrollToBottom(scrollAreaRef)
            chatInputRef.value?.focusChatInput()
        },
    })
}

const toggleCreateNewChat = (event: { user: ChatUser }) => {
    user.value = event.user
    chat_id.value = null
    isNewChat.value = true
    newChatDialog.value = false
    hasSelectedChat.value = true
}

const handleSendMessage = () => {
    isLoading.value = true;
    if (! chat_id.value) {
        chatStore.handleStoreChat({
            user_id: user.value.id,
            message: message.value,
        })
        .then((data: any) => {
            router.reload()
            message.value = ''
            setTimeout(() => {
                handleGetChatMessages(data.chat)
            }, 250)
        })
        .catch(error => {})
        .finally(() => {
            isLoading.value = false
            chatInputRef.value?.focusChatInput()
        })
    } else {
        chatStore.handleStoreChatMessage({
            message: message.value,
            chat_id: chat_id.value,
        })
        .then((data: any) => {
            message.value = ''
            localMessages.value.push(data.message)
            localChats.value = updateAndResortChats(localChats.value, data)
        })
        .catch(error => {})
        .finally(() => {
            isLoading.value = false
            chatInputRef.value?.focusChatInput()
        })
    }
}

watch(() => messages, (value: any) => {
    if (! messages) return
    localMessages.value = value.data;
})

watchEffect(() => {
    localChats.value = chats.data
})

watch(() => localMessages.value.length, () => {
    scrollToBottom(scrollAreaRef)
})

watch(() => chat_id.value, (newValue, oldValue) => {
    if (newValue === oldValue) return
    if (newValue && ! oldValue) {
        toggleChatChannel(newValue, 'enter')
    } else {
        toggleChatChannel(oldValue, 'leave')
        toggleChatChannel(newValue, 'enter')
    }
})

watch(eventMessageHandler, (newMessage) => {
    localMessages.value.push({
        ...newMessage.message,
        is_mine: false,
    })
    localChats.value = updateAndResortChats(localChats.value, {
        ...newMessage,
        message: {
            ...newMessage.message,
            is_mine: false,
        }
    })
})
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
                            <ScrollArea class="w-full h-full overflow-auto" v-if="chats && localChats.length">
                                <ChatList v-for="chat in localChats"
                                    :key="chat.id"
                                    :chat="chat"
                                    :isActive="chat.id === chat_id"
                                    @whenClick="handleGetChatMessages(chat)"
                                />
                            </ScrollArea>
                            <div class="text-center text-muted-foreground text-sm h-full p-5" v-else>No chats available.</div>
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
                                <ScrollArea class="flex flex-1 overflow-auto" ref="scrollAreaRef" v-if="!isNewChat && localMessages.length">
                                    <ChatBubble
                                        v-for="(message, index) in localMessages"
                                        :key="message.id"
                                        :message="message"
                                        :previousMessage="localMessages[index + 1] ?? null"
                                        :nextMessage="localMessages[index - 1] ?? null"
                                    />
                                </ScrollArea>
                                <EmptyChat
                                    v-else
                                    :title="`Chat with ${user.name }`"
                                    description="Start a conversation with this user."
                                />
                                <ChatInput 
                                    ref="chatInputRef"
                                    v-model:message="message"
                                    :disabled="isLoading"
                                    @sendMessage="handleSendMessage()"
                                />
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
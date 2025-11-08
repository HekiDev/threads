<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref, watch, watchEffect } from 'vue';
import { scrollToBottom, updateAndResortChats, handleSearchChat } from '@/lib/chats';
import {
    joinedUser,
    leavedUser,
    channelUsersHandler,
    eventMessageHandler,
    toggleChatChannel,
    toggleChatPageChannel
} from '@/lib/listeners';

import AppLayout from '@/layouts/AppLayout.vue';
import { ScrollArea } from '@/components/ui/scroll-area'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Plus, Search, ArrowLeft, LoaderCircle } from 'lucide-vue-next'
import { type BreadcrumbItem } from '@/types';
import { type ChatUser, type Chat, type SingleChat, type ChatMessage, SingleMessage } from '@/types/chat';
import ChatBubble from '@/components/chat/ChatBubble.vue';
import ChatInput from '@/components/chat/ChatInput.vue';
import CreateChat from '@/components/chat/CreateChat.vue';
import ChatHeader from '@/components/chat/ChatHeader.vue';
import EmptyChat from '@/components/chat/EmptyChat.vue';
import ChatList from '@/components/chat/ChatList.vue';
import { useChatStore } from '@/store/useChatStore';
import WhenVisible from '@/components/WhenVisible.vue';
import { debounce } from '@/lib/debounce';

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
const search = ref<string>('');
const user_id = ref(usePage().props.auth.user.id ?? 0);
const message = ref<string>('');
const isLoading = ref<boolean>(false);
const newChatDialog = ref<boolean>(false);
const isNewChat = ref<boolean>(false);
const hasSelectedChat = ref<boolean>(false);
const chat_id = ref<number|null>(active_chat_id);
const user = ref<ChatUser>({
    id: 0,
    name: '',
    username: '',
    avatar: '',
    status: '',
})
const scrollAreaRef = ref(null);
const localChats = ref<SingleChat[]>([]);
const localMessages = ref<SingleMessage[]>([]);
const chatInputRef = ref<InstanceType<typeof ChatInput> | null>(null)
const activeRecipients = ref<any[]>([]);
const chatChannel = ref<any>(null);
const isTyping = ref<boolean>(false);
const typingTimeout = ref<number|null>(null);
const typingUserId = ref<number|null>(null);
const ignoreWatcher = ref<boolean>(false);
const messagesPage = ref<number>(1);

const handleGetChatMessages = (chat: SingleChat) => {
    chat_id.value = chat.id
    user.value = chat.members[0]
    isNewChat.value = false
    hasSelectedChat.value = true
    messagesPage.value = 1

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
    ignoreWatcher.value = false
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
            updateMessageStatus()
        })
        .catch(error => {})
        .finally(() => {
            isLoading.value = false
            chatInputRef.value?.focusChatInput()
        })
    }
}

const updateMessageStatus = () => {
    const isActiveRecipient = channelUsersHandler().find((u) => u.id === user.value.id);
    if (! isActiveRecipient) return;

    const idsToMark = localMessages.value
        .filter(m => m.status === 'sent' && m.is_mine === true)
        .map(m => m.id);

    idsToMark.forEach(id => {
        const msg = localMessages.value.find(m => m.id === id)
        setTimeout(() => {
            if (msg) {
                msg.status = 'read';
            }
        }, 500)
    })
}

const sendTypingWhisperEvent = () => {
    chatChannel.value?.whisper('typing', {
        sender_id: user_id.value,
    })
}

const listenTypingWhisperEvent = () => {
    chatChannel.value?.listenForWhisper('typing', (e: any) => {
        isTyping.value = true;
        typingUserId.value = e.sender_id

        if (typingTimeout.value) clearTimeout(typingTimeout.value)

        typingTimeout.value = setTimeout(() => {
            isTyping.value = false;
        }, 1000)
    })
}

const handleLoadOlderMessages = debounce(() => {
    ignoreWatcher.value = true
    messagesPage.value = messagesPage.value + 1
    if (! chat_id.value || messagesPage.value > (messages?.meta.last_page ?? 0)) return

    chatStore.handleLoadOlderMessages({
        page: messagesPage.value,
        chat_id: chat_id.value,
    }).then((data: any) => {
        localMessages.value.unshift(...data.data)
        scrollToBottom(scrollAreaRef, true, 200)
    })
    .catch(error => {})
    .finally(() => {})
})

watch(() => joinedUser.value, (value) => {
    if (! value) return
    activeRecipients.value.push(value)

    if (value.id === user.value.id) {
        user.value.status = 'online';
        updateMessageStatus()
    }
})

watch(() => leavedUser.value, (value) => {
    if (! value) return
    activeRecipients.value = activeRecipients.value.filter(u => u.id !== value.id)

    if (value.id === user.value.id) {
        user.value.status = 'offline';
    }
})

watch(() => messages, (value: any) => {
    if (! messages) return
    localMessages.value = value.data;
})

watchEffect(() => {
    localChats.value = chats.data
})

watch(() => localMessages.value.length, () => {
    if (ignoreWatcher.value) return
    scrollToBottom(scrollAreaRef)
})

watch(() => chat_id.value, (newValue, oldValue) => {
    if (newValue === oldValue) return
    if (newValue && ! oldValue) {
        chatChannel.value = toggleChatChannel(newValue, 'enter')
        listenTypingWhisperEvent()
    } else {
        toggleChatChannel(oldValue, 'leave')
        chatChannel.value = toggleChatChannel(newValue, 'enter')
        listenTypingWhisperEvent()
    }
})

watch(() => eventMessageHandler()?.message.id, (value) => {
    ignoreWatcher.value = false
    const newMessage = eventMessageHandler();
    if (! newMessage) return

    if (chat_id.value === newMessage.chat_id) {
        localMessages.value.push({
            ...newMessage.message,
            is_mine: false,
        })
    }

    localChats.value = updateAndResortChats(localChats.value, {
        ...newMessage,
        message: {
            ...newMessage.message,
            is_mine: false,
        }
    })
})

onMounted(() => {
    if (chat_id.value) return
    toggleChatPageChannel(user_id.value, 'enter')
})

onBeforeUnmount(() => {
    if (chat_id.value) {
        toggleChatChannel(chat_id.value, 'leave')
    }
    if (typingTimeout.value) clearTimeout(typingTimeout.value)
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
                                        <Input id="search" type="text" placeholder="Search chats" class="pl-8 focus:flex-1"
                                            v-model="search"
                                            @keydown.enter.exact.prevent="handleSearchChat(search)"
                                            @keydown.shift.enter.stop
                                        />
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
                                        :isTyping="isTyping"
                                        :typingUserId="typingUserId"
                                    />
                                </div>
                                <ScrollArea class="flex flex-1 overflow-auto" ref="scrollAreaRef" v-if="!isNewChat && localMessages.length">
                                    <WhenVisible @visible="handleLoadOlderMessages()"
                                        v-if="(messages?.meta.last_page ?? 0) > messagesPage"
                                    >
                                        <div class="flex justify-center text-muted-foreground items-center text-xs py-3">
                                            <LoaderCircle class="mr-1 animate-spin text-muted-foreground"/> Loading..
                                        </div>
                                    </WhenVisible>
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
                                    @sendTypingWhiper="sendTypingWhisperEvent()"
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
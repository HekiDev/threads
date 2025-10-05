<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

import AppLayout from '@/layouts/AppLayout.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { ScrollArea } from '@/components/ui/scroll-area'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Plus, Search, ArrowLeft, EllipsisVertical } from 'lucide-vue-next'
import ChatBubble from '@/components/chat/ChatBubble.vue';
import ChatInput from '@/components/chat/ChatInput.vue';
import { type BreadcrumbItem } from '@/types';
import CreateChat from '@/components/chat/CreateChat.vue';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Chat',
        href: '/chats',
    },
];
const hasSelectedChat = ref<boolean>(false);

const handleGetChatMessages = (id: number) => {
    hasSelectedChat.value = true
    console.log(id)
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
                                    <CreateChat>
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
                                <div class="flex items-center gap-3 py-2 break-all not-first:border-t not-last:border-b-0 px-4 hover:bg-accent cursor-pointer"
                                    v-for="i in 20"
                                    :key="i"
                                    @click="handleGetChatMessages(i)"
                                >
                                    <Avatar>
                                        <AvatarImage class="object-cover" src="https://bundui-images.netlify.app/avatars/01.png" alt="avatar image" />
                                        <AvatarFallback class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white">
                                        </AvatarFallback>
                                    </Avatar>
                                    <div class="truncate text-ellipsis break-all space-y-1">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-medium truncate text-ellipsis">Jacquenetta Slowgrave</p>
                                            <p class="text-xs text-muted-foreground">10 min ago</p>
                                        </div>
                                        <p class="text-xs text-muted-foreground truncate text-ellipsis break-all">
                                            <span class="font-medium">You:</span> Lorem ipsum dolor sit amet consectetur adipisicing elit. Quasi tempore tenetur explicabo quo beatae illum assumenda doloremque temporibus rerum tempora.
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
                                    <div class="flex w-full items-center">
                                        <div class="flex flex-row flex-1 gap-2 items-center">
                                            <Avatar>
                                                <AvatarImage class="object-cover" src="https://bundui-images.netlify.app/avatars/01.png" alt="avatar image" />
                                                <AvatarFallback class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white">
                                                </AvatarFallback>
                                            </Avatar>
                                            <div class="">
                                                <p class="font-medium">Jacquenetta Slowgrave</p>
                                                <p class="text-xs text-muted-foreground">10 min ago</p>
                                            </div>
                                        </div>
                                        <Button variant="outline">
                                            <EllipsisVertical />
                                        </Button>
                                    </div>
                                </div>
                                <ScrollArea class="flex flex-1 overflow-auto">
                                    <ChatBubble
                                        v-for="i in 2"
                                        :key="i"
                                        :isMine="false"
                                        :message="`Hi, how can I help you today? Lorem ipsum dolor sit amet consectetur adipisicing elit. Natus ipsa laudantium consectetur fugit aliquam placeat rerum deleniti voluptatibus, id consequuntur labore, dolorem aperiam facere explicabo rem molestias non assumenda necessitatibus!`"
                                        :timestamp="'10:24 AM'"
                                        status="sent"
                                    />
                                    <ChatBubble
                                        v-for="i in 1"
                                        :key="i"
                                        :isMine="true"
                                        :message="`Hi, how can I help you today? Lorem ipsum dolor sit amet consectetur adipisicing elit. Natus ipsa laudantium consectetur fugit aliquam placeat rerum deleniti voluptatibus, id consequuntur labore, dolorem aperiam facere explicabo rem molestias non assumenda necessitatibus!`"
                                        :timestamp="'10:24 AM'"
                                        status="read"
                                    />
                                    <ChatBubble
                                        v-for="i in 1"
                                        :key="i"
                                        :isMine="true"
                                        :message="`Hey?`"
                                        :timestamp="'10:24 AM'"
                                        status="read"
                                    />
                                </ScrollArea>
                                <ChatInput />
                            </div>
                            <div class="flex items-center justify-center h-full w-full" v-else>
                                <p class="text-muted-foreground">Select chat to start conversation</p>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
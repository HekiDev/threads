<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

import { Input } from '@/components/ui/input'
import { Search, Check } from 'lucide-vue-next'
import Avatar from '../Avatar.vue';
import { ref } from 'vue';

interface User {
    id: number;
    name: string;
    username: string;
    avatar: string;
}

const selectedUser = ref<null|User>(null);

const users = [
    {
        id: 1,
        name: 'Olivia Martin',
        username: '@olivia',
        avatar: 'https://bundui-images.netlify.app/avatars/01.png',
    },
    {
        id: 2,
        name: 'Isabella Nguyen',
        username: '@isabella',
        avatar: 'https://bundui-images.netlify.app/avatars/02.png',
    },
]

const toggleSelectedUser = (user: User) => {
    selectedUser.value = user;
}

const toggleNewChat = () => {
    console.log(selectedUser.value)
}

</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <slot name="createChatDialogTrigger" />
        </DialogTrigger>
        <DialogContent class="p-4">
            <DialogHeader>
                <DialogTitle>New Chat</DialogTitle>
                <DialogDescription>
                    Start a new conversation with anyone by entering their username.
                </DialogDescription>
            </DialogHeader>
            <div class="-mx-4">
                <div class="relative w-full items-center">
                    <Input id="search" type="text" placeholder="Search user..." class="pl-10 py-5 focus:flex-1 focus-visible:ring-0 focus-visible:border-input break-all border-t border-x-0 shadow-none rounded-none" />
                    <span class="absolute start-0 inset-y-0 flex items-center justify-center px-4">
                        <Search class="size-4 text-muted-foreground" />
                    </span>
                </div>
            </div>
            <div class="flex flex-col -mx-2">
                <div class="py-2 px-2 flex gap-2 items-center rounded-md hover:bg-accent cursor-pointer"
                    v-for="user in users"
                    :key="user.id"
                    @click="toggleSelectedUser(user)"
                >
                    <Avatar :user="user" />
                    <div class="flex-1 flex flex-col select-none">
                        <p class="text-sm font-medium">{{ user.name }}</p>
                        <p class="text-xs text-muted-foreground">{{ user.username }}</p>
                    </div>
                    <div class="px-2" v-if="selectedUser?.id === user.id">
                        <Check class="size-4" />
                    </div>
                </div>
            </div>

            <DialogFooter>
                <Button @click="toggleNewChat()">Continue</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
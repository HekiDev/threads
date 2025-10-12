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
import { ref, watch } from 'vue';
import { useChatStore } from '@/store/useChatStore';
import { debounce } from "@/lib/debounce";

interface User {
    id: number;
    name: string;
    username: string;
    avatar: string;
}
const dialog = defineModel('dialog', { type: Boolean, default: false });
const emits = defineEmits<{
    (e: 'createNewChat', payload: { user: User }): void
}>();

const users = ref<User[]>([]);
const search = ref<string>('');
const chatStore = useChatStore();
const selectedUser = ref<null|User>(null);

const toggleSelectedUser = (user: User) => {
    if (selectedUser.value && selectedUser.value.id === user.id) {
        selectedUser.value = null;
    } else {
        selectedUser.value = user;
    }
}

const toggleNewChat = () => {
    if (! selectedUser.value) return

    emits('createNewChat', {
        user: selectedUser.value,
    })
}

const handleSearchUsers = debounce(() => {
    selectedUser.value = null
    chatStore.handleSearchUsers(search.value)
    .then((data: any) => {
        users.value = data
    })
    .catch(error => {})
})

watch(() => dialog.value, (value) => {
    if (value) {
        search.value = ''
        users.value = []
        selectedUser.value = null
        handleSearchUsers()
    }
})
</script>

<template>
    <Dialog v-model:open="dialog">
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
                    <Input class="pl-10 py-5 focus:flex-1 focus-visible:ring-0 focus-visible:border-input break-all border-t border-x-0 shadow-none rounded-none"
                        id="search"
                        type="text"
                        v-model="search"
                        placeholder="Search user..."
                        @keyup="handleSearchUsers()"
                    />
                    <span class="absolute start-0 inset-y-0 flex items-center justify-center px-4">
                        <Search class="size-4 text-muted-foreground" />
                    </span>
                </div>
            </div>
            <div class="flex flex-col -mx-2" v-if="users.length">
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
            <div class="text-center text-muted-foreground text-sm" v-else>No user(s) found</div>

            <DialogFooter>
                <Button :disabled="!selectedUser" @click="toggleNewChat()">Continue</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
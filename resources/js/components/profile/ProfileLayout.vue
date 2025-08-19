<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

import DeleteUser from '@/components/DeleteUser.vue';
import EditUser from '@/components/EditUser.vue';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'
import { Trash, Pencil } from 'lucide-vue-next';

interface User {
    id: number | string;
    name: string;
    username: string;
    avatar?: string;
    follower_count: number;
}

const { activeTab, user } = defineProps<{
    activeTab: string;
    user?: User;
}>();

const tabs = ref([
    { label: 'Threads', value: 'threads', to: route('profile.edit') },
    { label: 'Replies', value: 'replies', to: route('profile.replies') },
    { label: 'Media', value: 'media', to: route('profile.media') },
    { label: 'Shares', value: 'shares', to: route('profile.shares') },
]);
const tab = ref(activeTab);
</script>

<template>
    <div class="flex justify-between items-center">
        <div class="flex flex-col">
            <h1 class="text-lg font-semibold">Tor Tor</h1>
            <p class="text-sm text-muted-foreground">@tortor</p>
            <p class="text-sm text-muted-foreground">0 followers</p>
        </div>
        <Avatar class="size-16">
            <AvatarImage src="https://github.com/unovue.png" alt="@unovue" />
            <AvatarFallback>CN</AvatarFallback>
        </Avatar>
    </div>
    <div class="flex gap-3 w-full py-2">
        <div class="flex-1">
            <EditUser>
                <template v-slot:editDialogTrigger>
                    <Button variant="outline" class="cursor-pointer w-full">
                        <Pencil class="size-4"/> Edit profile
                    </Button>
                </template>
            </EditUser>
        </div>
        <div class="flex-1">
            <DeleteUser>
                <template v-slot:deleteDialogTrigger>
                    <Button variant="destructive" class="cursor-pointer w-full">
                        <Trash class="size-4"/> Delete account
                    </Button>
                </template>
            </DeleteUser>
        </div>
    </div>
    <Tabs v-model="tab" class="w-full">
        <TabsList class="w-full
            bg-transparent
            border-b
            border-b-accent
            p-0 px-0 py-0 h-auto rounded-none"
        >
            <Link v-for="t, i in tabs" :key="i" :href="t.to" class="w-full">
                <TabsTrigger :value="t.value" class="relative cursor-pointer w-full
                    border-none
                    py-2
                    rounded-none
                    text-muted-foreground
                    dark:data-[state=active]:border-none
                    data-[state=active]:shadow-none
                    data-[state=active]:text-foreground"
                >
                    {{ t.label }}
                    <span
                        :class="[
                            'absolute left-1/2 bottom-0 h-[2px] transition-all transform -translate-x-1/2',
                            tab === t.value ? 'w-full bg-foreground' : 'w-0 bg-transparent',
                        ]"
                    />
                </TabsTrigger>
            </Link>
        </TabsList>
        <TabsContent :value="tab">
            <slot />
        </TabsContent>
    </Tabs>
</template>

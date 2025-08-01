<script setup lang="ts">
import { ref } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { type ThreadList } from '@/types/thread';
import { Head, router } from '@inertiajs/vue3';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'
import Heading from '@/components/Heading.vue';
import HomeLayout from '@/components/home/HomeLayout.vue';
import Post from '@/components/home/Post.vue';
import AppLayout from '@/layouts/AppLayout.vue';

import { useCreateThreadStore } from '@/store/useCreateThreadStore';
const threadStore = useCreateThreadStore();

const { threads } = defineProps<{
    threads: ThreadList;
}>();
const index = ref(2);
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const showThread = (username: string, uuid: string|number) => {
    router.get(route('threads.show', {
        username: username,
        uuid: uuid,
    }), {}, { preserveScroll: false })
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6">
            <HomeLayout>
                <template v-slot:header>
                    <Heading title="Threads" description="Share your thoughts with others" />
                </template>
                <template v-slot:content>
                    <div class="w-full border rounded-lg bg-accent/20 cursor-pointer">
                        <div class="flex gap-3 px-5 py-6 items-center">
                            <Avatar>
                                <AvatarImage src="https://github.com/unovue.png" alt="@unovue" />
                                <AvatarFallback>CN</AvatarFallback>
                            </Avatar>
                            <div class="text-muted-foreground font-medium text-sm flex-1" @click="threadStore.openThreadDialog()">What's new?</div>
                            <Button @click="threadStore.openThreadDialog()" class="cursor-pointer">Post</Button>
                        </div>
                        <Post
                            className="not-first:border-t not-last:border-b-0"
                            v-for="thread in threads.data" :key="thread.uuid"
                            :post="thread"
                            @click="showThread(thread.user.username, thread.uuid)"
                        />
                    </div>
                </template>
            </HomeLayout>
        </div>
    </AppLayout>
</template>

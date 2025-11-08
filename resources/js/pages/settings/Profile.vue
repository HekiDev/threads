<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import ProfileLayout from '@/components/profile/ProfileLayout.vue';
import { type BreadcrumbItem, type UserProfile } from '@/types';
import { type ThreadList } from '@/types/thread';
import Post from '@/components/home/Post.vue';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
    tab: string;
    user: UserProfile;
    threads: ThreadList
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const activeTab = ref(props.tab);
const user = usePage().props.auth.user;

const showThread = (username: string, uuid: string|number) => {
    router.get(route('threads.show', {
        username: username,
        uuid: uuid,
    }), {}, { preserveScroll: false })
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <SettingsLayout>
            <ProfileLayout :activeTab="activeTab" :user="props.user">
                <div class="w-full border rounded-lg bg-accent/20 cursor-pointer"
                    v-if="props.threads.data.length"
                >
                    <Post
                        :user
                        className="not-first:border-t not-last:border-b-0"
                        v-for="(thread, index) in props.threads.data" :key="thread.uuid"
                        :index
                        :post="thread"
                        @click="showThread(thread.user.username, thread.uuid)"
                    />
                </div>
                <div v-else class="px-5 py-4 bg-accent/20 rounded-lg border text-muted-foreground text-sm text-center">
                    Not threads yet.
                </div>
            </ProfileLayout>
        </SettingsLayout>
    </AppLayout>
</template>

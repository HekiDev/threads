<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import ProfileLayout from '@/components/profile/ProfileLayout.vue';
import { type BreadcrumbItem, type UserProfile } from '@/types';

interface Props {
    tab: string;
    user: UserProfile;
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const activeTab = ref(props.tab);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <SettingsLayout>
            <ProfileLayout :activeTab="activeTab" :user="props.user">
                <div class="px-5 py-4 bg-accent/20 rounded-lg border text-muted-foreground text-sm text-center">
                    Not threads yet.
                </div>
            </ProfileLayout>
        </SettingsLayout>
    </AppLayout>
</template>

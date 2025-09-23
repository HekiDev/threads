<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
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
                Replies
            </ProfileLayout>
        </SettingsLayout>
    </AppLayout>
</template>

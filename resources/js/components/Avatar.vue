<script setup lang="ts">
import { getInitials } from '@/composables/useInitials';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'

interface User {
    name: string;
    avatar?: string | null;
}
const { user, className = '' } = defineProps<{
    user: User;
    className?: string;
}>();
</script>
<template>
    <Avatar :class="className">
        <AvatarImage class="object-cover" v-if="user.avatar" :src="user.avatar" :alt="user.name" />
        <AvatarFallback class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white">
            {{ getInitials(user.name) }}
        </AvatarFallback>
    </Avatar>
</template>
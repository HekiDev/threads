<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { EllipsisVertical } from 'lucide-vue-next'
import { type ChatUser } from '@/types/chat';
import Avatar from '../Avatar.vue';

const { chat_id = null, user } = defineProps<{
    chat_id: number | null,
    user: ChatUser,
}>();
</script>

<template>
    <div class="flex w-full items-center">
        <div class="flex flex-row flex-1 gap-2 items-center">
            <Avatar :user="user" />
            <div class="">
                <p class="font-medium">{{ user.name }}</p>
                <p class="text-xs text-muted-foreground">{{ user?.status ? user.status : '-' }}</p>
            </div>
        </div>
        <Button variant="outline" v-if="chat_id">
            <EllipsisVertical />
        </Button>
    </div>
</template>
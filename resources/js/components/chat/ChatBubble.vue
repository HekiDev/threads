<script setup lang="ts">
import { CheckCheck } from 'lucide-vue-next'

const { isMine, message, timestamp, status } = defineProps<{
    isMine: boolean;
    message: string;
    timestamp: string;
    status: 'sent' | 'read';
}>();
</script>

<template>
    <div
        class="flex py-2"
        :class="isMine ? 'justify-end' : 'justify-start'"
    >
        <div class="flex flex-col w-max max-w-[75%] gap-1">
            <!-- Message -->
            <div
                class="rounded-lg px-3 py-2 text-sm"
                :class="isMine
                    ? 'bg-primary text-primary-foreground rounded-br-none'
                    : 'bg-muted text-foreground rounded-bl-none'
                "
            >
                {{ message }}
            </div>

            <!-- Timestamp -->
            <div
                class="text-[11px] text-muted-foreground select-none flex"
                :class="isMine ? 'self-end' : 'self-start'"
            >
                {{ timestamp }} <span class="ml-1" v-if="isMine"><CheckCheck class="size-4" :class="{'text-green-500' : status === 'read'}" /></span>
            </div>
        </div>
    </div>
</template>
<script setup lang="ts">
import { Textarea } from '@/components/ui/textarea'
import { Plus, Images, Send } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'

</script>

<template>
    <div class="relative items-end w-full">
        <Textarea
            name="reply"
            placeholder="Enter message.."
            class="flex-1 min-h-1 pb-12 resize-none overflow-hidden focus-visible:ring-0 focus-visible:border-input break-all"
        />
        <div class="absolute bottom-2 w-full flex justify-between px-2">
            <div class="">
                <Button class="cursor-pointer" variant="ghost" size="icon" @click="[]">
                    <Plus />
                </Button>
                <Button class="cursor-pointer" variant="ghost" size="icon" @click="[]">
                    <Images />
                </Button>
            </div>
            <Button
                class="cursor-pointer"
                size="icon"
                @click.stop="[]"
            >
                <Send />
            </Button>
        </div>
    </div>
</template>
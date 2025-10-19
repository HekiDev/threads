<script setup lang="ts">
import { Textarea } from '@/components/ui/textarea'
import { Plus, Images, Send } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { nextTick, ref } from 'vue';

const { disabled = false } = defineProps<{
    disabled: boolean;
}>();
const model = defineModel<string>('message');
const emits = defineEmits<{
    (e: 'sendMessage'): void
}>();

const textareaRef = ref<HTMLTextAreaElement  | null>(null)

const focusChatInput = async () => {
    nextTick(() => {
        const textarea = (textareaRef.value as any)?.$el as HTMLTextAreaElement | null
        textarea?.focus()
    })
}

defineExpose({ focusChatInput })
</script>

<template>
    <div class="relative items-end w-full">
        <Textarea
            ref="textareaRef"
            v-model="model"
            :disabled="disabled"
            name="reply"
            placeholder="Enter message.."
            class="flex-1 min-h-1 pb-12 resize-none overflow-hidden focus-visible:ring-0 focus-visible:border-input break-all"
            @keydown.enter.exact.prevent="emits('sendMessage')"
            @keydown.shift.enter.stop
        />
        <div class="absolute bottom-2 w-full flex justify-between px-2">
            <div class="">
                <Button class="cursor-pointer" variant="ghost" size="icon" :disabled="disabled" @click.stop="[]">
                    <Plus />
                </Button>
                <Button class="cursor-pointer" variant="ghost" size="icon" :disabled="disabled" @click.stop="[]">
                    <Images />
                </Button>
            </div>
            <Button
                class="cursor-pointer"
                size="icon"
                type="submit"
                :disabled="disabled"
                @click.stop="emits('sendMessage')"
            >
                <Send />
            </Button>
        </div>
    </div>
</template>
<script setup lang="ts">
import { ref } from 'vue';
import { type ThreadFilter } from '@/types/thread';
import { Collapsible, CollapsibleContent } from '@/components/ui/collapsible'
import { Switch } from '@/components/ui/switch'
import { Button } from '@/components/ui/button'

const { filters } = defineProps<{
    filters: ThreadFilter;
}>();

const openPreference = defineModel<boolean>('openPreference');

const threadSettings = ref<any>({
    followersOnly: {
        id: 'followers-only',
        label: 'By followers',
        description: 'Show only threads from people that follows you.',
        value: filters.by_followers,
    },
    followingOnly: {
        id: 'following-only',
        label: 'By following',
        description: 'Show only threads from people you follow.',
        value: filters.by_following,
    },
});

const emits = defineEmits<{
    (e: 'update:threads', threadSettings: any): void;
}>();
</script>

<template>
    <Collapsible v-model:open="openPreference">
        <CollapsibleContent class="flex flex-col cursor-auto border-t p-5 gap-3" @click.stop>
            <div class="flex flex-col">
                <h1 class="text-sm font-medium textmute">Thread preferences</h1>
                <p class="text-xs text-muted-foreground">Choose what you want to see in your threads.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                <div class="flex items-center border p-3 rounded-md" v-for="setting, i in Object.keys(threadSettings)" :key="i">
                    <label class="flex flex-1 flex-col cursor-pointer" :for="threadSettings[setting].id">
                        <p class="text-sm font-medium">{{ threadSettings[setting].label }}</p>
                        <p class="text-xs text-muted-foreground">{{ threadSettings[setting].description }}</p>
                    </label>
                    <Switch class="cursor-pointer" :id="threadSettings[setting].id" v-model="threadSettings[setting].value" />
                </div>
            </div>
            <div class="flex gap-2">
                <Button variant="secondary" class="cursor-pointer" @click.stop="openPreference = false">
                    Close
                </Button>
                <Button variant="outline" class="cursor-pointer" @click.stop="emits('update:threads', threadSettings)">
                    Apply
                </Button>
            </div>
        </CollapsibleContent>
    </Collapsible>
</template>

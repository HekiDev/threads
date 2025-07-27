<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/layouts/app/AppHeaderLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { Input } from '@/components/ui/input';
import { ChevronRight, Search, X, Images, Smile } from 'lucide-vue-next';
import { useCreateThreadStore } from '@/store/useCreateThreadStore';

const threadStore = useCreateThreadStore();

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const thread = ref({
    description: '',
    topic: null,
    topic_id: null,
})

const handleSubmitThread = () => {
    console.log(thread.value)
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
    <Dialog v-model:open="threadStore.dialog">
        <DialogContent>
            <DialogHeader class="space-y-3">
                <DialogTitle class="text-center">New thread</DialogTitle>
            </DialogHeader>
            <DialogDescription></DialogDescription>
            <div class="flex flex-col w-full gap-3">
                <div class="flex gap-2 items-center">
                    <div class="">
                        <Avatar>
                            <AvatarImage src="https://github.com/unovue.png" alt="@unovue" />
                            <AvatarFallback>CN</AvatarFallback>
                        </Avatar>
                    </div>
                    <div class="flex flex-1 flex-col gap-2">
                        <div class="flex gap-2 items-center">
                            <p class="font-semibold">Tor</p>
                            <ChevronRight class="size-4 text-muted-foreground" />
                            <Select v-model="thread.topic">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select a topic" />
                                </SelectTrigger>
                                <SelectContent>
                                    <div class="relative w-full max-w-sm items-center m-1">
                                        <Input class="pl-9 focus-visible:ring-0 border-0 rounded-none h-10" placeholder="Select topic..." />
                                        <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                            <Search class="size-4 text-muted-foreground" />
                                        </span>
                                    </div>
                                    <SelectGroup>
                                        <SelectItem value="apple">Apple</SelectItem>
                                        <SelectItem value="green">Green</SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <Button variant="ghost" class="cursor-pointer text-muted-foreground rounded-full"
                                @click="thread.topic = null"
                                v-if="thread.topic"
                            >
                                <X />
                            </Button>
                        </div>
                    </div>
                </div>
                <Textarea v-model="thread.description" placeholder="What's new?" />
                <div>
                    <Button variant="ghost" class="cursor-pointer text-muted-foreground rounded-full">
                        <Images />
                    </Button>
                    <Button variant="ghost" class="cursor-pointer text-muted-foreground rounded-full">
                        <Smile />
                    </Button>
                </div>
            </div>
            <DialogFooter class="gap-2">
                <DialogClose as-child>
                    <Button variant="secondary">Cancel </Button>
                </DialogClose>
                <Button type="submit" @click="handleSubmitThread()"> Post thread </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

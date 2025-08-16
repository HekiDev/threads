<script setup lang="ts">
import { ref, watch, useTemplateRef } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/app/AppHeaderLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import type { CarouselApi } from "@/components/ui/carousel"
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
import { Carousel, CarouselContent, CarouselItem } from '@/components/ui/carousel'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip"
import InputError from '@/components/InputError.vue';
import { Textarea } from '@/components/ui/textarea'
import { Input } from '@/components/ui/input';
import { Toaster } from '@/components/ui/sonner'
import { ChevronRight, Search, X, Images, Smile, Plus, LoaderCircle, CircleAlert } from 'lucide-vue-next';
import { useCreateThreadStore } from '@/store/useCreateThreadStore';
import { debounce } from "@/lib/debounce";
import { toast } from 'vue-sonner'

const threadStore = useCreateThreadStore();

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const thread = ref({
    description: '',
    topic: '',
    attachments: [] as File[],
})
const isSubmitting = ref<boolean>(false);
const topic = ref<string>('');
const errors = ref<any>([]);
const filePreviews = ref<any>([]);
const fileInput = useTemplateRef<HTMLInputElement>('fileInput');
const carousel = ref<CarouselApi>()

const handleSearchTopic = debounce(() => {
    threadStore.handleSearchTopic(topic.value)
});

const handleSubmitThread = () => {
    isSubmitting.value = true
    threadStore.handleSubmitThread(thread.value)
    .then((data: any) => {
        filePreviews.value = []
        thread.value = {
            description: '',
            topic: '',
            attachments: [],
        }
        toast.success(data.message)
        threadStore.closeThreadDialog()
        router.reload()
    })
    .catch(error => {
        errors.value = error.errors
        for (const key in filePreviews.value) {
            if (errors.value[`attachments.${key}`]) {
                scrollToCarouselItem(Number(key))
            }
        }
    })
    .finally(() => {
        isSubmitting.value = false
    })
}

const setNewTopic = () => {
    threadStore.topics.push({ id: 0, name: topic.value })
    thread.value.topic = topic.value
    topic.value = ''
}

const removeAttachment = (index: number) => {
    filePreviews.value.splice(index, 1)
    thread.value.attachments.splice(index, 1)
}

const prepareAttachments = (event: Event) => {
    const target = event.target as HTMLInputElement
    if (target.files) {
        thread.value.attachments.push(...target.files)
        filePreviews.value.forEach((url: any) => URL.revokeObjectURL(url))
        filePreviews.value = thread.value.attachments.map((file: any) => URL.createObjectURL(file))
    }
}

const onCarouselApiReady = (emblaApi: CarouselApi) => {
    carousel.value = emblaApi
}

const scrollToCarouselItem = (key: number) => {
    carousel.value?.scrollTo(key)
}

watch(() => threadStore.dialog, (value) => {
    if (value) handleSearchTopic()
})
</script>

<template>
    <Toaster position="top-right"/>
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
                                    <SelectValue placeholder="Select a topic" class="max-w-[150px]" />
                                </SelectTrigger>
                                <SelectContent class="max-w-sm">
                                    <div class="relative items-center mb-1">
                                        <Input
                                            v-model="topic"
                                            class="pl-9 focus-visible:ring-0 border-0 rounded-none h-10"
                                            placeholder="Search topic..."
                                            @keyup="handleSearchTopic()"
                                            @keydown.stop
                                        />
                                        <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                                            <Search class="size-4 text-muted-foreground" />
                                        </span>
                                    </div>
                                    <SelectGroup v-if="threadStore.topics.length > 0">
                                        <SelectItem
                                            class="break-all"
                                            v-for="item in threadStore.topics"
                                            :value="item.name"
                                            :key="item.id"
                                        >
                                            {{ item.name }}
                                        </SelectItem>
                                    </SelectGroup>
                                    <div v-else class="hover:bg-accent cursor-pointer flex flex-col w-full break-all rounded-md px-2" @click="setNewTopic()">
                                        <div class="flex flex-col py-1">
                                            <p class="break-all text-sm">{{ topic }} </p>
                                            <div class="flex text-muted-foreground items-center gap-1">
                                                <Plus class="size-3.5"/>
                                                <p class="text-sm">Tag new topic</p>
                                            </div>
                                        </div>
                                    </div>
                                </SelectContent>
                            </Select>
                            <Button variant="ghost" class="cursor-pointer text-muted-foreground rounded-full"
                                @click="thread.topic = ''; topic = ''; handleSearchTopic()"
                                v-if="thread.topic"
                            >
                                <X />
                            </Button>
                        </div>
                    </div>
                </div>
                <div class="grid gap-1">
                    <Textarea v-model="thread.description" placeholder="What's new?"
                        :class="errors.description ? 'border-destructive focus-visible:ring-destructive/40 ring-destructive/20 dark:ring-destructive/40 focus-visible:border-destructive' : ''"
                    />
                    <InputError :message="errors.description?.[0] ?? ''" />
                </div>
                <input type="file" class="hidden" accept="image/*" multiple ref="fileInput" @change="prepareAttachments"/>
                <div v-if="thread.attachments.length > 0" class="flex flex-col gap-2">
                    <Carousel
                        @init-api="onCarouselApiReady"
                        class="relative w-full"
                        :opts="{
                            align: 'start',
                        }"
                    >
                        <CarouselContent class="lg:w-[150px]">
                            <CarouselItem v-for="item, i in filePreviews" :key="i" class="lg:w-[150px]">
                                <div class="relative flex overflow-hidden rounded-md self-start">
                                    <Button size="icon" class="size-7 cursor-pointer rounded-full absolute top-1 right-1"
                                        @click="removeAttachment(i)"
                                    >
                                        <X />
                                    </Button>
                                    <TooltipProvider :delay-duration="0" v-if="errors[`attachments.${i}`]">
                                        <Tooltip>
                                            <TooltipTrigger>
                                                <Button size="icon" variant="destructive" class="size-7 cursor-pointer rounded-full absolute top-1 left-1">
                                                    <CircleAlert />
                                                </Button>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                {{ errors[`attachments.${i}`][0] }}
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                    <img
                                        lazy
                                        :src="item"
                                        alt="image"
                                        width="250"
                                        height="300"
                                        class="h-auto w-auto object-cover transition-all aspect-[3/4]"
                                    />
                                </div>
                            </CarouselItem>
                        </CarouselContent>
                    </Carousel>
                    <InputError v-if="errors.attachments" :message="errors.attachments[0]" />
                </div>
                <div>
                    <Button variant="ghost" class="cursor-pointer text-muted-foreground rounded-full" @click="fileInput?.click()">
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
                <Button type="submit" :disabled="isSubmitting" @click="handleSubmitThread()">
                    <LoaderCircle v-if="isSubmitting" class="h-4 w-4 animate-spin" />
                    Post thread
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

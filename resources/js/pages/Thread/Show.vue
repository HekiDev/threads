<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button'
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import Heading from '@/components/Heading.vue';
import HomeLayout from '@/components/home/HomeLayout.vue';
import Post from '@/components/home/Post.vue';
import { ChevronRight } from 'lucide-vue-next';
import { type SingleThread, type ThreadList } from '@/types/thread';

const { post, comments } = defineProps<{
    post: SingleThread;
    comments: ThreadList;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const sorting = ref('top');
const sortingOptions = ref([
    {
        value: 'top',
        label: 'Top',
    },
    {
        value: 'recent',
        label: 'Recent',
    },
])
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6">
            <HomeLayout>
                <template v-slot:header>
                    <Heading title="Thread" description="24.2k views" />
                </template>
                <template v-slot:content>
                    <Post
                        :post="post.data"
                        className="border rounded-lg"
                    />
                    <div class="flex flex-col gap-3 py-4">
                        <div class="flex flex-wrap gap-2 justify-between items-center">
                            <Select v-model="sorting">
                                <SelectTrigger>
                                    <SelectValue placeholder="Sort By" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem v-for="option in sortingOptions" :value="option.value" :key="option.value">
                                            {{ option.label }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <Button variant="outline">
                                View activity <ChevronRight class="size-4 text-muted-foreground" />
                            </Button>
                        </div>
                        <div>
                            <Post
                                v-for="comment in comments.data" :key="comment.uuid"
                                :post="comment"
                                className="border not-first:border-t not-last:border-b-0 first:rounded-t-lg last:rounded-b-lg"
                            />
                        </div>
                    </div>
                </template>
            </HomeLayout>
        </div>
    </AppLayout>
</template>

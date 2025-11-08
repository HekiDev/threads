<script setup lang="ts">
import Avatar from '@/components/Avatar.vue'
import { Button } from '@/components/ui/button'
import { Heart, Ellipsis, AtSign, UsersRound, UserCheck } from 'lucide-vue-next';

import { usePage } from '@inertiajs/vue3';

const pageProps = <any>usePage().props;
const totalThreadCount = pageProps?.totalThreads ?? '0';
const totalReactionCount = pageProps?.totalReactions ?? '0';
const following = pageProps?.following?.data ?? [];
const followers = pageProps?.followers?.data ?? [];
</script>

<template>
    <slot name="header" />
    <div class="flex flex-col gap-4 lg:flex-row">
        <section class="w-full lg:w-1/4">
            <nav class="grid grid-cols-2 lg:grid-cols-1 gap-4">
                <div class="p-4 bg-accent/20 border rounded-lg">
                    <div class="flex justify-between items-center">
                        <h1 class="font-semibold text-sm">Threads</h1>
                        <AtSign class="size-4" />
                    </div>
                    <p class="text-muted-foreground text-sm">{{ totalThreadCount }}</p>
                </div>
                <div class="p-4 bg-accent/20 border rounded-lg">
                    <div class="flex justify-between items-center">
                        <h1 class="font-semibold text-sm">Hearts</h1>
                        <Heart class="size-4" />
                    </div>
                    <p class="text-muted-foreground text-sm">{{ totalReactionCount }}</p>
                </div>
            </nav>
        </section>

        <section class="w-full lg:w-3/4">
            <slot name="content" />
        </section>

        <section class="w-full lg:w-1/4 flex flex-col gap-4">
            <div class="flex flex-col p-4 bg-accent/20 border rounded-lg gap-4">
                <div class="flex justify-between items-center">
                    <h1 class="font-semibold text-sm">Followers</h1>
                    <UsersRound class="size-4" />
                </div>
                <div class="flex gap-2 text-sm"
                    v-if="followers.length > 0"
                    v-for="user in followers" :key="user.id"
                >
                    <Avatar :user="user" class="size-5" />
                    <div class="flex-1">
                        <p>{{ user.name }}</p>
                        <p class="text-xs text-muted-foreground truncate text-ellipsis">{{ user.username }}</p>
                    </div>
                    <span>
                        <Button variant="ghost" size="icon" class="cursor-pointer text-muted-foreground">
                            <Ellipsis />
                        </Button>
                    </span>
                </div>
                <div v-else class="flex flex-wrap gap-2">
                    <p class="w-full text-sm text-muted-foreground text-center">You don't have followers yet</p>
                </div>
            </div>
            <div class="flex flex-col p-4 bg-accent/20 border rounded-lg gap-4">
                <div class="flex justify-between items-center">
                    <h1 class="font-semibold text-sm">Following</h1>
                    <UserCheck class="size-4" />
                </div>
                <div class="flex gap-2 text-sm"
                    v-if="following.length > 0"
                    v-for="user in following" :key="user.id"
                >
                    <Avatar :user="user" class="size-5" />
                    <div class="flex-1">
                        <p>{{ user.name }}</p>
                        <p class="text-xs text-muted-foreground truncate text-ellipsis">{{ user.username }}</p>
                    </div>
                    <span>
                        <Button variant="ghost" size="icon" class="cursor-pointer text-muted-foreground">
                            <Ellipsis />
                        </Button>
                    </span>
                </div>
                <div v-else class="flex flex-wrap gap-2">
                    <p class="w-full text-sm text-muted-foreground text-center">You don't follow anyone yet</p>
                </div>
            </div>
        </section>
    </div>
</template>

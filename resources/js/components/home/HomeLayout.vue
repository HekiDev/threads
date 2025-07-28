<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'
import { Separator } from '@/components/ui/separator';
import { Heart, Ellipsis, AtSign, UsersRound, UserCheck } from 'lucide-vue-next';

import { useCreateThreadStore } from '@/store/useCreateThreadStore';
const threadStore = useCreateThreadStore();
</script>

<template>
    <slot name="header" />
    <div class="flex flex-col space-y-8 md:space-y-4 lg:flex-row lg:space-y-0 lg:space-x-4">
        <section class="w-full lg:w-1/4">
            <nav class="flex flex-col gap-4">
                <div class="p-4 bg-accent/20 border rounded-lg shadow">
                    <div class="flex justify-between items-center">
                        <h1 class="font-semibold text-sm">Threads</h1>
                        <AtSign class="size-4" />
                    </div>
                    <p class="text-muted-foreground">100+</p>
                </div>
                <div class="p-4 bg-accent/20 border rounded-lg shadow">
                    <div class="flex justify-between items-center">
                        <h1 class="font-semibold text-sm">Hearts</h1>
                        <Heart class="size-4" />
                    </div>
                    <p class="text-muted-foreground">12k</p>
                </div>
            </nav>
        </section>

        <Separator class="md:hidden" />
        <section class="w-full lg:w-3/4">
            <slot name="content" />
        </section>

        <section class="w-full lg:w-1/4 flex flex-col gap-4">
            <div class="flex flex-col p-4 bg-accent/20 border rounded-lg shadow gap-4">
                <div class="flex justify-between items-center">
                    <h1 class="font-semibold text-sm">Followers</h1>
                    <UsersRound class="size-4" />
                </div>
                <div class="flex flex-wrap gap-2 text-sm" v-for="i in 10" :key="i">
                    <Avatar class="h-5 w-5">
                        <AvatarImage src="https://github.com/unovue.png" alt="@unovue" />
                        <AvatarFallback>CN</AvatarFallback>
                    </Avatar>
                    <div class="flex-1">
                        <p>John Doe</p>
                        <p class="text-xs text-muted-foreground">@johndoe</p>
                    </div>
                    <span>
                        <Button variant="ghost" size="icon" class="cursor-pointer text-muted-foreground">
                            <Ellipsis />
                        </Button>
                    </span>
                </div>
            </div>
            <div class="flex flex-col p-4 bg-accent/20 border rounded-lg shadow gap-4">
                <div class="flex justify-between items-center">
                    <h1 class="font-semibold text-sm">Following</h1>
                    <UserCheck class="size-4" />
                </div>
                <div class="flex flex-wrap gap-2">
                    <p class="w-full text-xs text-muted-foreground text-center">You don't follow anyone yet</p>
                </div>
            </div>
        </section>
    </div>
</template>

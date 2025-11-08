<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Link, usePage } from '@inertiajs/vue3';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import type { NavItem } from '@/types';

import { House, Plus, MessageCircle, UserRound, Heart } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useCreateThreadStore } from '@/store/useCreateThreadStore';
const threadStore = useCreateThreadStore();

const mainNavItems: NavItem[] = [
    {
        title: 'Home',
        href: '/',
        icon: House,
    },
    {
        title: 'Messages',
        href: '/chats',
        icon: MessageCircle,
    },
    {
        title: 'Add',
        href: '',
        icon: MessageCircle,
    },
    {
        title: 'Activity',
        href: '/activity',
        icon: Heart,
    },
    {
        title: 'Profile',
        href: '/settings/profile',
        icon: UserRound,
    },
];

const page = usePage();

const isCurrentRoute = computed(() => (url: string) => {
    return page.url === url
});

const activeItemStyles = computed(
    () => (url: string) => (isCurrentRoute.value(url) ? 'text-neutral-900 bg-neutral-100 dark:bg-neutral-800 dark:text-neutral-100' : ''), 
);

const showBottomNav = ref(true)
let lastScrollY = 0

const handleScroll = () => {
    const currentScrollY = window.scrollY
    if (currentScrollY > lastScrollY && currentScrollY > 50) {
        showBottomNav.value = false
    } else {
        showBottomNav.value = true
    }
    lastScrollY = currentScrollY
}

onMounted(() => {
    window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll)
})
</script>

<template>
    <transition name="slide-up">
        <div class="fixed bottom-0 left-0 right-0 flex p-2 border-t bg-accent lg:hidden" v-show="showBottomNav">
            <div class="flex-1 flex items-center justify-center bg-accent"
                v-for="(item, index) in mainNavItems" :key="index"
            >
                <TooltipProvider v-if="item.title === 'Add'" :delay-duration="0">
                    <Tooltip>
                        <TooltipTrigger>
                            <Button variant="secondary" size="icon" class="h-9 cursor-pointer"
                                @click="threadStore.openThreadDialog()"
                            >
                                <Plus />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>Create a new thread</p>
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider>
                <Link
                    v-else
                    class="transition-all flex items-center justify-center rounded-md w-full"
                    :class="[activeItemStyles(item.href), 'h-9 cursor-pointer']"
                    :href="item.href"
                >
                    <component
                        v-if="item.icon"
                        :is="item.icon"
                        class="h-4 w-4"
                        :stroke="activeItemStyles(item.href) ? 'foreground' : 'currentColor'"
                        :fill="activeItemStyles(item.href) ? 'foreground' : 'none'"
                    />
                </Link>
            </div>
        </div>
    </transition>
</template>

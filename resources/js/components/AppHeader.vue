<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { NavigationMenu, NavigationMenuItem, NavigationMenuList, navigationMenuTriggerStyle } from '@/components/ui/navigation-menu';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Input } from '@/components/ui/input'
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import { useAppearance } from '@/composables/useAppearance';
import type { BreadcrumbItem, NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, Search, House, Plus, Mail, Heart, UserRound, Moon, Sun } from 'lucide-vue-next';
import { computed, onMounted } from 'vue';
import { useCreateThreadStore } from '@/store/useCreateThreadStore';

const threadStore = useCreateThreadStore();
const { appearance, updateAppearance } = useAppearance();

interface Props {
    breadcrumbs?: BreadcrumbItem[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const auth = computed(() => page.props.auth);

const isCurrentRoute = computed(() => (url: string) => {
    return page.url === url
});

const activeItemStyles = computed(
    () => (url: string) => (isCurrentRoute.value(url) ? 'text-neutral-900 bg-neutral-100 dark:bg-neutral-800 dark:text-neutral-100' : ''), 
);

const mainNavItems: NavItem[] = [
    {
        title: 'Home',
        href: '/',
        icon: House,
    },
    {
        title: 'Messages',
        href: '/messages',
        icon: Mail,
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

onMounted(() => {
    console.log(isCurrentRoute.value('/'));
    
});
</script>

<template>
    <div>
        <div class="border-b border-sidebar-border/80">
            <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">

                <Link :href="route('dashboard')" class="flex items-center gap-x-2">
                    <AppLogo />
                </Link>

                <!-- Desktop Menu -->
                <div class="hidden h-full lg:flex lg:flex-1">
                    <NavigationMenu class="ml-10 flex h-full items-stretch">
                        <NavigationMenuList class="flex h-full items-stretch space-x-2">
                            <NavigationMenuItem class="relative flex h-full items-center">
                                <TooltipProvider :delay-duration="0">
                                    <Tooltip>
                                        <TooltipTrigger>
                                            <Button variant="secondary" size="icon" class="mr-2 h-9 w-12 cursor-pointer"
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
                            </NavigationMenuItem>
                        </NavigationMenuList>
                        <NavigationMenuList class="flex h-full items-stretch space-x-1">
                            <NavigationMenuItem v-for="(item, index) in mainNavItems" :key="index" class="relative flex h-full items-center">
                                <Link
                                    class="transition-all"
                                    :class="[navigationMenuTriggerStyle(), activeItemStyles(item.href), 'h-9 cursor-pointer px-3']"
                                    :href="item.href"
                                >
                                    <component v-if="item.icon" :is="item.icon" class="mr-2 h-4 w-4" />
                                    {{ item.title }}
                                </Link>
                            </NavigationMenuItem>
                        </NavigationMenuList>
                    </NavigationMenu>
                </div>

                <div class="ml-auto flex items-center space-x-1">
                    <div class="relative flex items-center space-x-2">
                        <Button variant="ghost" size="icon" class="group h-9 w-9 cursor-pointer lg:hidden">
                            <Search class="size-4 opacity-80 group-hover:opacity-100" />
                        </Button>

                        <div class="hidden space-x-1 lg:flex">
                            <div class="relative w-full max-w-sm items-center">
                                <Input id="search" type="text" placeholder="Search..." class="pl-8 focus:flex-1" />
                                <span class="absolute start-0 inset-y-0 flex items-center justify-center px-2">
                                    <Search class="size-4 text-muted-foreground" />
                                </span>
                            </div>
                            <!-- <template v-for="item in rightNavItems" :key="item.title">
                                <TooltipProvider :delay-duration="0">
                                    <Tooltip>
                                        <TooltipTrigger>
                                            <Button variant="ghost" size="icon" as-child class="group h-9 w-9 cursor-pointer">
                                                <a :href="item.href" target="_blank" rel="noopener noreferrer">
                                                    <span class="sr-only">{{ item.title }}</span>
                                                    <component :is="item.icon" class="size-5 opacity-80 group-hover:opacity-100" />
                                                </a>
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <p>{{ item.title }}</p>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            </template> -->
                        </div>
                        <Button variant="ghost" size="icon" class="group h-9 w-9 cursor-pointer transition-colors"
                            @click="updateAppearance(appearance === 'light' ? 'dark' : 'light')"
                        >
                            <Moon v-if="appearance === 'light'" class="size-4 opacity-80 group-hover:opacity-100" />
                            <Sun v-else class="size-4 opacity-80 group-hover:opacity-100" />
                        </Button>
                    </div>

                    <DropdownMenu>
                        <DropdownMenuTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="relative size-10 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary"
                            >
                                <Avatar class="size-8 overflow-hidden rounded-full">
                                    <AvatarImage v-if="auth.user.avatar" :src="auth.user.avatar" :alt="auth.user.name" />
                                    <AvatarFallback class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white">
                                        {{ getInitials(auth.user?.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <UserMenuContent :user="auth.user" />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </div>

        <div v-if="props.breadcrumbs.length > 1" class="flex w-full border-b border-sidebar-border/70">
            <div class="mx-auto flex h-12 w-full items-center justify-start px-4 text-neutral-500 md:max-w-7xl">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </div>
        </div>
    </div>
</template>

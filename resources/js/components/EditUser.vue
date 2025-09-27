<script setup lang="ts">
import { useForm, usePage, Link } from '@inertiajs/vue3';

// Components
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription } from "@/components/ui/alert"
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { CircleCheck } from "lucide-vue-next"
import { type User } from '@/types';
import { ref, useTemplateRef } from 'vue';

const page = usePage();
const user = page.props.auth.user as User;
const form = useForm({
    name: user.name,
    email: user.email,
});
const mustVerifyEmail = page.props.mustVerifyEmail;
const status = page.props.status;
const avatarInput = useTemplateRef<HTMLInputElement>('avatarInput');
const avatarPreview = ref<any|null>(null);
const avatar = ref<null|File>(null);

const submit = () => {
    form.transform((data) => ({
        ...data,
        avatar_file: avatar.value,
    }))
    .post(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            avatar.value = null;
            avatarPreview.value = user.avatar;
        },
    });
};

const prepareAvatar = (event: Event) => {
    const target = event.target as HTMLInputElement
    if (target.files) {
        avatar.value = target.files[0]
        if (avatarPreview.value) {
            URL.revokeObjectURL(avatarPreview.value)
        }
        avatarPreview.value = URL.createObjectURL(avatar.value)
    }
}
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <slot name="editDialogTrigger" />
        </DialogTrigger>
        <DialogContent>
            <DialogHeader class="space-y-3">
                <DialogTitle>Edit profile</DialogTitle>
                <DialogDescription>
                    <Transition
                        enter-active-class="transition ease-in-out"
                        enter-from-class="opacity-0"
                        leave-active-class="transition ease-in-out"
                        leave-to-class="opacity-0"
                    >
                        <Alert v-show="form.recentlySuccessful" class="border-green-500 text-green-500">
                            <CircleCheck class="h-4 w-4" />
                            <AlertDescription class="text-green-500">
                                Information saved.
                            </AlertDescription>
                        </Alert>
                    </Transition>
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submit" class="space-y-6">
                <div class="flex flex-col gap-2 items-center justify-center">
                    <input type="file" class="hidden" accept="image/*" ref="avatarInput" @change="prepareAvatar"/>
                    <Avatar class="size-15">
                        <AvatarImage class="object-cover" v-if="avatarPreview ?? user.avatar" :src="avatarPreview ?? user.avatar" :alt="user.name" />
                        <AvatarFallback class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white">
                        </AvatarFallback>
                    </Avatar>
                    <Button variant="outline" size="sm" type="button" @click="avatarInput?.click()">
                        {{ user.avatar ? 'Change avatar' : 'Upload avatar' }}
                    </Button>
                </div>

                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input id="name" class="mt-1 block w-full" v-model="form.name" required autocomplete="name" placeholder="Full name" />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        required
                        autocomplete="username"
                        placeholder="Email address"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div v-if="mustVerifyEmail && !user.email_verified_at">
                    <p class="-mt-4 text-sm text-muted-foreground">
                        Your email address is unverified.
                        <Link
                            :href="route('verification.send')"
                            method="post"
                            as="button"
                            class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                        >
                            Click here to resend the verification email.
                        </Link>
                    </p>

                    <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                        A new verification link has been sent to your email address.
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary"> Cancel </Button>
                    </DialogClose>

                    <Button type="submit" :disabled="form.processing"> Save </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { MessageSquare } from "lucide-vue-next"
import {
    Empty,
    EmptyContent,
    EmptyDescription,
    EmptyHeader,
    EmptyMedia,
    EmptyTitle,
} from "@/components/ui/empty"

const { title = '', description = '' } = defineProps<{
    title: string,
    description: string,
}>();
</script>

<template>
    <Empty class="from-muted/50 to-background h-full bg-gradient-to-b from-30%">
        <EmptyHeader>
            <EmptyMedia variant="icon">
                <MessageSquare />
            </EmptyMedia>
            <EmptyTitle>{{ title }}</EmptyTitle>
            <EmptyDescription>
                {{ description }}
            </EmptyDescription>
        </EmptyHeader>
        <EmptyContent>
        </EmptyContent>
    </Empty>
</template>
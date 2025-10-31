<template>
    <div class="flex items-center text-gray-500">
        <span class="w-2 h-2 rounded-full bg-gray-400 animate-bounce" style="animation-delay: 0ms"></span>
        <span class="w-2 h-2 rounded-full bg-gray-400 animate-bounce" style="animation-delay: 150ms"></span>
        <span class="w-2 h-2 rounded-full bg-gray-400 animate-bounce" style="animation-delay: 300ms"></span>
    </div>
</template>

<style scoped>
@keyframes bounce {
    0%, 80%, 100% {
        transform: scale(0);
        opacity: 0.4;
    }
    40% {
        transform: scale(1);
        opacity: 1;
    }
}

.animate-bounce {
    animation: bounce 1.2s infinite ease-in-out;
}
</style>
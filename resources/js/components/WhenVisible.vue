<template>
    <div ref="observerTarget">
        <slot />
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

const emit = defineEmits(['visible'])

const observerTarget = ref(null)
let observer = null

onMounted(() => {
    observer = new IntersectionObserver(([entry]) => {
        if (entry.isIntersecting) {
            emit('visible')
        }
    }, { threshold: 0.1 }) // adjust threshold if needed

    if (observerTarget.value) {
        observer.observe(observerTarget.value)
    }
})

onBeforeUnmount(() => {
    if (observer && observerTarget.value) {
        observer.unobserve(observerTarget.value)
    }
})
</script>
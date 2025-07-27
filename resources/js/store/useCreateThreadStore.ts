import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useCreateThreadStore = defineStore('createThreadStore', () => {
    const dialog = ref<boolean>(false)

    const closeThreadDialog = () => {
        dialog.value = false
    }

    const openThreadDialog = () => {
        dialog.value = true
    }

    return { 
        dialog,
        closeThreadDialog,
        openThreadDialog
    }
})
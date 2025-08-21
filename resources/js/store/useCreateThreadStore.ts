import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

export const useCreateThreadStore = defineStore('createThreadStore', () => {
    const dialog = ref<boolean>(false)
    const topics = ref<{ id: number, name: string }[]>([])

    const closeThreadDialog = () => {
        dialog.value = false
    }

    const openThreadDialog = () => {
        dialog.value = true
    }

    const handleSearchTopic = async(keyword: string) => {
        return axios.get(route('get.topics'), {
            params: {
                search: keyword
            }
        })
        .then((response) => {
            topics.value = response.data
        })
        .catch(error => console.error(error))
    }

    const handleSubmitThread = async(form: {description: string, topic: string, attachments: File[]}) => {
        const formData = new FormData()
        formData.append('description', form.description)
        formData.append('topic', form.topic)
        // Append files
        form.attachments.forEach((file) => {
            formData.append(`attachments[]`, file)
        })

        return await new Promise((resolve, reject) => {
            axios.post(route('thread.store'), formData)
            .then(response => {
                resolve(response.data);
            })
            .catch(errs => {
                reject(errs.response.data)
            })
        })
    }

    const handleSubmitComment = async(form: {uuid: number|string, comment: string}) => {
        return await new Promise((resolve, reject) => {
            axios.post(route('thread.store.comment', form.uuid), {
                comment: form.comment,
            })
            .then(response => {
                resolve(response.data);
            })
            .catch(errs => {
                reject(errs.response.data)
            })
        })
    }

    const handleSubmitCommentReply = async({comment_id, comment}: { comment_id: number|string, comment: string }) => {
        return await new Promise((resolve, reject) => {
            axios.post(route('thread.store.comment-reply', comment_id), {
                comment: comment,
            })
            .then(response => {
                resolve(response.data);
            })
            .catch(errs => {
                reject(errs.response.data)
            })
        })
    }

    return {
        topics,
        dialog,
        closeThreadDialog,
        openThreadDialog,
        handleSearchTopic,
        handleSubmitThread,
        handleSubmitComment,
        handleSubmitCommentReply,
    }
})
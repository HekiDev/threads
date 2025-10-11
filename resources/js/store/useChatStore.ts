import { defineStore } from 'pinia'
import axios from 'axios'

export const useChatStore = defineStore('chatStore', () => {
    const handleGetChatMessages = async(chat_id: number|string) => {
        return await new Promise((resolve, reject) => {
            axios.get(route('chat.messages', chat_id))
            .then(response => {
                resolve(response.data);
            })
            .catch(errs => {
                reject(errs.response.data)
            })
        })
    }

    return {
        handleGetChatMessages,
    }
})
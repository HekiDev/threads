import { defineStore } from 'pinia'
import axios from 'axios'
import { echo } from '@laravel/echo-vue'

export const useChatStore = defineStore('chatStore', () => {
    const handleSearchUsers = async(query: string) => {
        return await new Promise((resolve, reject) => {
            axios.get(route('chat.search.users'), {
                params: {
                    search: query,
                },
            })
            .then(response => {
                resolve(response.data);
            })
            .catch(errs => {
                reject(errs.response.data)
            })
        })
    }

    const handleStoreChat = async({ message, user_id }: { message: string, user_id: number }) => {
        return await new Promise((resolve, reject) => {
            axios.post(route('chat.store'), {
                user_id: user_id,
                message: message,
            }, {
                headers: {
                    'X-Socket-Id': echo().connector.socketId(),
                },
            })
            .then(response => {
                resolve(response.data);
            })
            .catch(errs => {
                reject(errs.response.data)
            })
        })
    }

    const handleStoreChatMessage = async({ message, chat_id }: { message: string, chat_id: number }) => {
        return await new Promise((resolve, reject) => {
            axios.post(route('chat.store-message', chat_id), {
                message: message,
            }, {
                headers: {
                    'X-Socket-Id': echo().connector.socketId(),
                },
            })
            .then(response => {
                resolve(response.data);
            })
            .catch(errs => {
                reject(errs.response.data)
            })
        })
    }

    const handleLoadOlderMessages = async({ page, chat_id }: { page: number, chat_id: number }) => {
        return await new Promise((resolve, reject) => {
            axios.get(route('chat.older-messages', chat_id), {
                params: {
                    page: page,
                },
            })
            .then(response => {
                resolve(response.data);
            })
            .catch(errs => {
                reject(errs.response.data)
            })
        })
    }

    const blockUser = async({ user_id, chat_id }: { user_id: number, chat_id: number }) => {
        return await new Promise((resolve, reject) => {
            axios.post(route('chat.block-user'), {
                user_id: user_id,
                chat_id: chat_id,
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
        handleSearchUsers,
        handleStoreChat,
        handleStoreChatMessage,
        handleLoadOlderMessages,
        blockUser,
    }
})
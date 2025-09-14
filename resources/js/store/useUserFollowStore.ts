import { defineStore } from 'pinia'
import axios from 'axios'

export const useUserFollowStore = defineStore('userFollowStore', () => {
    const handleFollowUser = async(user_id: number|string) => {
        return await new Promise((resolve, reject) => {
            axios.post(route('user.follow', user_id))
            .then(response => {
                resolve(response.data);
            })
            .catch(errs => {
                reject(errs.response.data)
            })
        })
    }

    return {
        handleFollowUser,
    }
})
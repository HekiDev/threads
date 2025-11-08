import { SingleChat } from "@/types/chat"
import { nextTick } from "vue"
import { router } from '@inertiajs/vue3'

export function scrollToBottom(scrollAreaRef: any, slightly: boolean = false, offset: number = 0) {
    const el = (scrollAreaRef.value as any)?.$el || scrollAreaRef.value
    const scrollEl = el?.querySelector('[data-reka-scroll-area-viewport]') || el

    if (! scrollEl) return

    if (slightly) {
        const distanceFromBottom = scrollEl.scrollHeight - scrollEl.scrollTop - scrollEl.clientHeight

        nextTick(() => {
            const newScrollTop =
                scrollEl.scrollHeight - scrollEl.clientHeight - distanceFromBottom + offset

            scrollEl.scrollTo({
                top: newScrollTop,
                behavior: 'smooth',
            })
        })
        return;
    }

    nextTick(() => {
        scrollEl.scrollTo({
            top: scrollEl.scrollHeight,
            behavior: 'smooth',
        })
    })
}

export function updateAndResortChats(chatsArr: SingleChat[], newMessage: any): SingleChat[] {
    const index = chatsArr.findIndex(c => c.id === newMessage.chat_id)

    if (chatsArr.length === 0) {
        return [{
            id: newMessage.chat_id,
            last_message: {
                created_at: newMessage.message.created_at,
                datetime: newMessage.message.datetime,
                is_mine: newMessage.message.is_mine,
                message: newMessage.message.message,
                id: newMessage.message.id,
                status: newMessage.message.status,
                user: {
                    id: newMessage.message.user.id,
                    name: newMessage.message.user.name,
                },
            },
            members: [
                {
                    avatar: newMessage.message.user.avatar,
                    id: newMessage.message.user.id,
                    name: newMessage.message.user.name,
                    username: newMessage.message.user.username
                },
            ],
        }]
    }

    if (index === -1) return chatsArr

    if (index === 0) {
        const topChat = chatsArr[0]
        const newList = [...chatsArr]
        newList[0] = {
            ...topChat, 
            last_message: {
                ...topChat.last_message,
                message: newMessage.message.message,
                datetime: newMessage.message.datetime,
                is_mine: newMessage.message.is_mine,
                status: newMessage.message.status,
            }
        }
        return newList
    }

    const chat = chatsArr[index];
    const updatedChat = {
        ...chat,
        last_message: {
            ...chat.last_message,
            message: newMessage.message.message,
            datetime: newMessage.message.datetime,
            is_mine: newMessage.message.is_mine,
            status: newMessage.message.status,
        },
    }

    // Remove and reinsert at top
    const newList = [...chatsArr]
    newList.splice(index, 1)
    newList.unshift(updatedChat)
    return newList
}

export function shouldShowDateDivider(current: any, previous: any) {
    if (!previous) return true // always show for the first message

    const currentDate = new Date(current.created_at)
    const previousDate = new Date(previous.created_at)

    // Compare only the Y-M-D, not the time
    return (
        currentDate.getFullYear() !== previousDate.getFullYear() ||
        currentDate.getMonth() !== previousDate.getMonth() ||
        currentDate.getDate() !== previousDate.getDate()
    )
}

export function formatMessageDate(dateString: any) {
    const date = new Date(dateString)
    const today = new Date()
    const yesterday = new Date()
    yesterday.setDate(today.getDate() - 1)

    const isSameDay = (d1: any, d2: any) =>
        d1.getFullYear() === d2.getFullYear() &&
        d1.getMonth() === d2.getMonth() &&
        d1.getDate() === d2.getDate()

    if (isSameDay(date, today)) return 'Today'
    if (isSameDay(date, yesterday)) return 'Yesterday'

    // Format like "October 15, 2025"
    return new Intl.DateTimeFormat('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    }).format(date)
}

export function handleSearchChat(search: string) {
    router.reload({
        data: {
            search: search
        },
        only: ['chats'],
        preserveUrl: true,
    })
}
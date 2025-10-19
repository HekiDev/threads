import { SingleChat } from "@/types/chat"
import { nextTick } from "vue"

export function scrollToBottom(scrollAreaRef: any) {
    const el = (scrollAreaRef.value as any)?.$el || scrollAreaRef.value
    const scrollEl = el?.querySelector('[data-reka-scroll-area-viewport]') || el

    if (! scrollEl) return

    nextTick(() => {
        scrollEl.scrollTop = scrollEl.scrollHeight
    })
}

export function updateAndResortChats(chatsArr: SingleChat[], newMessage: any): SingleChat[] {
    const index = chatsArr.findIndex(c => c.id === newMessage.chat_id)
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
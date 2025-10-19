import { useEcho, useEchoPresence } from '@laravel/echo-vue';
import { ref } from 'vue';

const eventMessage = ref<any>(null);

export function toggleChatPresence(chat_id: number | null, type: 'enter' | 'leave') {
    return
    if (! chat_id) return

    const { listen, leave, leaveChannel, stopListening } = useEchoPresence(
        `chat.${chat_id ?? 0}`, '.chat-page',
        (e: any) => {
            console.log(e);
    });

    if (type === 'enter') {
        listen();
    } else {
        leave();
        leaveChannel();
        stopListening();
    }
}

export function toggleChatChannel(chat_id: number | null, type: 'enter' | 'leave') {
    const { listen, leave, leaveChannel, stopListening } = useEcho(`chat-messages.${chat_id ?? 0}`, '.chat-messages-event', (e: any) => {
        eventMessage.value = e;
    });

    if (type === 'enter') {
        listen()
    } else {
        leave()
        leaveChannel()
        stopListening()
    }
}

export function eventMessageHandler() {
    return eventMessage.value;
}
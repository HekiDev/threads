import { echo, useEcho, useEchoPresence } from '@laravel/echo-vue';
import { ref } from 'vue';

interface Message {
    chat_id: number;
    message: {
        id: number;
        message: string;
        datetime: string;
        created_at: string;
        is_mine: boolean;
        status: string;
        user: {
            id: number;
            name: string;
            avatar: string | null;
        }
    };
}

interface User {
    id: number;
}

const channelUsers = ref<User[]>([]);
const eventMessage = ref<Message|null>(null);
export const hereUsers = ref<User[]>([]);
export const joinedUser = ref<User|null>(null);
export const leavedUser = ref<User|null>(null);

export function toggleChatChannel(chat_id: number | null, type: 'enter' | 'leave') {
    const presenceChannel = useEchoPresence<Message>(
        `chat-messages.${chat_id}`,
        '.chat-messages-event',
        (e: Message) => {
            eventMessage.value = e;
        }
    )

    echo()
    .join(`chat-messages.${chat_id}`)
    .here((users: User[]) => {
        channelUsers.value = users;
        hereUsers.value = users;
    })
    .joining((user: User) => {
        channelUsers.value.push(user);
        joinedUser.value = user;
    })
    .leaving((user: User) => {
        leavedUser.value = user;
        channelUsers.value = channelUsers.value.filter((u) => u.id !== user.id);
    })

    if (type === 'enter') {
        presenceChannel.listen()

        return {
            whisper: (name: string, payload: Record<string, any>) => {
                const activeChannel = presenceChannel.channel();
                if (activeChannel) {
                    activeChannel.whisper(name, payload);
                }
            },
            listenForWhisper: (name: string, callback: (payload: any) => void) => {
                const activeChannel = presenceChannel.channel();
                if (activeChannel) {
                    activeChannel.listenForWhisper(name, (e: any) => {
                        callback(e);
                    });
                }
            },
        };
    } else {
        presenceChannel.leave()
        presenceChannel.leaveChannel()
        presenceChannel.stopListening()
        return null
    }
}

export function toggleChatPageChannel(recipient_id: number, type: 'enter' | 'leave') {
    const {
        listen,
        leave,
        leaveChannel,
        stopListening
    } = useEcho<Message>(
        `chats.${recipient_id}`,
        '.chat-messages-event',
        (e: Message) => {
            eventMessage.value = e;
        }
    );

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

export function channelUsersHandler() {
    return channelUsers.value;
}
<script setup>
import { ref, onMounted, computed } from 'vue'
import { mdiMessage, mdiSend, mdiPaperclip, mdiEmoticon, mdiMagnify, mdiDotsVertical } from '@mdi/js'
import { useForm } from '@inertiajs/vue3'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import BaseButton from '@/Components/BaseButton.vue'
import { Head } from "@inertiajs/vue3"
import { usePage } from '@inertiajs/vue3'
import Echo from 'laravel-echo'

const props = defineProps({
    chats: Object,
    currentChat: Object,
})

const currentChatId = ref(null)
const messages = ref([])
const searchQuery = ref('')
const selectedChat = ref(null)

const form = useForm({
    message: '',
    chat_id: null,
    attachments: []
})

const filteredChats = computed(() => {
    if (!searchQuery.value) return props.chats
    return props.chats.filter(chat => 
        chat.user.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
})

const formatTime = (date) => {
    return new Date(date).toLocaleTimeString('vi-VN', { 
        hour: '2-digit', 
        minute: '2-digit' 
    })
}

const selectChat = async (chat) => {
    currentChatId.value = chat.id
    selectedChat.value = chat
    form.chat_id = chat.id
    
    // Fetch messages for selected chat
    const response = await fetch(`/api/chats/${chat.id}/messages`)
    const data = await response.json()
    messages.value = data.messages
    
    // Mark messages as read
    await fetch(`/api/chats/${chat.id}/mark-as-read`, { method: 'POST' })
}

const sendMessage = () => {
    if (!form.message.trim()) return

    form.post('/api/messages', {
        preserveScroll: true,
        onSuccess: () => {
            form.message = ''
            form.attachments = []
        }
    })
}

const handleFileUpload = (event) => {
    form.attachments = Array.from(event.target.files)
}

onMounted(() => {
    // Listen for new messages
    window.Echo.private(`chat.${currentChatId.value}`)
        .listen('NewMessage', (e) => {
            messages.value.push(e.message)
        })
})
</script>

<template>
    <LayoutAuthenticated>
        <Head title="Tin nhắn" />
        <SectionMain>
            <SectionTitleLineWithButton :icon="mdiMessage" title="Tin nhắn" main>
            </SectionTitleLineWithButton>

            <div class="flex h-[calc(100vh-200px)]">
                <!-- Left sidebar - Chat list -->
                <CardBox class="w-1/4 mr-4 p-0 overflow-hidden">
                    <div class="p-4 border-b">
                        <div class="relative">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Tìm kiếm..."
                                class="w-full px-4 py-2 rounded-lg border bg-gray-50 focus:outline-none focus:border-blue-500"
                            >
                            <BaseButton
                                :icon="mdiMagnify"
                                class="absolute right-2 top-1/2 transform -translate-y-1/2"
                                small
                            />
                        </div>
                    </div>

                    <div class="overflow-y-auto h-full">
                        <div
                            v-for="chat in filteredChats"
                            :key="chat.id"
                            @click="selectChat(chat)"
                            class="flex items-center p-4 hover:bg-gray-50 cursor-pointer transition-colors duration-150"
                            :class="{ 'bg-blue-50': currentChatId === chat.id }"
                        >
                            <div class="relative">
                                <img
                                    :src="chat.user.avatar || '/default-avatar.png'"
                                    class="w-12 h-12 rounded-full"
                                    alt="Avatar"
                                >
                                <div
                                    v-if="chat.user.is_online"
                                    class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"
                                />
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="flex justify-between items-center">
                                    <h3 class="font-medium">{{ chat.user.name }}</h3>
                                    <span class="text-sm text-gray-500">
                                        {{ formatTime(chat.last_message?.created_at) }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 truncate">
                                    {{ chat.last_message?.message }}
                                </p>
                            </div>
                        </div>
                    </div>
                </CardBox>

                <!-- Right side - Chat messages -->
                <CardBox class="flex-1 p-0 flex flex-col">
                    <template v-if="selectedChat">
                        <!-- Chat header -->
                        <div class="p-4 border-b flex justify-between items-center">
                            <div class="flex items-center">
                                <img
                                    :src="selectedChat.user.avatar || '/default-avatar.png'"
                                    class="w-10 h-10 rounded-full"
                                    alt="Avatar"
                                >
                                <div class="ml-3">
                                    <h3 class="font-medium">{{ selectedChat.user.name }}</h3>
                                    <p class="text-sm text-gray-500">
                                        {{ selectedChat.user.is_online ? 'Đang hoạt động' : 'Không hoạt động' }}
                                    </p>
                                </div>
                            </div>
                            <BaseButton :icon="mdiDotsVertical" />
                        </div>

                        <!-- Messages -->
                        <div class="flex-1 overflow-y-auto p-4">
                            <div
                                v-for="message in messages"
                                :key="message.id"
                                class="mb-4"
                            >
                                <div
                                    class="flex"
                                    :class="message.sender_id === $page.props.auth.user.id ? 'justify-end' : 'justify-start'"
                                >
                                    <div
                                        class="max-w-[70%] rounded-lg px-4 py-2"
                                        :class="message.sender_id === $page.props.auth.user.id ? 'bg-blue-500 text-white' : 'bg-gray-100'"
                                    >
                                        <p>{{ message.message }}</p>
                                        <span class="text-xs mt-1 block" :class="message.sender_id === $page.props.auth.user.id ? 'text-blue-100' : 'text-gray-500'">
                                            {{ formatTime(message.created_at) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Message input -->
                        <div class="p-4 border-t">
                            <div class="flex items-center space-x-2">
                                <BaseButton :icon="mdiEmoticon" />
                                <label class="cursor-pointer">
                                    <BaseButton :icon="mdiPaperclip" />
                                    <input
                                        type="file"
                                        class="hidden"
                                        multiple
                                        @change="handleFileUpload"
                                    >
                                </label>
                                <input
                                    v-model="form.message"
                                    type="text"
                                    placeholder="Nhập tin nhắn..."
                                    class="flex-1 px-4 py-2 rounded-lg border bg-gray-50 focus:outline-none focus:border-blue-500"
                                    @keyup.enter="sendMessage"
                                >
                                <BaseButton
                                    :icon="mdiSend"
                                    color="info"
                                    @click="sendMessage"
                                />
                            </div>
                        </div>
                    </template>

                    <div
                        v-else
                        class="flex-1 flex items-center justify-center text-gray-500"
                    >
                        Chọn một cuộc trò chuyện để bắt đầu
                    </div>
                </CardBox>
            </div>
        </SectionMain>
    </LayoutAuthenticated>
</template>

<script setup>
import { ref, onMounted, nextTick, computed, watch, onUnmounted } from 'vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import { usePage, Head } from '@inertiajs/vue3'
import axios from 'axios'
import moment from 'moment'
import debounce from 'lodash/debounce'
import SectionMain from '@/Components/SectionMain.vue'

const props = defineProps({
    chats: Array
})

const user = computed(() => usePage().props.auth.user)
const searchQuery = ref('')
const suggestedUsers = ref([])
const selectedChat = ref(null)
const messages = ref([])
const newMessage = ref('')
const messageContainer = ref(null)
const isLoading = ref(false)
const currentPage = ref(1)
const hasMoreMessages = ref(false)
const isLoadingMore = ref(false)
const isFirstLoad = ref(true)

// Format thời gian tin nhắn
const formatTime = (timestamp) => {
    const messageDate = moment(timestamp)
    const today = moment().startOf('day')

    if (messageDate.isSame(today, 'day')) {
        return messageDate.format('HH:mm')
    } else if (messageDate.isSame(today.clone().subtract(1, 'day'), 'day')) {
        return 'Hôm qua ' + messageDate.format('HH:mm')
    }
    return messageDate.format('DD/MM/YYYY HH:mm')
}

// Tìm kiếm người dùng với debounce
const searchUsers = debounce(async () => {
    if (!searchQuery.value) {
        suggestedUsers.value = []
        return
    }

    try {
        isLoading.value = true
        const response = await axios.get(`/api/users/search?q=${searchQuery.value}`)
        suggestedUsers.value = response.data.data.filter(u => u.id !== user.value.id)
    } catch (error) {
        console.error('Error searching users:', error)
    } finally {
        isLoading.value = false
    }
}, 300)

// Tạo chat mới
const startNewChat = async (userId) => {
    try {
        isLoading.value = true
        const response = await axios.post('/chats', { user_id: userId })
        const newChat = response.data.data

        // Thêm chat mới vào danh sách nếu chưa tồn tại
        if (!props.chats.find(c => c.id === newChat.id)) {
            props.chats.unshift(newChat)
        }

        selectedChat.value = newChat
        await loadMessages(newChat.id)
        searchQuery.value = ''
        suggestedUsers.value = []
    } catch (error) {
        console.error('Error creating chat:', error)
    } finally {
        isLoading.value = false
    }
}

// Load tin nhắn của chat
const loadMessages = async (chatId, page = 1, append = false) => {
    try {
        if (page === 1) {
            isLoading.value = true;
        } else {
            isLoadingMore.value = true;
        }

        const response = await axios.get(`/chats/${chatId}/messages`, {
            params: {
                page: page,
                per_page: 20
            }
        });

        const { messages: newMessages, has_more } = response.data.data;
        hasMoreMessages.value = has_more;

        // Đảo ngược thứ tự tin nhắn từ API để tin nhắn cũ nhất ở trên cùng
        const reversedMessages = [...newMessages].reverse();

        if (append) {
            // Khi load more, thêm tin nhắn cũ vào trên cùng
            messages.value = [...reversedMessages, ...messages.value];
        } else {
            messages.value = reversedMessages;
        }

        currentPage.value = page;

        // Đợi DOM cập nhật
        await nextTick();

        // Force cuộn xuống dưới khi là lần đầu load hoặc load trang đầu tiên
        if (isFirstLoad.value || !append) {
            scrollToBottom(true);
            isFirstLoad.value = false;
        }

        if (page === 1) {
            markAsRead(chatId);
        }
    } catch (error) {
        console.error('Error loading messages:', error);
    } finally {
        isLoading.value = false;
        isLoadingMore.value = false;
    }
}

// Gửi tin nhắn mới
const sendMessage = async () => {
    if (!selectedChat.value || !newMessage.value.trim()) return;

    try {
        const formData = new FormData();
        formData.append('chat_id', selectedChat.value.id);
        formData.append('message', newMessage.value);

        const response = await axios.post('/chats/send', formData);

        if (!Array.isArray(messages.value)) {
            messages.value = [];
        }

        // Thêm tin nhắn mới vào cuối danh sách
        const newMsg = response.data.data;
        messages.value = [...messages.value, newMsg];

        newMessage.value = '';

        // Cập nhật danh sách chat
        const chatIndex = props.chats.findIndex(c => c.id === selectedChat.value.id);
        if (chatIndex !== -1) {
            const updatedChat = { ...props.chats[chatIndex] };
            updatedChat.messages = [newMsg];
            props.chats.splice(chatIndex, 1);
            props.chats.unshift(updatedChat);
        }

        // Force cuộn xuống sau khi gửi tin nhắn
        await nextTick();
        scrollToBottom(true);
    } catch (error) {
        console.error('Error sending message:', error);
    }
};

// Thiết lập Echo listener khi component được mount
onMounted(() => {
    if (selectedChat.value) {
        subscribeToChat(selectedChat.value.id)
    }

    // Sử dụng handler function đã tách riêng
    window.addEventListener('refresh-chat-messages', handleRefreshMessages)
})

// Cleanup event listener
onUnmounted(() => {
    // Cleanup Echo listener cho chat hiện tại
    if (selectedChat.value?.id) {
        Echo.leave(`chat.${selectedChat.value.id}`)
    }

    // Cleanup event listener với handler function cụ thể
    window.removeEventListener('refresh-chat-messages', handleRefreshMessages)
    
    // Reset các ref về null hoặc giá trị mặc định
    selectedChat.value = null
    messages.value = []
    suggestedUsers.value = []
    searchQuery.value = ''
    currentPage.value = 1
    hasMoreMessages.value = false
    isLoadingMore.value = false
    isFirstLoad.value = true
})

// Tách handler function ra riêng
const handleRefreshMessages = (event) => {
    if (selectedChat.value?.id === event.detail.chatId) {
        loadMessages(selectedChat.value.id, 1)
    }
}

// Cập nhật hàm subscribeToChat
const subscribeToChat = (chatId) => {
    Echo.private(`chat.${chatId}`)
        .listen('NewMessage', (e) => {
            const messageExists = messages.value.some(m => m.id === e.message.id);
            if (!messageExists) {
                messages.value = [...messages.value, e.message];

                const chatIndex = props.chats.findIndex(c => c.id === chatId);
                if (chatIndex !== -1) {
                    const updatedChat = { ...props.chats[chatIndex] };
                    updatedChat.messages = [e.message];
                    props.chats.splice(chatIndex, 1);
                    props.chats.unshift(updatedChat);
                }

                // Force cuộn xuống khi nhận tin nhắn mới
                nextTick(() => {
                    scrollToBottom(true);
                    if (e.message.sender_id !== user.value.id) {
                        markAsRead(chatId);
                    }
                });
            }
        });
};

// Watch selectedChat để đăng ký/hủy đăng ký Echo listener
watch(() => selectedChat.value?.id, (newChatId, oldChatId) => {
    if (oldChatId) {
        Echo.leave(`chat.${oldChatId}`)
    }
    if (newChatId) {
        subscribeToChat(newChatId)
    }
})

// Watch searchQuery
watch(searchQuery, searchUsers)

// Thêm hàm selectChat
const selectChat = async (chat) => {
    selectedChat.value = chat;
    messages.value = [];
    currentPage.value = 1;
    hasMoreMessages.value = false;
    isFirstLoad.value = true;
    await loadMessages(chat.id, 1);
}

// Thêm hàm getOtherUser
const getOtherUser = (chat) => {
    return chat.user_id === user.value.id ? chat.staff : chat.user
}

// Thêm hàm scrollToBottom
const scrollToBottom = (force = false) => {
    if (messageContainer.value) {
        const container = messageContainer.value;
        const isScrolledNearBottom = container.scrollHeight - container.scrollTop - container.clientHeight < 100;

        // Cuộn xuống nếu force = true hoặc đang ở gần cuối
        if (force || isScrolledNearBottom) {
            nextTick(() => {
                container.scrollTop = container.scrollHeight;
            });
        }
    }
}

// Thêm hàm hasUnreadMessages
const hasUnreadMessages = (chat) => {
    return chat.messages && chat.messages.length > 0 &&
        chat.messages.some(message => !message.is_read && message.sender_id !== user.value.id);
}


// Thêm hàm markAsRead
const markAsRead = async (chatId) => {
    try {
        await axios.post(`/chats/${chatId}/mark-as-read`)
    } catch (error) {
        console.error('Error marking messages as read:', error)
    }
}

// Format date time helper
const formatDateTime = (datetime) => {
    const date = new Date(datetime);
    return new Intl.DateTimeFormat('vi-VN', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    }).format(date);
};

// Thêm hàm xử lý scroll để tải thêm tin nhắn
const handleScroll = debounce(async (e) => {
    const container = e.target
    // Kiểm tra khi scroll gần đến đầu container
    if (container.scrollTop <= 100 && hasMoreMessages.value && !isLoadingMore.value) {
        const nextPage = currentPage.value + 1
        const previousHeight = container.scrollHeight

        await loadMessages(selectedChat.value.id, nextPage, true)

        // Giữ nguyên vị trí scroll sau khi tải thêm tin nhắn
        const newHeight = container.scrollHeight
        container.scrollTop = newHeight - previousHeight
    }
}, 200)
</script>
<template>
    <LayoutAuthenticated>

        <Head title="Chats" />
        <!-- Thay đổi SectionMain để fix layout -->
        <SectionMain>
            <div class="flex h-[calc(100vh-4rem)] bg-gray-100"> <!-- Điều chỉnh chiều cao -->
                <!-- Sidebar danh sách chat -->
                <div class="w-1/3 min-w-[300px] max-w-[400px] border-r bg-white flex flex-col">
                    <!-- Thêm min/max width -->
                    <!-- Search box -->
                    <div class="p-4 border-b">
                        <div class="relative">
                            <input v-model="searchQuery" type="text" placeholder="Tìm kiếm người dùng..."
                                class="w-full px-4 py-2 rounded-lg border bg-gray-50 focus:outline-none focus:border-blue-500">
                            <!-- Loading indicator -->
                            <div v-if="isLoading" class="absolute right-3 top-1/2 -translate-y-1/2">
                                <div
                                    class="w-4 h-4 border-2 border-blue-500 border-t-transparent rounded-full animate-spin">
                                </div>
                            </div>

                            <!-- Dropdown tìm kiếm -->
                            <div v-if="searchQuery && suggestedUsers.length"
                                class="absolute left-0 right-0 top-full mt-1 bg-white border rounded-lg shadow-lg z-50 max-h-60 overflow-y-auto">
                                <div v-for="user in suggestedUsers" :key="user.id" @click="startNewChat(user.id)"
                                    class="flex items-center space-x-3 p-3 hover:bg-gray-50 cursor-pointer transition duration-150">
                                    <img :src="user.avatar || 'storage/images/users/default.png'" :alt="user.full_name"
                                        class="w-10 h-10 rounded-full object-cover">
                                    <div>
                                        <div class="font-medium">{{ user.full_name }}</div>
                                        <div class="text-sm text-gray-500">{{ user.phone_number }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Danh sách chat -->
                    <div class="flex-1 overflow-y-auto">
                        <div v-if="chats.length === 0" class="p-4 text-center text-gray-500">
                            Chưa có cuộc trò chuyện nào
                        </div>
                        <div v-else>
                            <div v-for="chat in chats" :key="chat.id" @click="selectChat(chat)" :class="[
                                'p-4 border-b hover:bg-gray-50 cursor-pointer transition duration-150',
                                selectedChat?.id === chat.id ? 'bg-blue-50' : ''
                            ]">
                                <div class="flex items-center space-x-3">
                                    <img :src="getOtherUser(chat).avatar || 'storage/images/users/default.png'"
                                        :alt="getOtherUser(chat).full_name" class="w-12 h-12 rounded-full object-cover">
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium">{{ getOtherUser(chat).full_name }}</div>
                                        <div class="text-sm text-gray-500 truncate">
                                            {{ chat.messages && chat.messages.length > 0
                                                ? chat.messages[0].message
                                                : 'Bắt đầu cuộc trò chuyện' }}
                                        </div>
                                    </div>
                                    <div v-if="hasUnreadMessages(chat)"
                                        class="w-3 h-3 bg-blue-500 rounded-full flex-shrink-0"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chat area -->
                <div class="flex-1 flex flex-col overflow-hidden"> <!-- Thêm overflow-hidden -->
                    <template v-if="selectedChat">
                        <!-- Chat header -->
                        <div class="p-4 border-b bg-white">
                            <div class="flex items-center space-x-3">
                                <img :src="getOtherUser(selectedChat).avatar || 'storage/images/users/default.png'"
                                    :alt="getOtherUser(selectedChat).full_name"
                                    class="w-10 h-10 rounded-full object-cover">
                                <div>
                                    <div class="font-medium">{{ getOtherUser(selectedChat).full_name }}</div>
                                    <div class="text-sm text-gray-500">
                                        {{ getOtherUser(selectedChat).phone_number }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Messages -->
                        <div ref="messageContainer" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50"
                            @scroll="handleScroll">
                            <!-- Thêm loading indicator cho "Load more" -->
                            <div v-if="isLoadingMore" class="flex justify-center py-2">
                                <div
                                    class="w-6 h-6 border-2 border-blue-500 border-t-transparent rounded-full animate-spin">
                                </div>
                            </div>

                            <div v-if="isLoading" class="flex justify-center">
                                <div
                                    class="w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin">
                                </div>
                            </div>
                            <template v-else>
                                <div v-if="messages && messages.length > 0">
                                    <div v-for="message in messages" :key="message.id"
                                        :class="['flex mb-4', message.sender_id === user.id ? 'justify-end' : 'justify-start']">
                                        <div :class="[
                                            'max-w-[70%] rounded-lg p-3',
                                            message.sender_id === user.id ? 'bg-blue-500 text-white' : 'bg-white'
                                        ]">
                                            <div class="break-words whitespace-pre-wrap">{{ message.message }}</div>
                                            <div :class="[
                                                'text-xs mt-1',
                                                message.sender_id === user.id ? 'text-blue-100' : 'text-gray-500'
                                            ]">
                                                {{ formatTime(message.created_at) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="flex justify-center items-center h-full">
                                    <div class="text-gray-500">Chưa có tin nhắn nào</div>
                                </div>
                            </template>
                        </div>

                        <!-- Message input -->
                        <div class="p-4 bg-white border-t">
                            <form @submit.prevent="sendMessage" class="flex space-x-2">
                                <input v-model="newMessage" type="text" placeholder="Nhập tin nhắn..."
                                    class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                                <button type="submit" :disabled="!newMessage.trim() || isLoading" :class="[
                                    'px-6 py-2 rounded-lg transition duration-150',
                                    newMessage.trim() && !isLoading
                                        ? 'bg-blue-500 hover:bg-blue-600 text-white'
                                        : 'bg-gray-200 text-gray-500 cursor-not-allowed'
                                ]">
                                    <span v-if="isLoading">Đang gửi...</span>
                                    <span v-else>Gửi</span>
                                </button>
                            </form>
                        </div>
                    </template>

                    <!-- Empty state -->
                    <div v-else class="flex-1 flex items-center justify-center bg-gray-50">
                        <div class="text-center text-gray-500">
                            <div class="text-xl mb-2">👋 Chào mừng đến với tin nhắn</div>
                            <div>Chọn một cuộc trò chuyện để bắt đầu</div>
                        </div>
                    </div>
                </div>
            </div>
        </SectionMain>
    </LayoutAuthenticated>
</template>

<style scoped>
/* Thêm styles để đảm bảo scroll hoạt động đúng */
:deep(.section-main) {
    padding: 0;
    height: calc(100vh - 4rem);
}

.messages-container {
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
}

.messages-container::-webkit-scrollbar {
    width: 6px;
}

.messages-container::-webkit-scrollbar-track {
    background: transparent;
}

.messages-container::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 3px;
}
</style>

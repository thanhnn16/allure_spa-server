<script setup>
import { ref, onMounted, nextTick, computed, watch, onUnmounted, onBeforeUnmount } from 'vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import { usePage, Head } from '@inertiajs/vue3'
import axios from 'axios'
import moment from 'moment'
import debounce from 'lodash/debounce'
import SectionMain from '@/Components/SectionMain.vue'
import { ref as vueRef } from 'vue'
import {
    mdiImage,
    mdiEmoticonOutline
} from '@mdi/js'
import 'emoji-picker-element';

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
const fileInput = vueRef(null)
const selectedFile = ref(null)
const isUploading = ref(false)
const previewUrl = ref(null)
const showEmojiPicker = ref(false)
const emojiPickerContainer = ref(null)
const searchContainer = ref(null)
const showSearchResults = ref(false)

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
        showSearchResults.value = false
        return
    }

    try {
        isLoading.value = true
        const response = await axios.get(`/api/users/search?q=${searchQuery.value}`)
        suggestedUsers.value = response.data.data.filter(u => u.id !== user.value.id)
        showSearchResults.value = true
    } catch (error) {
        console.error('Error searching users:', error)
    } finally {
        isLoading.value = false
    }
}, 300)

// Tạo chat mới

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
    if (!selectedChat.value || (!newMessage.value.trim() && !selectedFile.value)) return

    try {
        isUploading.value = true
        const formData = new FormData()
        formData.append('chat_id', selectedChat.value.id)

        if (newMessage.value.trim()) {
            formData.append('message', newMessage.value)
        }

        if (selectedFile.value) {
            formData.append('file', selectedFile.value)
        }

        const response = await axios.post('/chats/send', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })

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

        // Reset file selection
        selectedFile.value = null
        previewUrl.value = null
        if (fileInput.value) {
            fileInput.value.value = ''
        }
    } catch (error) {
        console.error('Error sending message:', error)
    } finally {
        isUploading.value = false
    }
}

// Thêm hàm để cancel file upload
const cancelFileUpload = () => {
    selectedFile.value = null
    previewUrl.value = null
    if (fileInput.value) {
        fileInput.value.value = ''
    }
}

// Thêm hàm xử lý file

// Thiết lập Echo listener khi component được mount
onMounted(() => {
    if (selectedChat.value) {
        subscribeToChat(selectedChat.value.id)
    }

    window.addEventListener('refresh-chat-messages', handleRefreshMessages)
    document.addEventListener('click', handleClickOutside)
    document.addEventListener('click', handleSearchClickOutside)
})

// Cleanup event listener
onUnmounted(() => {
    cleanup()
})

// Update the cleanup function
const cleanup = () => {
    // Cleanup Echo listener
    if (selectedChat.value?.id) {
        Echo.leave(`chat.${selectedChat.value.id}`)
    }

    // Remove event listeners
    window.removeEventListener('refresh-chat-messages', handleRefreshMessages)
    document.removeEventListener('click', handleClickOutside)
    document.removeEventListener('click', handleSearchClickOutside)
    
    // Reset all state to initial values
    searchQuery.value = ''
    currentPage.value = 1
    hasMoreMessages.value = false
    isLoadingMore.value = false
    isFirstLoad.value = true
    isLoading.value = false
    isUploading.value = false
    showEmojiPicker.value = false
    newMessage.value = ''
    selectedChat.value = null
    messages.value = []
    suggestedUsers.value = []
    selectedFile.value = null
    
    // Cleanup URLs
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value)
        previewUrl.value = null
    }
    
    // Reset file input
    if (fileInput.value) {
        fileInput.value.value = ''
    }
    
    // Thêm cleanup cho search click outside
    document.removeEventListener('click', handleSearchClickOutside)
    showSearchResults.value = false
}

// Update the watch for selectedChat
watch(() => selectedChat.value, (newChat, oldChat) => {
    if (oldChat?.id) {
        Echo.leave(`chat.${oldChat.id}`)
    }
    
    if (newChat?.id) {
        subscribeToChat(newChat.id)
    }
}, { deep: true })

// Ensure proper cleanup on component unmount
onBeforeUnmount(() => {
    cleanup()
})

onUnmounted(() => {
    cleanup()
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
    if (!datetime) return '';

    try {
        return moment(datetime).format('HH:mm');
    } catch (error) {
        console.error('Error formatting date:', error);
        return '';
    }
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

// Add this method to handle emoji selection
const onEmojiSelect = (event) => {
    const emoji = event.detail.unicode
    newMessage.value += emoji
    showEmojiPicker.value = false
}

// Add this to handle clicking outside emoji picker
const handleClickOutside = (event) => {
    if (emojiPickerContainer.value && !emojiPickerContainer.value.contains(event.target)) {
        showEmojiPicker.value = false
    }
}

// Thêm hàm createNewChat
const createNewChat = async (userId) => {
    try {
        const response = await axios.post('/chats/', {
            user_id: userId
        })
        
        // Thêm chat mới vào đầu danh sách
        props.chats.unshift(response.data.data)
        
        // Chọn chat mới tạo
        await selectChat(response.data.data)
        
        // Xóa kết quả tìm kiếm
        searchQuery.value = ''
        suggestedUsers.value = []
    } catch (error) {
        console.error('Error creating chat:', error)
    }
}

// Thêm hàm xử lý click outside cho search
const handleSearchClickOutside = (event) => {
    if (searchContainer.value && !searchContainer.value.contains(event.target)) {
        showSearchResults.value = false
        searchQuery.value = ''
        suggestedUsers.value = []
    }
}
</script>
<template>
    <LayoutAuthenticated>

        <Head title="Chats" />
        <SectionMain>
            <div class="flex h-[calc(100vh-6rem)] bg-white dark:bg-dark-surface rounded-lg shadow-lg mx-4">
                <div class="w-1/3 min-w-[300px] max-w-[400px] border-r dark:border-dark-border flex flex-col">
                    <div class="p-4 border-b dark:border-dark-border bg-gray-50 dark:bg-dark-surface">
                        <div class="relative" ref="searchContainer">
                            <input v-model="searchQuery" 
                                type="text" 
                                placeholder="Tìm kiếm người dùng..."
                                @focus="showSearchResults = true"
                                class="w-full px-4 py-3 pl-10 rounded-full border bg-white dark:bg-dark-modal dark:border-dark-border dark:text-dark-text focus:outline-none focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-400 transition-all">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                            
                            <!-- Cập nhật điều kiện hiển thị kết quả tìm kiếm -->
                            <div v-if="showSearchResults && suggestedUsers.length > 0" 
                                class="absolute z-50 left-0 right-0 mt-2 bg-white dark:bg-dark-modal rounded-xl shadow-xl border dark:border-dark-border max-h-[300px] overflow-y-auto">
                                <div v-for="user in suggestedUsers" 
                                    :key="user.id"
                                    @click="createNewChat(user.id)"
                                    class="p-3 flex items-center space-x-3 hover:bg-gray-50 dark:hover:bg-dark-hover cursor-pointer first:rounded-t-xl last:rounded-b-xl border-b last:border-0 dark:border-dark-border transition-colors duration-200">
                                    <img :src="user.avatar_url" 
                                        :alt="user.full_name"
                                        class="w-10 h-10 rounded-full object-cover border-2 border-gray-200 dark:border-dark-border">
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium dark:text-dark-text truncate">{{ user.full_name }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ user.phone_number }}</div>
                                    </div>
                                    <div class="text-gray-400 dark:text-gray-500">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto aside-scrollbars">
                        <div v-if="chats.length === 0" class="flex flex-col items-center justify-center h-full p-4">
                            <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <p class="text-gray-500 dark:text-gray-400">Chưa có cuộc trò chuyện nào</p>
                        </div>
                        <div v-else>
                            <div v-for="chat in chats" :key="chat.id" @click="selectChat(chat)" :class="[
                                'p-4 hover:bg-gray-50 dark:hover:bg-dark-hover cursor-pointer transition-all duration-200',
                                selectedChat?.id === chat.id ? 'bg-primary-50 dark:bg-primary-900/20' : ''
                            ]">
                                <div class="flex items-center space-x-3">
                                    <div class="relative">
                                        <img :src="getOtherUser(chat).avatar || 'storage/images/users/default.png'"
                                            :alt="getOtherUser(chat).full_name"
                                            class="w-12 h-12 rounded-full object-cover ring-2 ring-offset-2 dark:ring-offset-dark-surface"
                                            :class="[
                                                selectedChat?.id === chat.id
                                                    ? 'ring-primary-500'
                                                    : 'ring-transparent'
                                            ]">
                                        <span
                                            class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white dark:border-dark-surface rounded-full"></span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium dark:text-dark-text">
                                                {{ getOtherUser(chat).full_name }}
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ formatDateTime(chat.messages?.[0]?.created_at) }}
                                            </span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                                {{ chat.messages && chat.messages.length > 0
                                                    ? chat.messages[0].message
                                                    : 'Bắt đầu cuộc trò chuyện' }}
                                            </p>
                                            <div v-if="hasUnreadMessages(chat)"
                                                class="flex-shrink-0 w-5 h-5 bg-primary-500 rounded-full flex items-center justify-center">
                                                <span class="text-xs text-white">1</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-1 flex flex-col bg-gray-50 dark:bg-dark-bg">
                    <template v-if="selectedChat">
                        <div class="p-4 bg-white dark:bg-dark-surface border-b dark:border-dark-border">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="relative">
                                        <img :src="getOtherUser(selectedChat).avatar || 'storage/images/users/default.png'"
                                            :alt="getOtherUser(selectedChat).full_name"
                                            class="w-10 h-10 rounded-full object-cover">
                                    </div>
                                    <div>
                                        <div class="font-medium dark:text-dark-text">
                                            {{ getOtherUser(selectedChat).full_name }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ getOtherUser(selectedChat).phone_number }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <button
                                        class="p-2 text-gray-500 hover:text-primary-500 dark:text-gray-400 dark:hover:text-primary-400 rounded-full hover:bg-gray-100 dark:hover:bg-dark-hover transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div ref="messageContainer" class="flex-1 overflow-y-auto p-4 space-y-4 aside-scrollbars"
                            @scroll="handleScroll">
                            <div v-if="isLoadingMore" class="flex justify-center py-2">
                                <div
                                    class="w-6 h-6 border-2 border-primary-500 border-t-transparent rounded-full animate-spin">
                                </div>
                            </div>

                            <div v-if="isLoading" class="flex justify-center">
                                <div
                                    class="w-8 h-8 border-4 border-primary-500 border-t-transparent rounded-full animate-spin">
                                </div>
                            </div>

                            <template v-else>
                                <div v-if="messages && messages.length > 0">
                                    <div v-for="(message) in messages" :key="message.id" :class="[
                                        'flex mb-4',
                                        message.sender_id === user.id ? 'justify-end' : 'justify-start'
                                    ]">
                                        <div v-if="message.sender_id !== user.id" class="flex-shrink-0 mr-3">
                                            <img :src="getOtherUser(selectedChat).avatar || 'storage/images/users/default.png'"
                                                class="w-8 h-8 rounded-full">
                                        </div>

                                        <div :class="[
                                            'max-w-[70%]',
                                            message.sender_id === user.id ? 'order-1' : 'order-2'
                                        ]">
                                            <div :class="[
                                                'px-4 py-2 rounded-2xl',
                                                message.sender_id === user.id
                                                    ? 'bg-primary-500 text-white rounded-br-none'
                                                    : 'bg-white dark:bg-dark-surface dark:text-dark-text rounded-bl-none'
                                            ]">
                                                <div v-if="message.file_url" class="mb-2">
                                                    <img v-if="message.file_type?.startsWith('image/')"
                                                        :src="message.file_url"
                                                        class="rounded-lg max-w-full max-h-[300px] object-contain"
                                                        alt="Uploaded image" />
                                                    <video v-else-if="message.file_type?.startsWith('video/')"
                                                        :src="message.file_url"
                                                        class="rounded-lg max-w-full max-h-[300px]" controls />
                                                </div>

                                                <div class="break-words whitespace-pre-wrap">
                                                    {{ message.message }}
                                                </div>
                                            </div>

                                            <div class="mt-1 text-xs" :class="[
                                                message.sender_id === user.id
                                                    ? 'text-right text-gray-500 dark:text-gray-400'
                                                    : 'text-left text-gray-500 dark:text-gray-400'
                                            ]">
                                                {{ formatTime(message.created_at) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="p-4 bg-white dark:bg-dark-surface border-t dark:border-dark-border">
                            <div v-if="selectedFile" class="mb-4">
                                <div class="relative inline-block group">
                                    <img v-if="previewUrl && selectedFile.type.startsWith('image/')" :src="previewUrl"
                                        class="max-h-[200px] rounded-lg border dark:border-dark-border" alt="Preview" />
                                    <video v-else-if="previewUrl && selectedFile.type.startsWith('video/')"
                                        :src="previewUrl"
                                        class="max-h-[200px] rounded-lg border dark:border-dark-border" controls />
                                    <button @click="cancelFileUpload"
                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1.5 hover:bg-red-600 transition-colors shadow-lg">
                                        <span class="w-4 h-4 block">
                                            <svg viewBox="0 0 24 24" class="w-full h-full">
                                                <path fill="currentColor"
                                                    d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z" />
                                            </svg>
                                        </span>
                                    </button>
                                </div>
                            </div>

                            <form @submit.prevent="sendMessage" class="flex items-end space-x-3">
                                <div class="flex-1 relative">
                                    <input v-model="newMessage" type="text" placeholder="Nhập tin nhắn..."
                                        class="w-full px-4 py-3 pr-24 border rounded-full dark:bg-dark-modal dark:border-dark-border dark:text-dark-text focus:outline-none focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-400 transition-all" />

                                    <div
                                        class="absolute right-2 bottom-1/2 transform translate-y-1/2 flex items-center space-x-2">
                                        <div class="relative" ref="emojiPickerContainer">
                                            <button type="button" @click="showEmojiPicker = !showEmojiPicker"
                                                class="p-2 text-gray-500 hover:text-primary-500 dark:text-gray-400 dark:hover:text-primary-400 rounded-full hover:bg-gray-100 dark:hover:bg-dark-hover transition-all">
                                                <svg viewBox="0 0 24 24" class="w-5 h-5">
                                                    <path fill="currentColor" :d="mdiEmoticonOutline" />
                                                </svg>
                                            </button>

                                            <emoji-picker v-if="showEmojiPicker"
                                                class="absolute bottom-full right-0 mb-2"
                                                @emoji-click="onEmojiSelect"></emoji-picker>
                                        </div>

                                        <button type="button" @click="() => fileInput.click()"
                                            class="p-2 text-gray-500 hover:text-primary-500 dark:text-gray-400 dark:hover:text-primary-400 rounded-full hover:bg-gray-100 dark:hover:bg-dark-hover transition-all">
                                            <svg viewBox="0 0 24 24" class="w-5 h-5">
                                                <path fill="currentColor" :d="mdiImage" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" :disabled="(!newMessage.trim() && !selectedFile) || isUploading"
                                    class="p-3 rounded-full transition-all duration-200 flex items-center justify-center min-w-[3rem] disabled:opacity-50"
                                    :class="[
                                        (newMessage.trim() || selectedFile) && !isUploading
                                            ? 'bg-primary-500 hover:bg-primary-600 text-white shadow-lg'
                                            : 'bg-gray-200 dark:bg-dark-modal text-gray-500 dark:text-gray-400 cursor-not-allowed'
                                    ]">
                                    <span class="w-6 h-6 block">
                                        <svg v-if="isUploading" class="animate-spin" viewBox="0 0 24 24">
                                            <path fill="currentColor" d="M12,4V2A10,10 0 0,0 2,12H4A8,8 0 0,1 12,4Z" />
                                        </svg>
                                        <svg v-else viewBox="0 0 24 24">
                                            <path fill="currentColor" d="M2,21L23,12L2,3V10L17,12L2,14V21Z" />
                                        </svg>
                                    </span>
                                </button>
                            </form>
                        </div>
                    </template>

                    <div v-else class="flex-1 flex items-center justify-center bg-gray-50 dark:bg-dark-bg">
                        <div class="text-center space-y-4">
                            <div
                                class="w-20 h-20 mx-auto bg-gray-200 dark:bg-dark-modal rounded-full flex items-center justify-center">
                                <svg class="w-10 h-10 text-gray-500 dark:text-gray-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </div>
                            <div class="space-y-2">
                                <p class="text-xl font-medium dark:text-dark-text">Chào mừng đến với tin nhắn</p>
                                <p class="text-gray-500 dark:text-gray-400">Chọn một cuộc trò chuyện để bắt đầu</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </SectionMain>
    </LayoutAuthenticated>
</template>

<style scoped>
:deep(.section-main) {
    padding: 1rem;
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

/* Add transition for icons */
.svg-icon {
    transition: color 0.15s ease-in-out;
}

/* Cập nhật styles */
:deep(.section-main) {
    padding: 1rem;
    height: calc(100vh - 4rem);
}

/* Custom scrollbar styles */
.aside-scrollbars {
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
}

.aside-scrollbars::-webkit-scrollbar {
    width: 4px;
}

.aside-scrollbars::-webkit-scrollbar-track {
    background: transparent;
}

.aside-scrollbars::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 2px;
}

.aside-scrollbars:hover::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.7);
}

/* Smooth transitions */
.svg-icon {
    transition: all 0.2s ease-in-out;
}

/* Message animations */
.message-enter-active,
.message-leave-active {
    transition: all 0.3s ease;
}

.message-enter-from,
.message-leave-to {
    opacity: 0;
    transform: translateY(20px);
}

/* Loading animation */
@keyframes bounce {

    0%,
    100% {
        transform: translateY(-25%);
        animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
    }

    50% {
        transform: translateY(0);
        animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
    }
}

.animate-bounce {
    animation: bounce 1s infinite;
}

/* Emoji picker customization */
emoji-picker {
    --background: var(--surface-background);
    --border-color: var(--border-color);
    --indicator-color: var(--primary-color);
    --input-border-color: var(--border-color);
    --input-font-color: var(--text-color);
    --input-placeholder-color: var(--text-secondary);
    --category-font-color: var(--text-secondary);
    height: 350px;
    z-index: 100;
}

.dark emoji-picker {
    --background: var(--dark-modal);
    --border-color: var(--dark-border);
    --text-color: var(--dark-text);
    --category-font-color: var(--dark-text-secondary);
}
</style>

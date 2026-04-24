<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import api from '../services/api'

const notifications = ref([])
const open = ref(false)
const userId = ref(null)
const container = ref(null)

const handleClickOutside = (event) => {
    if (container.value && !container.value.contains(event.target)) {
        open.value = false
    }
}

const fetchNotifications = async () => {
    try {
        const response = await api.get('/api/notifications')
        notifications.value = response.data.data
    } catch (error) {
        console.error('Failed to fetch notifications', error)
    }
}

const markAsRead = async (notification) => {
    try {
        await api.post(`/api/notifications/${notification.id}/read`)
        notifications.value = notifications.value.filter(n => n.id !== notification.id)
    } catch (error) {
        console.error('Failed to mark notification as read', error)
    }
}

const markAllAsRead = async () => {
    for (const notification of notifications.value) {
        await markAsRead(notification)
    }
}

onMounted(async () => {
    document.addEventListener('click', handleClickOutside)
    // Fetch unread notifications from DB → fill the list
    await fetchNotifications()
    // Get current user's ID
    const meRes = await api.get('/api/me')
    userId.value = meRes.data.user.id
    // Open a WebSocket connection and listen for new notifications
    window.Echo.private(`App.Models.User.${userId.value}`)
        .notification((notification) => {
            notifications.value.unshift(notification)
        })
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
    if (userId.value) {
        window.Echo.leave(`App.Models.User.${userId.value}`)
    }
})
</script>

<template>
    <div ref="container" class="relative">
        <button @click="open = !open" class="relative p-2 text-gray-600 hover:text-gray-900">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span
                v-if="notifications.length"
                class="absolute top-1 right-1 h-4 w-4 rounded-full bg-red-500 text-xs text-white flex items-center justify-center"
            >
                {{ notifications.length }}
            </span>
        </button>

        <div
            v-if="open"
            class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
        >
            <div class="flex items-center justify-between px-4 py-3 border-b">
                <span class="font-semibold text-sm text-gray-700">Notifications</span>
                <button
                    v-if="notifications.length"
                    @click="markAllAsRead"
                    class="text-xs text-blue-600 hover:underline"
                >
                    Mark all as read
                </button>
            </div>

            <ul class="max-h-72 overflow-y-auto divide-y divide-gray-100">
                <li
                    v-for="notification in notifications"
                    :key="notification.id"
                    class="px-4 py-3 hover:bg-gray-50 cursor-pointer"
                    @click="markAsRead(notification)"
                >
                    <p class="text-sm text-gray-800">{{ notification.data?.message ?? notification.message }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ notification.created_at?.slice(0, 16).replace('T', ' ') ?? 'Just now' }}</p>
                </li>
                <li v-if="!notifications.length" class="px-4 py-4 text-sm text-gray-400 text-center">
                    No unread notifications
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import NotificationBell from './NotificationBell.vue'

const router = useRouter()
const user = ref(null)

onMounted(async () => {
    try {
        const res = await api.get('/api/me')
        user.value = res.data.user
    } catch {
        router.push('/login')
    }
})

const logout = async () => {
    try {
        await api.post('/logout')
        router.push('/login')
    } catch (error) {
        console.error(error)
    }
}

const navItems = [
    {
        name: 'Dashboard',
        route: '/dashboard',
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>`,
    },
    {
        name: 'Prescriptions',
        route: '/prescriptions',
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>`,
    },
    {
        name: 'Administrations',
        route: '/administrations',
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>`,
    },
    {
        name: 'Patients',
        route: '/patients',
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>`,
    },
    {
        name: 'Medications',
        route: '/medications',
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18" /></svg>`,
    },
    {
        name: 'Users',
        route: '/users',
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>`,
    },
    {
        name: 'Activity Logs',
        route: '/activity-logs',
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>`,
    },
]
</script>

<template>
    <div class="flex h-screen bg-gray-100">

        <!-- Sidebar -->
        <aside class="flex flex-col items-center w-16 bg-gray-900 py-4 gap-2">
            <!-- Logo -->
            <div class="mb-4 text-white font-bold text-xs text-center px-1">M</div>

            <!-- Nav items -->
            <nav class="flex flex-col items-center gap-1 flex-1">
                <router-link
                    v-for="item in navItems"
                    :key="item.route"
                    :to="item.route"
                    class="group relative flex items-center justify-center w-10 h-10 rounded-lg text-gray-400 hover:bg-gray-700 hover:text-white transition"
                    active-class="bg-gray-700 text-white"
                >
                    <span v-html="item.icon"></span>
                    <!-- Tooltip -->
                    <span class="absolute left-14 bg-gray-800 text-white text-xs rounded px-2 py-1 whitespace-nowrap opacity-0 group-hover:opacity-100 transition pointer-events-none z-50">
                        {{ item.name }}
                    </span>
                </router-link>
            </nav>

            <!-- Logout -->
            <button
                @click="logout"
                class="group relative flex items-center justify-center w-10 h-10 rounded-lg text-gray-400 hover:bg-red-600 hover:text-white transition"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span class="absolute left-14 bg-gray-800 text-white text-xs rounded px-2 py-1 whitespace-nowrap opacity-0 group-hover:opacity-100 transition pointer-events-none z-50">
                    Logout
                </span>
            </button>
        </aside>

        <!-- Main content -->
        <div class="flex flex-col flex-1 overflow-hidden">

            <!-- Header -->
            <header class="flex items-center justify-between bg-white border-b border-gray-200 px-6 h-14 shrink-0">
                <h1 class="text-sm font-semibold text-gray-700">Medimo</h1>
                <div class="flex items-center gap-4">
                    <span v-if="user" class="text-sm text-gray-500">
                        {{ user.name }} · {{ user.role }}
                    </span>
                    <NotificationBell />
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

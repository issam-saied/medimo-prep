<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api, { getCsrfCookie } from '../services/api'

const router = useRouter()

const email = ref('')
const password = ref('')
const errorMessage = ref('')
const loading = ref(false)

const handleLogin = async () => {
    loading.value = true
    errorMessage.value = ''

    try {
        await getCsrfCookie()

        await api.post('/login', {
            email: email.value,
            password: password.value,
        })

        await api.get('/api/me')

        router.push('/dashboard')
    } catch (error) {
        errorMessage.value =
            error.response?.data?.message || 'Login failed'

        console.error(error)
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-blue-600 mb-4 shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Medimo</h1>
                <p class="mt-1 text-sm text-gray-500">Medication management</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">Sign in to your account</h2>

                <form @submit.prevent="handleLogin" class="space-y-5">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Email</label>
                        <input
                            v-model="email"
                            type="email"
                            placeholder="you@example.com"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                            required
                            autocomplete="email"
                        />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Password</label>
                        <input
                            v-model="password"
                            type="password"
                            placeholder="••••••••"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                            required
                            autocomplete="current-password"
                        />
                    </div>

                    <div
                        v-if="errorMessage"
                        class="rounded-lg border border-red-200 bg-red-50 px-3 py-2.5 text-sm text-red-700"
                    >
                        {{ errorMessage }}
                    </div>

                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{ loading ? 'Signing in...' : 'Sign in' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

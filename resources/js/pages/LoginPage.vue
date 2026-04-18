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

        router.push('/prescriptions')
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
    <div style="max-width: 400px; margin: 40px auto;">
        <h1>Login</h1>

        <form @submit.prevent="handleLogin">
            <div style="margin-bottom: 12px;">
                <label>Email</label>
                <input
                    v-model="email"
                    type="email"
                    style="width: 100%; padding: 8px;"
                />
            </div>

            <div style="margin-bottom: 12px;">
                <label>Password</label>
                <input
                    v-model="password"
                    type="password"
                    style="width: 100%; padding: 8px;"
                />
            </div>

            <p v-if="errorMessage" style="color: red;">
                {{ errorMessage }}
            </p>

            <button type="submit" :disabled="loading">
                {{ loading ? 'Logging in...' : 'Login' }}
            </button>
        </form>
    </div>
</template>

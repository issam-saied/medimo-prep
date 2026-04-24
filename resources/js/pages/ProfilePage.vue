<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'

const user = ref(null)
const profileSuccess = ref(false)
const profileErrors = ref({})

const passwordSuccess = ref(false)
const passwordErrors = ref({})

const profileForm = ref({ name: '', email: '', job_title: '', organization: '' })
const passwordForm = ref({ current_password: '', password: '', password_confirmation: '' })

onMounted(async () => {
    const res = await api.get('/api/me')
    user.value = res.data.user
    profileForm.value = {
        name: user.value.name ?? '',
        email: user.value.email ?? '',
        job_title: user.value.job_title ?? '',
        organization: user.value.organization ?? '',
    }
})

const saveProfile = async () => {
    profileErrors.value = {}
    profileSuccess.value = false
    try {
        const res = await api.put('/api/profile', profileForm.value)
        user.value = res.data.user
        profileSuccess.value = true
    } catch (err) {
        profileErrors.value = err.response?.data?.errors ?? {}
    }
}

const savePassword = async () => {
    passwordErrors.value = {}
    passwordSuccess.value = false
    try {
        console.log('passwordForm.value', passwordForm.value);
        await api.put('/api/profile/password', passwordForm.value)
        passwordSuccess.value = true
        passwordForm.value = { current_password: '', password: '', password_confirmation: '' }
    } catch (err) {
        passwordErrors.value = err.response?.data?.errors ?? {}
    }
}
</script>

<template>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-xl font-bold text-gray-800 mb-6">Profile</h1>

        <div v-if="!user" class="text-gray-500">Loading...</div>

        <template v-else>

            <!-- Profile info -->
            <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Personal information</h2>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <p class="text-sm text-gray-500 bg-gray-50 border border-gray-200 rounded px-3 py-2">{{ user.role }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input v-model="profileForm.name" type="text" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        <p v-if="profileErrors.name" class="text-red-500 text-xs mt-1">{{ profileErrors.name[0] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input v-model="profileForm.email" type="email" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        <p v-if="profileErrors.email" class="text-red-500 text-xs mt-1">{{ profileErrors.email[0] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Job title</label>
                        <input v-model="profileForm.job_title" type="text" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        <p v-if="profileErrors.job_title" class="text-red-500 text-xs mt-1">{{ profileErrors.job_title[0] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Organization</label>
                        <input v-model="profileForm.organization" type="text" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        <p v-if="profileErrors.organization" class="text-red-500 text-xs mt-1">{{ profileErrors.organization[0] }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button @click="saveProfile" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                        Save changes
                    </button>
                    <span v-if="profileSuccess" class="text-sm text-green-600">Saved successfully.</span>
                </div>
            </div>

            <!-- Change password -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Change password</h2>

                <div class="flex flex-col gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Current password</label>
                        <input v-model="passwordForm.current_password" type="password" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        <p v-if="passwordErrors.current_password" class="text-red-500 text-xs mt-1">{{ passwordErrors.current_password[0] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New password</label>
                        <input v-model="passwordForm.password" type="password" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        <p v-if="passwordErrors.password" class="text-red-500 text-xs mt-1">{{ passwordErrors.password[0] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm new password</label>
                        <input v-model="passwordForm.password_confirmation" type="password" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button @click="savePassword" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                        Update password
                    </button>
                    <span v-if="passwordSuccess" class="text-sm text-green-600">Password updated.</span>
                </div>
            </div>

        </template>
    </div>
</template>

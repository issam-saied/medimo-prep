<script setup>
import {reactive, ref} from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const form = reactive({
    name: '',
    email: '',
    password: '',
    job_title: '',
    organization: '',
});

const router = useRouter()
const errorMessage = ref('')
const submitting = ref(false)

const validationErrors = ref({})

const hasError = (field) => {
    return !!validationErrors.value[field]?.length
}
const clearFieldError = (field) => {
    if (validationErrors.value[field]) {
        delete validationErrors.value[field]
    }
}

const getFieldError = (field) => {
    return validationErrors.value[field]?.[0] || ''
}

const submitForm = async () => {
    submitting.value = true
    errorMessage.value = ''
    validationErrors.value = {}

    try {
        await api.post('/api/users', {
            name: form.name,
            email: form.email,
            password: form.password,
            job_title: form.job_title,
            organization: form.organization,
        })
        router.push('/users')
    } catch (error) {
        if (error.response?.status === 422) {
            validationErrors.value = error.response.data.errors || {}
            errorMessage.value = ''
            return
        }

        if (error.response?.status === 403) {
            errorMessage.value = 'You are not allowed to perform this action.'
            return
        }

        if (error.response?.status === 405 || error.response?.status === 500) {
            errorMessage.value = 'Something went wrong. Please try again.'
            return
        }

        errorMessage.value =
            error.response?.data?.message ||
            'An error occurred while creating the user.'
    } finally {
        submitting.value = false
    }
}

const logout = async () => {
    try {
        await api.post('/logout')
        router.push('/login')
    } catch (error) {
        console.error(error)
    }
}

const inputClass = (field) => {
    return [
        'w-full rounded-lg px-3 py-2 text-sm shadow-sm outline-none transition',
        hasError(field)
            ? 'border border-red-500 focus:ring-2 focus:ring-red-200'
            : 'border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200'
    ]
}

</script>

<template>
    <div class="mx-auto max-w-3xl px-4 py-8">
        <div class="mb-8 flex items-center justify-between">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">
                Create User
            </h1>

            <button
                @click="logout"
                type="button"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
            >
                Logout
            </button>
        </div>

        <div
            v-if="errorMessage"
            class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
        >
            {{ errorMessage }}
        </div>

        <form
            @submit.prevent="submitForm"
            class="space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm"
        >

            <div>
                <label for="name"
                       :class="[
                            'mb-2 block text-sm font-medium',
                            hasError('name') ? 'text-red-600' : 'text-gray-700'
                        ]"
                >
                    Name
                </label>
                <input
                    v-model="form.name"
                    type="text"
                    id="name"
                    @input="clearFieldError('name')"
                    :class="inputClass('name')"
                >
                <p v-if="getFieldError('name')" class="mt-1 text-sm text-red-600">
                    {{ getFieldError('name')}}
                </p>
            </div>

            <div>
                <label for="email"
                       :class="[
                            'mb-2 block text-sm font-medium',
                            hasError('email') ? 'text-red-600' : 'text-gray-700'
                        ]"
                >
                    Email
                </label>
                <input
                    v-model="form.email"
                    type="email"
                    id="email"
                    @input="clearFieldError('email')"
                    :class="inputClass('email')"
                >
                <p v-if="getFieldError('email')" class="mt-1 text-sm text-red-600">
                    {{ getFieldError('email')}}
                </p>
            </div>

            <div>
                <label for="password"
                       :class="[
                                'mb-2 block text-sm font-medium',
                                hasError('password') ? 'text-red-600' : 'text-gray-700'
                            ]"
                >
                    Password
                </label>
                <input
                    v-model="form.password"
                    type="password"
                    id="password"
                    @input="clearFieldError('password')"
                    :class="inputClass('password')"
                >
                <p v-if="getFieldError('password')" class="mt-1 text-sm text-red-600">
                    {{ getFieldError('password')}}
                </p>
            </div>
            <div>
                <label for="job_title"
                       :class="[
                            'mb-2 block text-sm font-medium',
                            hasError('job_title') ? 'text-red-600' : 'text-gray-700'
                        ]"
                >
                    Job title
                </label>
                <select
                    v-model="form.job_title"
                    id="job_title"
                    @change="clearFieldError('job_title')"
                    :class="inputClass('job_title')"
                >
                    <option value="" disabled>Select a job title</option>
                    <option value="admin">Admin</option>
                    <option value="doctor">Doctor</option>
                    <option value="nurse">Nurse</option>
                </select>
                <p v-if="getFieldError('job_title')" class="mt-1 text-sm text-red-600">
                    {{ getFieldError('job_title')}}
                </p>
            </div>
            <div>
                <label for="organization"
                       :class="[
                            'mb-2 block text-sm font-medium',
                            hasError('organization') ? 'text-red-600' : 'text-gray-700'
                        ]"
                >
                    Organization
                </label>
                <input
                    v-model="form.organization"
                    type="text"
                    id="organization"
                    @input="clearFieldError('organization')"
                    :class="inputClass('organization')"
                >
                <p v-if="getFieldError('organization')" class="mt-1 text-sm text-red-600">
                    {{ getFieldError('organization')}}
                </p>
            </div>
            <div class="flex items-center gap-3 pt-2">
                <button
                    type="submit"
                    :disabled="submitting"
                    class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ submitting ? 'Creating...' : 'Create User' }}
                </button>
            </div>
        </form>
    </div>
</template>



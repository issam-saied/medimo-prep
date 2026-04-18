<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const route = useRoute()

const form = reactive({
    name: '',
    birthdate: '',
})

const errorMessage = ref('')
const submitting = ref(false)
const loading = ref(true)
const validationErrors = ref({})

const hasError = (field) => !!validationErrors.value[field]?.length

const clearFieldError = (field) => {
    if (validationErrors.value[field]) {
        delete validationErrors.value[field]
    }
}

const getFieldError = (field) => validationErrors.value[field]?.[0] || ''

const inputClass = (field) => [
    'w-full rounded-lg px-3 py-2 text-sm shadow-sm outline-none transition',
    hasError(field)
        ? 'border border-red-500 focus:ring-2 focus:ring-red-200'
        : 'border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200',
]

const submitForm = async () => {
    submitting.value = true
    errorMessage.value = ''
    validationErrors.value = {}

    try {
        await api.put(`/api/patients/${route.params.id}`, {
            name: form.name,
            birthdate: form.birthdate,
        })
        router.push('/patients')
    } catch (error) {
        if (error.response?.status === 422) {
            validationErrors.value = error.response.data.errors || {}
            return
        }
        if (error.response?.status === 403) {
            errorMessage.value = 'You are not allowed to perform this action.'
            return
        }
        errorMessage.value =
            error.response?.data?.message || 'An error occurred while updating the patient.'
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

onMounted(async () => {
    try {
        const response = await api.get(`/api/patients/${route.params.id}`)
        const patient = response.data.data
        form.name = patient.name
        form.birthdate = patient.birthdate?.slice(0, 10) ?? ''
    } catch (error) {
        errorMessage.value = 'Failed to load patient data.'
    } finally {
        loading.value = false
    }
})
</script>

<template>
    <div class="mx-auto max-w-3xl px-4 py-8">
        <div class="mb-8 flex items-center justify-between">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">
                Edit Patient
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

        <div v-if="loading" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm text-sm text-gray-600">
            Loading patient data...
        </div>

        <form
            v-else
            @submit.prevent="submitForm"
            class="space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm"
        >
            <div>
                <label
                    for="name"
                    :class="['mb-2 block text-sm font-medium', hasError('name') ? 'text-red-600' : 'text-gray-700']"
                >
                    Name -
                </label>
                <input
                    v-model="form.name"
                    type="text"
                    id="name"
                    @input="clearFieldError('name')"
                    :class="inputClass('name')"
                >
                <p v-if="getFieldError('name')" class="mt-1 text-sm text-red-600">
                    {{ getFieldError('name') }}
                </p>
            </div>

            <div>
                <label
                    for="birthdate"
                    :class="['mb-2 block text-sm font-medium', hasError('birthdate') ? 'text-red-600' : 'text-gray-700']"
                >
                    Birthdate
                </label>
                <input
                    v-model="form.birthdate"
                    type="date"
                    id="birthdate"
                    @input="clearFieldError('birthdate')"
                    :class="inputClass('birthdate')"
                >
                <p v-if="getFieldError('birthdate')" class="mt-1 text-sm text-red-600">
                    {{ getFieldError('birthdate') }}
                </p>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button
                    type="submit"
                    :disabled="submitting"
                    class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ submitting ? 'Saving...' : 'Save Changes' }}
                </button>
                <button
                    type="button"
                    @click="router.push('/patients')"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                >
                    Cancel
                </button>
            </div>
        </form>
    </div>
</template>

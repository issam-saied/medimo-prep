<script setup>
import {onMounted, ref} from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const route = useRoute()

const note = ref('')

const errorMessage = ref('')
const submitting = ref(false)
const loadingFormData = ref(true)
const validationErrors = ref({})

const displayPatientName = ref('')
const displayPrescriptionLabel = ref('')
const displayStatus = ref('')
const displayAdministeredAt = ref('')

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
        await api.put(`/api/administrations/${route.params.id}`, {
            note: note.value,
        })
        router.push('/administrations')
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
            'An error occurred while updating the administration.'
    } finally {
        submitting.value = false
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

const prescriptionLabel = (prescription) => {
    return [
        prescription.medication?.name,
        prescription.dosage,
        prescription.frequency,
        prescription.status,
    ]
        .filter(Boolean)
        .join(' — ')
}

onMounted(async () => {
    loadingFormData.value = true

    try {
        const administrationRes = await api.get(`/api/administrations/${route.params.id}`)
        const administration = administrationRes.data.data

        displayPatientName.value = administration.prescription?.patient?.name
        displayPrescriptionLabel.value = prescriptionLabel(administration?.prescription)
        displayStatus.value = administration?.status
        displayAdministeredAt.value = administration?.administered_at
        note.value = administration.note
    } catch (error) {
        console.error('Failed to load administration:', error)
        errorMessage.value = 'Failed to load form data.'
    } finally {
        loadingFormData.value = false
    }
})
</script>

<template>
    <div class="mx-auto max-w-3xl px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">
                Update Administration
            </h1>
        </div>

        <div
            v-if="errorMessage"
            class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
        >
            {{ errorMessage }}
        </div>

        <div
            v-if="loadingFormData"
            class="rounded-xl border border-gray-200 bg-white p-6 text-sm text-gray-600 shadow-sm"
        >
            Loading form data...
        </div>

        <form
            v-else
            @submit.prevent="submitForm"
            class="space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm"
        >
            <div>
                <label for="patient_id"
                       class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Patient
                </label>
                <input
                    :value="displayPatientName"
                    id="patient_id"
                    disabled
                    class="w-full rounded-lg px-3 py-2 text-sm shadow-sm outline-none transition border border-gray-300 bg-gray-100 text-gray-500 cursor-not-allowed"
                >
            </div>

            <div>
                <label for="prescription_id"
                       class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Prescription
                </label>
                <input
                    :value="displayPrescriptionLabel"
                    id="prescription_id"
                    disabled
                    class="w-full rounded-lg px-3 py-2 text-sm shadow-sm outline-none transition border border-gray-300 bg-gray-100 text-gray-500 cursor-not-allowed"
                >
            </div>

            <div>
                <label for="status"
                       class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Status
                </label>
                <select
                    :value="displayStatus"
                    id="status"
                    disabled
                    class="w-full rounded-lg px-3 py-2 text-sm shadow-sm outline-none transition border border-gray-300 bg-gray-100 text-gray-500 cursor-not-allowed"
                >
                    <option value="given">Given</option>
                    <option value="missed">Missed</option>
                    <option value="refused">Refused</option>
                </select>
            </div>

            <div>
                <label for="note"
                       :class="[
                            'mb-2 block text-sm font-medium',
                            hasError('note') ? 'text-red-600' : 'text-gray-700'
                        ]"
                >
                    Note
                    <span v-if="displayStatus === 'missed' || displayStatus === 'refused'" class="text-red-500">*</span>
                </label>

                <textarea
                    v-model="note"
                    id="note"
                    @input="clearFieldError('note')"
                    :class="inputClass('note')"
                ></textarea>

                <p v-if="getFieldError('note')" class="mt-1 text-sm text-red-600">
                    {{ getFieldError('note')}}
                </p>
            </div>

            <div>
                <label for="administered_at"
                       class="mb-2 block text-sm font-medium text-gray-700"
                >
                Administered at
                </label>
                <input
                    :value="displayAdministeredAt"
                    type="datetime-local"
                    id="administered_at"
                    disabled
                    class="w-full rounded-lg px-3 py-2 text-sm shadow-sm outline-none transition border border-gray-300 bg-gray-100 text-gray-500 cursor-not-allowed"
                >
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button
                    type="submit"
                    :disabled="submitting"
                    class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ submitting ? 'Updating...' : 'Update Administration' }}
                </button>
            </div>
        </form>
    </div>
</template>



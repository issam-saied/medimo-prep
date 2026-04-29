<script setup>
import {computed, onMounted, reactive, ref, watch} from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../services/api'

const form = reactive({
    patient_name: '',
    medication_name: '',
    prescriber_name: '',
    nurse_id: '',
    dosage: '',
    frequency: '',
    status: 'active',
    start_date: '',
    end_date: '',
});

const nurses = ref([])

const router = useRouter()
const route = useRoute()
const errorMessage = ref('')
const submitting = ref(false)
const loadingFormData = ref(true)


const isEndDateRequired = computed(() => {
    return form.status === 'completed' || form.status === 'stopped'
})

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
        const { patient_name, medication_name, prescriber_name, ...payload } = form
        await api.put(`/api/prescriptions/${route.params.id}`, payload)
        router.push('/prescriptions')
    } catch (error) {
        if (error.response?.status === 422) {
            validationErrors.value = error.response.data.errors || {}
            errorMessage.value = ''
            return
        }

        if (error.response?.status === 405 || error.response?.status === 500) {
            errorMessage.value = 'Something went wrong. Please try again.'
            return
        }

        errorMessage.value =
            error.response?.data?.message ||
            'An error occurred while updating the prescription.'
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

onMounted(async () => {
    loadingFormData.value = true
    try {
        const [prescriptionRes, nursesRes] = await Promise.all([
            api.get(`/api/prescriptions/${route.params.id}`),
            api.get('/api/nurse-options'),
        ])

        const prescription = prescriptionRes.data.data
        nurses.value = nursesRes.data.data

        form.patient_name = prescription.patient?.name
        form.medication_name = prescription.medication?.name + ' ' + prescription.medication?.strength
        form.prescriber_name = prescription.prescriber?.name
        form.nurse_id = prescription.nurse?.id ?? ''
        form.dosage = prescription.dosage
        form.frequency = Number(prescription.frequency)
        form.status = prescription.status
        form.start_date = prescription.start_date?.slice(0, 10) ?? ''
        form.end_date = prescription.end_date?.slice(0, 10) ?? ''
    } catch (error) {
        console.error(error)
        errorMessage.value = 'Failed to load prescription data.'
    } finally {
        loadingFormData.value = false
    }
})

watch(() => form.status, (newStatus) => {
    if (newStatus === 'active') {
        form.end_date = ''
        clearFieldError('end_date')
    }
})


</script>

<template>
    <div class="mx-auto max-w-3xl px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">
                Update Prescription
            </h1>
        </div>

        <div
            v-if="errorMessage"
            class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
        >
            {{ errorMessage }}
        </div>

        <div v-if="loadingFormData" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            Loading form data...
        </div>

        <form
            @submit.prevent="submitForm"
            class="space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm"
        >
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Patient</label>
                <p class="text-sm text-gray-500 bg-gray-50 border border-gray-200 rounded px-3 py-2">{{ form.patient_name }}</p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Medication</label>
                <p class="text-sm text-gray-500 bg-gray-50 border border-gray-200 rounded px-3 py-2">{{ form.medication_name }}</p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Prescriber</label>
                <p class="text-sm text-gray-500 bg-gray-50 border border-gray-200 rounded px-3 py-2">{{ form.prescriber_name }}</p>
            </div>

            <div>
                <label for="nurse_id"
                       :class="[
                             'mb-2 block text-sm font-medium',
                             hasError('nurse_id') ? 'text-red-600' : 'text-gray-700'
                       ]"
                >
                    Responsible Nurse
                </label>
                <select
                    v-model="form.nurse_id"
                    id="nurse_id"
                    @change="clearFieldError('nurse_id')"
                    :class="inputClass('nurse_id')"
                >
                    <option value="" disabled>Select a nurse</option>
                    <option v-for="nurse in nurses" :key="nurse.id" :value="nurse.id">
                        {{ nurse.name }}
                    </option>
                </select>
                <p v-if="getFieldError('nurse_id')" class="mt-1 text-sm text-red-600">
                    {{ getFieldError('nurse_id') }}
                </p>
            </div>

            <div>
                <label for="dosage"
                       :class="[
                            'mb-2 block text-sm font-medium',
                            hasError('dosage') ? 'text-red-600' : 'text-gray-700'
                        ]"
                >
                    Dosage
                </label>
                <input
                    v-model="form.dosage"
                    type="text"
                    id="dosage"
                    @input="clearFieldError('dosage')"
                    :class="inputClass('dosage')"
                    required
                >
                <p v-if="getFieldError('dosage')" class="mt-1 text-sm text-red-600">
                    {{ getFieldError('dosage') }}
                </p>
            </div>

            <div>
                <label for="frequency"
                       :class="[
                            'mb-2 block text-sm font-medium',
                            hasError('frequency') ? 'text-red-600' : 'text-gray-700'
                        ]"
                >
                Frequency x day
</label>
                <input
                    v-model="form.frequency"
                    type="number"
                    min="1"
                    max="24"
                    id="frequency"
                    @input="clearFieldError('frequency')"
                    :class="inputClass('frequency')"
                    required
                >
                <p v-if="getFieldError('frequency')" class="mt-1 text-sm text-red-600">
                    {{ getFieldError('frequency')}}
                </p>
            </div>

            <div>
                <label for="status"
                       :class="[
                            'mb-2 block text-sm font-medium',
                            hasError('status') ? 'text-red-600' : 'text-gray-700'
                        ]"
                >
                    Status
                </label>
                <select
                    v-model="form.status"
                    id="status"
                    @change="clearFieldError('status')"
                    :class="inputClass('status')"
                    required
                >
                    <option value="active">Active</option>
                    <option value="completed">Completed</option>
                    <option value="stopped">Stopped</option>
                </select>
                <p v-if="getFieldError('status')" class="mt-1 text-sm text-red-600">
                    {{ getFieldError('status') }}
                </p>
            </div>

            <div>
                <label for="start_date"
                       :class="[
                            'mb-2 block text-sm font-medium',
                            hasError('start_date') ? 'text-red-600' : 'text-gray-700'
                        ]"
                >
                Start Date
                </label>
                <input
                    v-model="form.start_date"
                    type="date"
                    id="start_date"
                    @input="clearFieldError('start_date')"
                    :class="inputClass('start_date')"
                    required
                >
                <p v-if="getFieldError('start_date')" class="mt-1 text-sm text-red-600">
                    {{ getFieldError('start_date') }}
                </p>
            </div>

            <div v-if="isEndDateRequired">
                <label for="end_date"
                       :class="[
                             'mb-2 block text-sm font-medium',
                             hasError('end_date') ? 'text-red-600' : 'text-gray-700'
                       ]"
                >
                    End Date
                </label>
                <input
                    v-model="form.end_date"
                    type="date"
                    id="end_date"
                    @input="clearFieldError('end_date')"
                    :class="inputClass('end_date')"
                    required
                >
                <p v-if="getFieldError('end_date')" class="mt-1 text-sm text-red-600">
                    {{ getFieldError('end_date') }}
                </p>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button
                    type="submit"
                    :disabled="submitting"
                    class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ submitting ? 'Updating...' : 'Update Prescription' }}
                </button>
            </div>
        </form>
    </div>
</template>



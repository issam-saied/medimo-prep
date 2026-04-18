<script setup>
import {computed, onMounted, reactive, ref, watch} from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const form = reactive({
    patient_id: '',
    prescription_id: '',
    status: 'given',
    administered_at: '',
    note: '',
})

const router = useRouter()
const errorMessage = ref('')
const submitting = ref(false)
const loadingFormData = ref(true)

const patients = ref([])
const prescriptions= ref([])

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
        await api.post('/api/administrations', {
            prescription_id: form.prescription_id,
            status: form.status,
            administered_at: form.administered_at,
            note: form.note,
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
            'An error occurred while creating the administration.'
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
        const patientsRes = await api.get('/api/patient-options')
        patients.value = patientsRes.data.data
    } catch (error) {
        console.error('Error fetching patient options:', error)
        errorMessage.value = 'Failed to load form data.'
    } finally {
        loadingFormData.value = false
    }
})

watch(() => form.patient_id, async (newPatientId) => {
    form.prescription_id = ''
    prescriptions.value = []

    clearFieldError('patient_id')
    clearFieldError('prescription_id')

    if (!newPatientId) {
        return
    }

    try {
        const prescriptionsRes = await api.get('/api/prescription-options', {
            params: {
                patient_id: newPatientId,
            },
        })

        prescriptions.value = prescriptionsRes.data.data;
    } catch (error) {
        console.error('Error fetching prescription options:', error)
        errorMessage.value = 'Failed to load prescriptions.'
    }
})

watch(() => form.status, (newStatus) => {
    if (newStatus === 'given') {
        clearFieldError('note')
    }
})

</script>

<template>
    <div class="mx-auto max-w-3xl px-4 py-8">
        <div class="mb-8 flex items-center justify-between">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">
                Create Administration
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
                <label
                    for="patient_id"
                    :class="[
            'mb-2 block text-sm font-medium',
            hasError('patient_id') ? 'text-red-600' : 'text-gray-700'
        ]"
                >
                    Patient
                </label>

                <select
                    v-model="form.patient_id"
                    id="patient_id"
                    @change="clearFieldError('patient_id')"
                    :class="inputClass('patient_id')"
                >
                    <option value="" disabled>Select a patient</option>
                    <option v-for="patient in patients" :key="patient.id" :value="patient.id">
                        {{ patient.name }}
                    </option>
                </select>

                <p v-if="getFieldError('patient_id')" class="mt-1 text-sm text-red-600">
                    {{ getFieldError('patient_id') }}
                </p>
            </div>

            <div>
                <label for="prescription_id"
                       :class="[
                             'mb-2 block text-sm font-medium',
                             hasError('prescription_id') ? 'text-red-600' : 'text-gray-700'
                       ]"
                >
                    Prescription
                </label>
                <select
                    v-model="form.prescription_id"
                    id="prescription_id"
                    @change="clearFieldError('prescription_id')"
                    :disabled="!form.patient_id"
                    :class="[
                        ...inputClass('prescription_id'),
                        !form.patient_id ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : ''
                    ]"
                >
                    <option value="" disabled>
                        {{ form.patient_id ? 'Select a prescription' : 'Select a patient first' }}
                    </option>

                    <option
                        v-for="prescription in prescriptions"
                        :key="prescription.id"
                        :value="prescription.id"
                    >
                        {{ prescriptionLabel(prescription) }}
                    </option>
                </select>
                <p v-if="getFieldError('prescription_id')" class="mt-1 text-sm text-red-600">
                    {{ getFieldError('prescription_id') }}
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
                >
                    <option value="given">Given</option>
                    <option value="missed">Missed</option>
                    <option value="refused">Refused</option>
                </select>
                <p v-if="getFieldError('status')" class="mt-1 text-sm text-red-600">
                    {{ getFieldError('status') }}
                </p>
            </div>

            <div>
                <label for="note"
                       :class="[
                            'mb-2 block text-sm font-medium',
                            hasError('note') ? 'text-red-600' : 'text-gray-700'
                        ]"
                >
                    Note
                    <span v-if="form.status === 'missed' || form.status === 'refused'" class="text-red-500">*</span>
                </label>

                <textarea
                    v-model="form.note"
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
                       :class="[
                            'mb-2 block text-sm font-medium',
                            hasError('administered_at') ? 'text-red-600' : 'text-gray-700'
                        ]"
                >
                Administered at
                </label>
                <input
                    v-model="form.administered_at"
                    type="datetime-local"
                    id="administered_at"
                    @input="clearFieldError('administered_at')"
                    :class="inputClass('administered_at')"
                >
                <p v-if="getFieldError('administered_at')" class="mt-1 text-sm text-red-600">
                    {{ getFieldError('administered_at') }}
                </p>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button
                    type="submit"
                    :disabled="submitting"
                    class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ submitting ? 'Creating...' : 'Create Administration' }}
                </button>
            </div>
        </form>
    </div>
</template>



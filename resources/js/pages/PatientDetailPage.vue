<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../services/api'

const route = useRoute()
const router = useRouter()

const patient = ref(null)
const loading = ref(true)
const error = ref(null)

const statusColors = {
    active: 'bg-green-100 text-green-800',
    completed: 'bg-blue-100 text-blue-800',
    stopped: 'bg-gray-100 text-gray-800',
    given: 'bg-green-100 text-green-800',
    missed: 'bg-yellow-100 text-yellow-800',
    refused: 'bg-red-100 text-red-800',
}

onMounted(async () => {
    try {
        const res = await api.get(`/api/patients/${route.params.id}`)
        patient.value = res.data.data
    } catch {
        error.value = 'Patient not found.'
    } finally {
        loading.value = false
    }
})
</script>

<template>
    <div class="max-w-4xl mx-auto">

        <button
            @click="router.push({ name: 'patients' })"
            class="mb-6 text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1"
        >
            ← Back to patients
        </button>

        <div v-if="loading" class="text-gray-500">Loading...</div>
        <div v-else-if="error" class="text-red-600">{{ error }}</div>

        <template v-else-if="patient">

            <!-- Patient header -->
            <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
                <h1 class="text-2xl font-bold text-gray-800">{{ patient.name }}</h1>
                <p class="text-sm text-gray-500 mt-1">Date of birth: {{ patient.birthdate?.slice(0, 10) }}</p>
            </div>

            <!-- Prescriptions -->
            <h2 class="text-lg font-semibold text-gray-700 mb-3">Prescriptions</h2>

            <div v-if="!patient.prescriptions.length" class="text-gray-500 text-sm">No prescriptions found.</div>

            <div
                v-for="prescription in patient.prescriptions"
                :key="prescription.id"
                class="bg-white rounded-lg border border-gray-200 mb-4 overflow-hidden"
            >
                <!-- Prescription header -->
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <div>
                        <span class="font-semibold text-gray-800">{{ prescription.medication.name }}</span>
                        <span class="text-gray-500 text-sm ml-2">{{ prescription.medication.strength }} {{ prescription.medication.unit }}</span>
                    </div>
                    <span
                        class="text-xs font-medium px-2 py-1 rounded-full"
                        :class="statusColors[prescription.status]"
                    >
                        {{ prescription.status }}
                    </span>
                </div>

                <!-- Prescription details -->
                <div class="px-5 py-3 grid grid-cols-2 gap-x-8 gap-y-1 text-sm text-gray-600 border-b border-gray-100">
                    <div><span class="font-medium">Dosage:</span> {{ prescription.dosage }}</div>
                    <div><span class="font-medium">Frequency:</span> {{ prescription.frequency }}x/day</div>
                    <div><span class="font-medium">Start:</span> {{ prescription.start_date?.slice(0, 10) }}</div>
                    <div><span class="font-medium">End:</span> {{ prescription.end_date?.slice(0, 10) ?? '—' }}</div>
                    <div><span class="font-medium">Prescriber:</span> {{ prescription.prescriber.name }}</div>
                </div>

                <!-- Administrations -->
                <div class="px-5 py-3">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Administrations</p>

                    <div v-if="!prescription.administrations.length" class="text-sm text-gray-400">No administrations recorded.</div>

                    <table v-else class="w-full text-sm table-fixed">
                        <thead>
                            <tr class="text-left text-gray-500 text-xs border-b">
                                <th class="pb-1 pr-4 font-medium w-2/8">Date & time</th>
                                <th class="pb-1 pr-4 font-medium w-1/8">Status</th>
                                <th class="pb-1 pr-4 font-medium w-2/8">Nurse</th>
                                <th class="pb-1 font-medium w-3/8">Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="adm in prescription.administrations"
                                :key="adm.id"
                                class="border-b border-gray-50 last:border-0"
                            >
                                <td class="py-1 pr-4 text-gray-600">{{ adm.administered_at?.slice(0, 16).replace('T', ' ') }}</td>
                                <td class="py-1 pr-4">
                                    <span
                                        class="text-xs font-medium px-2 py-0.5 rounded-full"
                                        :class="statusColors[adm.status]"
                                    >
                                        {{ adm.status }}
                                    </span>
                                </td>
                                <td class="py-1 pr-4 text-gray-600">{{ adm.user.name }}</td>
                                <td class="py-1 text-gray-500">{{ adm.note ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </template>
    </div>
</template>

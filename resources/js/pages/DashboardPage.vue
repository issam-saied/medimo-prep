<script setup>
import { ref, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const date = ref(new Date().toISOString().slice(0, 10))
const patientName = ref('')
const prescriptions = ref([])
const loading = ref(false)
const errorMessage = ref('')
const currentUserRole = ref('')

const loadDashboard = async () => {
    loading.value = true
    errorMessage.value = ''

    try {
        const response = await api.get('/api/dashboard', {
            params: {
                date: date.value,
                patient_name: patientName.value || undefined,
            },
        })
        prescriptions.value = response.data.data
    } catch (error) {
        errorMessage.value = 'Failed to load dashboard data.'
    } finally {
        loading.value = false
    }
}

let debounceTimer = null
watch([date, patientName], () => {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => loadDashboard(), 500)
})

onMounted(async () => {
    const meRes = await api.get('/api/me')
    currentUserRole.value = meRes.data.user.role
    loadDashboard()
})
</script>

<template>
    <div class="p-10">
        <h1 class="text-xl font-bold mb-6">Dashboard</h1>

        <div class="flex flex-wrap gap-4 mb-6">
            <input
                v-model="date"
                type="date"
                class="px-4 py-2 border rounded"
            />
            <input
                v-model="patientName"
                type="text"
                placeholder="Filter by patient name"
                class="px-4 py-2 border rounded"
            />
        </div>

        <p v-if="loading">Loading...</p>
        <p v-if="errorMessage" class="text-red-600">{{ errorMessage }}</p>

        <table class="w-full border border-gray-300" v-if="!loading && prescriptions.length">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-3 py-2">ID</th>
                    <th class="border px-3 py-2">Patient</th>
                    <th class="border px-3 py-2">Medication</th>
                    <th class="border px-3 py-2">Frequency</th>
                    <th class="border px-3 py-2">Given today</th>
                    <th class="border px-3 py-2">Remaining</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="row in prescriptions" :key="row.id"
                    :class="row.administered_by_me ? 'bg-green-50' : ''"
                >
                    <td class="border px-3 py-2">{{ row.id }}</td>
                    <td class="border px-3 py-2">{{ row.patient_name }}</td>
                    <td class="border px-3 py-2">{{ row.medication }}</td>
                    <td class="border px-3 py-2">{{ row.frequency }}x per day</td>
                    <td class="border px-3 py-2">{{ row.administered_count }}</td>
                    <td class="border px-3 py-2">
                        <span :class="row.remaining > 0 ? 'text-red-600 font-semibold' : 'text-green-600 font-semibold'">
                            {{ row.remaining > 0 ? row.remaining + ' remaining' : 'Done' }}
                        </span>
                        <button
                            v-if="currentUserRole === 'nurse' && row.remaining > 0"
                            @click="$router.push({ name: 'administrations.create', query: { prescription_id: row.id } })"
                            class="ml-2 px-2 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600"
                        >
                            + Administer
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <p v-if="!loading && !prescriptions.length">No active prescriptions for this date.</p>
    </div>
</template>

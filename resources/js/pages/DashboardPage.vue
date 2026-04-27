<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const date = ref(new Date().toISOString().slice(0, 10))
const patientName = ref('')
const prescriptions = ref([])
const stats = ref(null)
const recentActivity = ref([])
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
        stats.value = response.data.stats
        recentActivity.value = response.data.recent_activity
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

    window.Echo.channel('dashboard')
        .listen('.administration.changed', () => {
            loadDashboard()
        })
})

onUnmounted(() => {
    window.Echo.leaveChannel('dashboard')
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

        <!-- Stat cards -->
        <div v-if="stats" class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Active prescriptions</p>
                <p class="text-2xl font-bold text-gray-800">{{ stats.active_prescriptions }}</p>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Total patients</p>
                <p class="text-2xl font-bold text-gray-800">{{ stats.total_patients }}</p>
            </div>
            <div v-if="date === new Date().toISOString().slice(0, 10)" class="bg-white rounded-lg border border-gray-200 p-4">
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Remaining today</p>
                <p class="text-2xl font-bold" :class="stats.remaining_today > 0 ? 'text-red-600' : 'text-green-600'">
                    {{ stats.remaining_today }}
                </p>
            </div>
            <div class="bg-green-50 rounded-lg border border-green-200 p-4">
                <p class="text-xs text-green-700 uppercase tracking-wide mb-1">Given today</p>
                <p class="text-2xl font-bold text-green-700">{{ stats.given_today }}</p>
            </div>
            <div class="bg-yellow-50 rounded-lg border border-yellow-200 p-4">
                <p class="text-xs text-yellow-700 uppercase tracking-wide mb-1">Missed today</p>
                <p class="text-2xl font-bold text-yellow-700">{{ stats.missed_today }}</p>
            </div>
            <div class="bg-red-50 rounded-lg border border-red-200 p-4">
                <p class="text-xs text-red-700 uppercase tracking-wide mb-1">Refused today</p>
                <p class="text-2xl font-bold text-red-700">{{ stats.refused_today }}</p>
            </div>
        </div>

        <!-- Recent activity -->
        <div v-if="recentActivity.length" class="bg-white rounded-lg border border-gray-200 mb-6">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide px-5 pt-4 pb-2">Recent activity</p>
            <div
                v-for="item in recentActivity"
                :key="item.id"
                class="flex items-center justify-between px-5 py-3 border-t border-gray-100"
            >
                <div class="text-sm">
                    <span class="font-medium text-gray-800">{{ item.patient }}</span>
                    <span class="text-gray-500"> — {{ item.medication }}</span>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    <span
                        class="text-xs font-medium px-2 py-0.5 rounded-full"
                        :class="{
                            'bg-green-100 text-green-800': item.status === 'given',
                            'bg-yellow-100 text-yellow-800': item.status === 'missed',
                            'bg-red-100 text-red-800': item.status === 'refused',
                        }"
                    >{{ item.status }}</span>
                    <span class="text-gray-400">{{ item.nurse }}</span>
                    <span class="text-gray-400">{{ item.administered_at?.slice(0, 16).replace('T', ' ') }}</span>
                </div>
            </div>
        </div>

        <p v-if="loading">Loading...</p>
        <p v-if="errorMessage" class="text-red-600">{{ errorMessage }}</p>

        <!-- Prescriptions table -->
        <div v-if="!loading && prescriptions.length" class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide px-5 pt-4 pb-2">Active prescriptions</p>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 border-t border-gray-100">
                        <th class="px-5 py-3 font-medium">Patient</th>
                        <th class="px-5 py-3 font-medium">Medication</th>
                        <th class="px-5 py-3 font-medium">Frequency</th>
                        <th class="px-5 py-3 font-medium">Progress</th>
                        <th class="px-5 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="row in prescriptions"
                        :key="row.id"
                        class="border-t border-gray-100"
                        :class="row.administered_by_me ? 'bg-green-50' : 'hover:bg-gray-50'"
                    >
                        <td class="px-5 py-3 font-medium text-gray-800">{{ row.patient_name }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ row.medication }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ row.frequency }}x / day</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div
                                        class="h-2 rounded-full transition-all"
                                        :class="row.remaining === 0 ? 'bg-green-500' : 'bg-blue-500'"
                                        :style="{ width: (row.administered_count / row.frequency * 100) + '%' }"
                                    ></div>
                                </div>
                                <span class="text-xs" :class="row.remaining > 0 ? 'text-gray-500' : 'text-green-600 font-semibold'">
                                    {{ row.remaining === 0 ? 'Done' : row.administered_count + ' / ' + row.frequency }}
                                </span>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <button
                                v-if="currentUserRole === 'nurse' && row.remaining > 0"
                                @click="$router.push({ name: 'administrations.create', query: { prescription_id: row.id } })"
                                class="px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600"
                            >
                                + Administer
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p v-if="!loading && !prescriptions.length" class="text-gray-500 text-sm">No active prescriptions for this date.</p>
    </div>
</template>

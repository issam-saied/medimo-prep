<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import { useDataTable } from '../composables/useDataTable'

const router = useRouter()

const {
    rows: logs,
    loading,
    errorMessage,
    paginationMeta,
    loadData: loadLogs,
} = useDataTable({
    endpoint: '/api/activity-logs',
    defaultSortField: 'id',
    buildFilterParams: () => ({}),
})


const actionClass = (action) => {
    if (action.includes('deleted')) return 'bg-red-100 text-red-700'
    if (action.includes('created')) return 'bg-green-100 text-green-700'
    if (action.includes('login') || action.includes('logout')) return 'bg-blue-100 text-blue-700'
    return 'bg-gray-100 text-gray-700'
}

onMounted(async () => {
    await loadLogs()
})
</script>

<template>
    <div class="p-10">
        <h1 class="text-xl font-bold mb-6">Activity Logs</h1>

        <div>
            <p v-if="loading">Loading...</p>
            <p v-if="errorMessage" class="text-red-600">{{ errorMessage }}</p>
        </div>

        <table class="w-full border border-gray-300" v-if="!loading && logs.length">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-3 py-2">Date</th>
                    <th class="border px-3 py-2">Action</th>
                    <th class="border px-3 py-2">Subject</th>
                    <th class="border px-3 py-2">Description</th>
                    <th class="border px-3 py-2">Changes</th>
                    <th class="border px-3 py-2">User</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="log in logs" :key="log.id">
                    <td class="border px-3 py-2 whitespace-nowrap">{{ log.created_at }}</td>
                    <td class="border px-3 py-2">
                        <span :class="actionClass(log.action)" class="px-2 py-0.5 rounded text-xs font-medium">
                            {{ log.action }}
                        </span>
                    </td>
                    <td class="border px-3 py-2">{{ log.subject_type }} #{{ log.subject_id }}</td>
                    <td class="border px-3 py-2">{{ log.description || '-' }}</td>
                    <td class="border px-3 py-2 text-xs text-gray-600">
                        <div v-if="log.changes">
                            <div v-for="(change, field) in log.changes" :key="field">
                                <span class="font-medium">{{ field }}:</span>
                                <span class="text-red-500"> {{ change.from }}</span>
                                <span> → </span>
                                <span class="text-green-600">{{ change.to }}</span>
                            </div>
                        </div>
                        <span v-else class="text-gray-400">-</span>
                    </td>
                    <td class="border px-3 py-2">{{ log.user_name }}</td>
                </tr>
            </tbody>
        </table>

        <div v-if="paginationMeta" class="flex gap-2 mt-4 pb-2">
            <button
                v-for="link in paginationMeta.links"
                :key="`${link.label}-${link.page}`"
                :disabled="!link.url"
                :class="[
                    'px-3 py-1 rounded border',
                    link.active ? 'bg-blue-600 text-white' : 'bg-gray-100',
                    !link.url ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-200'
                ]"
                @click="link.url && loadLogs(link.page)"
            >
                <span v-html="link.label"></span>
            </button>
        </div>

        <p v-if="!loading && !logs.length">No activity logs found.</p>
    </div>
</template>

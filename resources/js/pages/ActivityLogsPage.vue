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
                    <th class="border px-3 py-2">User</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="log in logs" :key="log.id">
                    <td class="border px-3 py-2 whitespace-nowrap">{{ log.created_at }}</td>
                    <td class="border px-3 py-2">{{ log.action }}</td>
                    <td class="border px-3 py-2">{{ log.subject_type }} #{{ log.subject_id }}</td>
                    <td class="border px-3 py-2">{{ log.description || '-' }}</td>
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

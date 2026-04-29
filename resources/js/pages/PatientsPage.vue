<script setup>
import { onMounted, ref, watch} from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import { useDataTable } from '../composables/useDataTable'


const nameFilter = ref('')
const birthDateFilter = ref('')

const router = useRouter()

const normalizeSearchValue = (value) => {
    const trimmedValue = value.trim()

    if (trimmedValue.length >= 2) {
        return trimmedValue
    }

    return undefined
}

const {
    rows: patients,
    loading,
    errorMessage,
    paginationMeta,
    sortField,
    sortDirection,
    loadData: loadPatients,
    toggleSortDirection,
} = useDataTable({
    endpoint: '/api/patients',
    defaultSortField: 'id',
    buildFilterParams: () => ({
        name: normalizeSearchValue(nameFilter.value),
        birthdate: normalizeSearchValue(birthDateFilter.value),
    }),
})

const exportData = async (format) => {
    const params = new URLSearchParams({ format })
    const name = nameFilter.value.trim()
    const birthdate = birthDateFilter.value.trim()
    if (name.length >= 2) params.set('name', name)
    if (birthdate.length >= 2) params.set('birthdate', birthdate)

    const response = await api.get(`/api/patients/export?${params}`, { responseType: 'blob' })
    const url = URL.createObjectURL(response.data)
    const a = document.createElement('a')
    a.href = url
    a.download = `patients.${format}`
    a.click()
    URL.revokeObjectURL(url)
}

const deletePatient = async (patient) => {
    if (!confirm(`Delete patient "${patient.name}"? This cannot be undone.`)) return
    try {
        await api.delete(`/api/patients/${patient.id}`)
        await loadPatients()
    } catch {
        alert('Failed to delete patient.')
    }
}

onMounted(async () => {
    await loadPatients()
})

let debounceTimer = null

watch(
    [nameFilter, birthDateFilter],
    () => {
        clearTimeout(debounceTimer)

        debounceTimer = setTimeout(() => {
            loadPatients(1)
        }, 500)
    }
)

</script>

<template>
    <div class="p-10">

        <h1 class="text-xl font-bold mb-6">Patients</h1>

        <div class="flex flex-wrap gap-4 mb-6">

            <input v-model="nameFilter"
                   type="text"
                   placeholder="Filter by patient name"
                   class="px-4 py-2 border rounded"
            />
            <input
                    v-model="birthDateFilter"
                    type="date"
                    class="px-4 py-2 border rounded"
            />
            <button @click="loadPatients(1)"
                    class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                Filter
            </button>
            <button @click="$router.push({ name: 'patients.create' })"
                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
            >
                New Patient
            </button>
            <button @click="exportData('csv')"
                    class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                Export CSV
            </button>
            <button @click="exportData('pdf')"
                    class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                Export PDF
            </button>
        </div>

        <div>
            <p v-if="loading">Loading...</p>

            <p v-if="errorMessage" class="text-red-600">
                {{ errorMessage }}
            </p>
        </div>

        <table
            class="w-full border border-gray-300"
            v-if="!loading && patients.length"
        >

            <thead class="bg-gray-100">
            <tr>
                <th class="border px-3 py-2">ID</th>
                <th
                    @click="toggleSortDirection('name')"
                    class="cursor-pointer select-none border px-3 py-2 hover:text-blue-600"
                >
                    Patient name
                    <span v-if="sortField === 'name'" class="ml-1">
                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                </th>

                <th
                    @click="toggleSortDirection('birthdate')"
                    class="cursor-pointer select-none border px-3 py-2 hover:text-blue-600"
                >
                    Birthdate
                    <span v-if="sortField === 'birthdate'" class="ml-1">
                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                </th>
                <th class="border px-3 py-2">Actions</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="patient in patients" :key="patient.id">
                <td class="border px-3 py-2">{{ patient.id }}</td>
                <td class="border px-3 py-2">{{ patient.name || '-' }}</td>
                <td class="border px-3 py-2">{{ patient.birthdate?.slice(0, 10) }}</td>
                <td class="border px-3 py-2 flex gap-2">
                    <button
                        @click="$router.push({ name: 'patients.show', params: { id: patient.id } })"
                        class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm"
                    >
                        View
                    </button>
                    <button
                        @click="$router.push({ name: 'patients.edit', params: { id: patient.id } })"
                        class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-sm"
                    >
                        Edit
                    </button>
                    <button
                        @click="deletePatient(patient)"
                        class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm"
                    >
                        Delete
                    </button>
                </td>
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
            link.active
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100',
            !link.url
                ? 'opacity-50 cursor-not-allowed'
                : 'hover:bg-blue-200'
        ]"
                @click="link.url && loadPatients(link.page)"
            >
                <span v-html="link.label"></span>
            </button>
        </div>

        <p v-if="!loading && !patients.length">
            No patients found.
        </p>
    </div>
</template>

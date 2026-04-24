<script setup>
import { onMounted, ref, watch} from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import { useDataTable } from '../composables/useDataTable'


const nameFilter = ref('')
const formFilter = ref('')

const router = useRouter()

const normalizeSearchValue = (value) => {
    const trimmedValue = value.trim()

    if (trimmedValue.length >= 2) {
        return trimmedValue
    }

    return undefined
}

const {
    rows: medications,
    loading,
    errorMessage,
    paginationMeta,
    sortField,
    sortDirection,
    loadData: loadMedications,
    toggleSortDirection,
} = useDataTable({
    endpoint: '/api/medications',
    defaultSortField: 'id',
    buildFilterParams: () => ({
        name: normalizeSearchValue(nameFilter.value),
        form: normalizeSearchValue(formFilter.value),
    }),
})

const deleteMedication = async (medication) => {
    if (!confirm(`Delete medication "${medication.name}"? This cannot be undone.`)) return
    try {
        await api.delete(`/api/medications/${medication.id}`)
        await loadMedications()
    } catch {
        alert('Failed to delete medication.')
    }
}

onMounted(async () => {
    await loadMedications()
})

let debounceTimer = null

watch(
    [nameFilter, formFilter],
    () => {
        clearTimeout(debounceTimer)

        debounceTimer = setTimeout(() => {
            loadMedications(1)
        }, 500)
    }
)

</script>

<template>
    <div class="p-10">

        <h1 class="text-xl font-bold mb-6">Medications</h1>

        <div class="flex flex-wrap gap-4 mb-6">

            <input v-model="nameFilter"
                   type="text"
                   placeholder="Filter by medication name"
                   class="px-4 py-2 border rounded"
            />
            <input
                    v-model="formFilter"
                    type="text"
                    placeholder="Filter by medication form"
                    class="px-4 py-2 border rounded"
            />
            <button @click="loadMedications(1)"
                    class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                Filter
            </button>
            <button @click="$router.push({ name: 'medications.create' })"
                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
            >
                New Medication
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
            v-if="!loading && medications.length"
        >

            <thead class="bg-gray-100">
            <tr>
                <th class="border px-3 py-2">ID</th>
                <th
                    @click="toggleSortDirection('name')"
                    class="cursor-pointer select-none border px-3 py-2 hover:text-blue-600"
                >
                     Name
                    <span v-if="sortField === 'name'" class="ml-1">
                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                </th>
                <th
                    @click="toggleSortDirection('form')"
                    class="cursor-pointer select-none border px-3 py-2 hover:text-blue-600"
                >
                    Form
                    <span v-if="sortField === 'form'" class="ml-1">
                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                </th>
                <th class="border px-3 py-2">Strength</th>
                <th class="border px-3 py-2">Unit</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="medication in medications" :key="medication.id"
                class="cursor-pointer hover:bg-gray-50"
            >
                <td class="border px-3 py-2">{{ medication.id }}</td>
                <td class="border px-3 py-2">{{ medication.name || '-' }}</td>
                <td class="border px-3 py-2">{{ medication.form || '-' }}</td>
                <td class="border px-3 py-2">{{ medication.strength || '-' }}</td>
                <td class="border px-3 py-2">{{ medication.unit || '-' }}</td>
                <td class="border px-3 py-2 flex gap-2">
                    <button
                        @click="$router.push({ name: 'medications.edit', params: { id: medication.id } })"
                        class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-sm"
                    >
                        Edit
                    </button>
                    <button
                        @click="deleteMedication(medication)"
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
                @click="link.url && loadMedications(link.page)"
            >
                <span v-html="link.label"></span>
            </button>
        </div>

        <p v-if="!loading && !medications.length">
            No medications found.
        </p>
    </div>
</template>

<script setup>
import { onMounted, ref, watch} from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const statusFilter = ref('')
const patientNameFilter = ref('')
const prescriberNameFilter = ref('')
const medicationNameFilter = ref('')

const router = useRouter()
const prescriptions = ref([])
const paginationMeta = ref(null)
const loading = ref(false)
const errorMessage = ref('')

const sortDirection = ref('desc')
const sortField = ref('start_date')
const toggleSortDirection = (field) => {
    // if the user clicks the same field, toggle the direction,
    // otherwise set to new field and default to ascending
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortField.value = field
        sortDirection.value = 'asc'
    }

    loadPrescriptions(1)
}

const normalizeSearchValue = (value) => {
    const trimmedValue = value.trim()

    if (trimmedValue.length >= 2) {
        return trimmedValue
    }

    return undefined
}

const loadPrescriptions = async (page = 1) => {
    loading.value = true
    errorMessage.value = ''

    try {

        const response = await api.get('/api/prescriptions', {

            // This approach is more concise and avoids the need for the spread operator
            /*params: {
                ...(statusFilter.value && { status: statusFilter.value }),
                ...(patientNameFilter.value && { patient_name: patientNameFilter.value }),
            }*/
            params: {
                page: page,
                sort_direction: sortDirection.value,
                sort_field: sortField.value,
                status: statusFilter.value || undefined,
                patient_name: normalizeSearchValue(patientNameFilter.value),
                prescriber_name: normalizeSearchValue(prescriberNameFilter.value),
                medication_name: normalizeSearchValue(medicationNameFilter.value),
            },
        })

        console.log('Prescriptions response:', response.data);
        prescriptions.value = response.data.data
        paginationMeta.value = response.data.meta
    } catch (error) {
        errorMessage.value =
            error.response?.data?.message || 'Could not load prescriptions'
        console.error(error)
    } finally {
        loading.value = false
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

onMounted(async () => {
    await loadPrescriptions()
})

let debounceTimer = null

watch(
    [prescriberNameFilter, patientNameFilter, medicationNameFilter],
    () => {
        clearTimeout(debounceTimer)
        const prescriberLength = prescriberNameFilter.value.trim().length
        const patientLength = patientNameFilter.value.trim().length
        const medicationLength = medicationNameFilter.value.trim().length

        if(prescriberLength  > 1 || patientLength > 1 || medicationLength > 1) {
            debounceTimer = setTimeout(() => {
                loadPrescriptions(1)
            }, 500)
        }
    }
)

</script>

<template>
    <div class="p-10">

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-xl font-bold">Prescriptions</h1>
            <button @click="logout"
                    class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                Logout
            </button>
        </div>

        <div class="flex flex-wrap gap-4 mb-6">
            <select name="status"
                    @change="loadPrescriptions(1)"
                    v-model="statusFilter"
                    class="px-4 py-2 border rounded">
                <option value="">All statuses</option>
                <option value="active">Active</option>
                <option value="completed">Completed</option>
                <option value="stopped">Stopped</option>
            </select>
            <input v-model="patientNameFilter"
                   type="text"
                   placeholder="Filter by patient name"
                   class="px-4 py-2 border rounded">
            <input v-model="prescriberNameFilter"
                   type="text"
                   placeholder="Filter by prescriber name"
                   class="px-4 py-2 border rounded">
            <input v-model="medicationNameFilter"
                   type="text"
                   placeholder="Filter by medication name"
                   class="px-4 py-2 border rounded">
            <button @click="loadPrescriptions(1)"
                    class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                Filter
            </button>
        </div>

<!--        <div v-if="paginationMeta" class="flex gap-2 mt-4 py-2">
            <button
                v-for="link in paginationMeta.links"
                :key="link.label"
                v-show="link.page !== null"
                :class="[
                            'px-3 py-1 rounded',
                            link.active ? 'bg-blue-600 text-white' : 'bg-gray-100'
                        ]"
                @click="loadPrescriptions(link.page)"
            >
                <span v-html="link.label"></span>
            </button>
        </div>-->

        <div>
            <p v-if="loading">Loading...</p>

            <p v-if="errorMessage" class="text-red-600">
                {{ errorMessage }}
            </p>
        </div>

        <table
            class="w-full border border-gray-300"
            v-if="!loading && prescriptions.length"
        >

            <thead class="bg-gray-100">
            <tr>
                <th class="border px-3 py-2">ID</th>
                <th
                    @click="toggleSortDirection('patient')"
                    class="cursor-pointer select-none border px-3 py-2 hover:text-blue-600"
                >
                    Patient name
                    <span v-if="sortField === 'patient'" class="ml-1">
                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                </th>
                <th
                    @click="toggleSortDirection('prescriber')"
                    class="cursor-pointer select-none border px-3 py-2 hover:text-blue-600"
                >
                    Prescriber name
                    <span v-if="sortField === 'prescriber'" class="ml-1">
                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                </th>
                <th
                    @click="toggleSortDirection('medication')"
                    class="cursor-pointer select-none border px-3 py-2 hover:text-blue-600"
                >
                    Medication name
                    <span v-if="sortField === 'medication'" class="ml-1">
                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                </th>
                <th class="border px-3 py-2">Status</th>
                <th class="border px-3 py-2">Dosage</th>
                <th class="border px-3 py-2">Frequency</th>
                <th
                    @click="toggleSortDirection('start_date')"
                    class="cursor-pointer select-none border px-3 py-2 hover:text-blue-600"
                >
                    Start date
                    <span v-if="sortField === 'start_date'" class="ml-1">
                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                </th>
                <th
                    @click="toggleSortDirection('end_date')"
                    class="cursor-pointer select-none border px-3 py-2 hover:text-blue-600"
                >
                    End date
                    <span v-if="sortField === 'end_date'" class="ml-1">
                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="prescription in prescriptions" :key="prescription.id">
                <td class="border px-3 py-2">{{ prescription.id }}</td>
                <td class="border px-3 py-2">{{ prescription.patient?.name || '-' }}</td>
                <td class="border px-3 py-2">{{ prescription.prescriber?.name || '-' }}</td>
                <td class="border px-3 py-2">{{ prescription.medication?.name || '-' }}</td>
                <td class="border px-3 py-2">{{ prescription.status }}</td>
                <td class="border px-3 py-2">{{ prescription.dosage }}</td>
                <td class="border px-3 py-2">{{ prescription.frequency }}</td>
                <td class="border px-3 py-2">{{ prescription.start_date?.slice(0, 10) }}</td>
                <td class="border px-3 py-2">{{ prescription.end_date?.slice(0, 10) }}</td>
            </tr>
            </tbody>
        </table>

        <div v-if="paginationMeta" class="flex gap-2 mt-4 pb-2">
            <button
                v-for="link in paginationMeta.links"
                :key="link.label"
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
                @click="link.url && loadPrescriptions(link.page)"
            >
                <span v-html="link.label"></span>
            </button>
        </div>

<!--        <div v-if="paginationMeta" class="flex flex-wrap gap-4 mb-6 py-2">
            <button @click="loadPrescriptions(paginationMeta.current_page - 1)"
                    v-if="paginationMeta.current_page > 1"
                    class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                Previous
            </button>
            page {{ paginationMeta?.current_page }}
            <button @click="loadPrescriptions(paginationMeta.current_page + 1)"
                    v-if="paginationMeta.current_page < paginationMeta.last_page"
                    class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                Next
            </button>
        </div>-->
        <p v-if="!loading && !prescriptions.length">
            No prescriptions found.
        </p>
    </div>
</template>

<script setup>
import { onMounted, ref, watch} from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import { useDataTable } from '../composables/useDataTable'


const nameFilter = ref('')
const emailFilter = ref('')
const jobTitleFilter = ref('')
const organizationFilter = ref('')

const router = useRouter()

const normalizeSearchValue = (value) => {
    const trimmedValue = value.trim()

    if (trimmedValue.length >= 2) {
        return trimmedValue
    }

    return undefined
}

const {
    rows: users,
    loading,
    errorMessage,
    paginationMeta,
    sortField,
    sortDirection,
    loadData: loadUsers,
    toggleSortDirection,
} = useDataTable({
    endpoint: '/api/users',
    defaultSortField: 'id',
    buildFilterParams: () => ({
        name: normalizeSearchValue(nameFilter.value),
        email: normalizeSearchValue(emailFilter.value),
        job_title: normalizeSearchValue(jobTitleFilter.value),
        organization: normalizeSearchValue(organizationFilter.value),
    }),
})

const logout = async () => {
    try {
        await api.post('/logout')
        router.push('/login')
    } catch (error) {
        console.error(error)
    }
}

onMounted(async () => {
    await loadUsers()
})

let debounceTimer = null

watch(
    [nameFilter, emailFilter, jobTitleFilter, organizationFilter],
    () => {
        clearTimeout(debounceTimer)

        debounceTimer = setTimeout(() => {
            loadUsers(1)
        }, 500)
    }
)

</script>

<template>
    <div class="p-10">

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-xl font-bold">Users</h1>
            <button @click="logout"
                    class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                Logout
            </button>
        </div>

        <div class="flex flex-wrap gap-4 mb-6">

            <input v-model="nameFilter"
                   type="text"
                   placeholder="Filter by user name"
                   class="px-4 py-2 border rounded"
            />
            <input v-model="emailFilter"
                   type="text"
                   placeholder="Filter by user email"
                   class="px-4 py-2 border rounded"
            />
            <input
                    v-model="jobTitleFilter"
                    type="text"
                    placeholder="Filter by user job title"
                    class="px-4 py-2 border rounded"
            />
            <input
                    v-model="organizationFilter"
                    type="text"
                    placeholder="Filter by user organization"
                    class="px-4 py-2 border rounded"
            />

            <button @click="loadUsers(1)"
                    class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                Filter
            </button>
            <button @click="$router.push({ name: 'users.create' })"
                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
            >
                New User
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
            v-if="!loading && users.length"
        >

            <thead class="bg-gray-100">
            <tr>
                <th class="border px-3 py-2">ID</th>
                <th
                    @click="toggleSortDirection('name')"
                    class="cursor-pointer select-none border px-3 py-2 hover:text-blue-600"
                >
                    User name
                    <span v-if="sortField === 'name'" class="ml-1">
                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                </th>

                <th
                    @click="toggleSortDirection('email')"
                    class="cursor-pointer select-none border px-3 py-2 hover:text-blue-600"
                >
                    User email
                    <span v-if="sortField === 'email'" class="ml-1">
                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                </th>

                <th
                    @click="toggleSortDirection('job_title')"
                    class="cursor-pointer select-none border px-3 py-2 hover:text-blue-600"
                >
                    User job title
                    <span v-if="sortField === 'job_title'" class="ml-1">
                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                </th>

                <th
                    @click="toggleSortDirection('organization')"
                    class="cursor-pointer select-none border px-3 py-2 hover:text-blue-600"
                >
                    User organization
                    <span v-if="sortField === 'organization'" class="ml-1">
                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="user in users" :key="user.id">
                <td class="border px-3 py-2">{{ user.id }}</td>
                <td class="border px-3 py-2">{{ user.name || '-' }}</td>
                <td class="border px-3 py-2">{{ user.email || '-' }}</td>
                <td class="border px-3 py-2">{{ user.job_title || '-' }}</td>
                <td class="border px-3 py-2">{{ user.organization || '-' }}</td>
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
                @click="link.url && loadUsers(link.page)"
            >
                <span v-html="link.label"></span>
            </button>
        </div>

        <p v-if="!loading && !users.length">
            No users found.
        </p>
    </div>
</template>

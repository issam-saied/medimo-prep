import { ref } from 'vue'
import api from '../services/api'

export function useDataTable({ endpoint, defaultSortField, buildFilterParams }) {
    const rows = ref([])
    const loading = ref(false)
    const errorMessage = ref('')
    const paginationMeta = ref(null)

    const sortField = ref(defaultSortField)
    const sortDirection = ref('desc')

    const loadData = async (page = 1) => {
        loading.value = true
        errorMessage.value = ''

        try {
            const response = await api.get(endpoint, {
                params: {
                    page,
                    sort_field: sortField.value,
                    sort_direction: sortDirection.value,
                    ...buildFilterParams(),
                },
            })

            rows.value = response.data.data
            paginationMeta.value = response.data.meta
        } catch (error) {
            errorMessage.value =
                error.response?.data?.message || 'Could not load data'
            console.error(error)
        } finally {
            loading.value = false
        }
    }

    const toggleSortDirection = (field) => {
        if (sortField.value === field) {
            sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
        } else {
            sortField.value = field
            sortDirection.value = 'asc'
        }

        return loadData(1)
    }

    return {
        rows,
        loading,
        errorMessage,
        paginationMeta,
        sortField,
        sortDirection,
        loadData,
        toggleSortDirection,
    }
}

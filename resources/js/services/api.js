import axios from 'axios'

const api = axios.create({
    baseURL: '',
    withCredentials: true,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
    },
})

export const getCsrfCookie = async () => {
    await api.get('/sanctum/csrf-cookie')
}


export default api

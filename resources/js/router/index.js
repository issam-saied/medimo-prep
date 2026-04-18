import { createRouter, createWebHistory } from 'vue-router'
import LoginPage from '../pages/LoginPage.vue'
import PrescriptionsPage from '../pages/PrescriptionsPage.vue'
import AdministrationsPage from "../pages/AdministrationsPage.vue";
import CreatePrescriptionPage from "../pages/CreatePrescriptionPage.vue";
import CreateAdministrationPage from "../pages/CreateAdministrationPage.vue";
import api from "../services/api.js";
import {ref} from "vue";
import PatientsPage from "../pages/PatientsPage.vue";
import CreatePatientPage from "../pages/CreatePatientPage.vue";
import MedicationsPage from "../pages/MedicationsPage.vue";
import CreateMedicationPage from "../pages/CreateMedicationPage.vue";
import UsersPage from "../pages/UsersPage.vue";
import CreateUserPage from "../pages/CreateUserPage.vue";
import UpdatePatientPage from "../pages/UpdatePatientPage.vue";


const isAuthenticated = ref(false)

const routes = [
    {
        path: '/login',
        name: 'login',
        component: LoginPage,
    },
    {
        path: '/patients',
        name: 'patients',
        component: PatientsPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/users',
        name: 'user',
        component: UsersPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/medications',
        name: 'medications',
        component: MedicationsPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/prescriptions',
        name: 'prescriptions',
        component: PrescriptionsPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/administrations',
        name: 'administrations',
        component: AdministrationsPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/patients/create',
        name: 'patients.create',
        component: CreatePatientPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/patients/:id/edit',
        name: 'patients.edit',
        component: UpdatePatientPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/users/create',
        name: 'users.create',
        component: CreateUserPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/medications/create',
        name: 'medications.create',
        component: CreateMedicationPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/prescriptions/create',
        name: 'prescriptions.create',
        component: CreatePrescriptionPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/administrations/create',
        name: 'administrations.create',
        component: CreateAdministrationPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/',
        redirect: '/prescriptions',
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

router.beforeEach(async (to) => {
    if (!to.meta.requiresAuth) {
        return true
    }

    try {
        await api.get('/api/me')
        return true
    } catch (error) {
        return { name: 'login' }
    }
})

export default router

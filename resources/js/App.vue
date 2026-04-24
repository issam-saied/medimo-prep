<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import AppLayout from './components/AppLayout.vue'

const route = useRoute()
const isLoginPage = computed(() => route.path === '/login')
</script>

<template>
    <AppLayout v-if="!isLoginPage">
        <router-view />
    </AppLayout>
    <router-view v-else />
</template>

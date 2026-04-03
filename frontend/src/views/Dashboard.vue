<template>
  <div>
    <h1>Dashboard</h1>
    <p>Willkommen!</p>
    <button @click="handleLogout">Logout</button>
  </div>
</template>

<script setup>
import {useAuthStore} from '../stores/auth'
import {useRouter} from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

function handleLogout() {
  auth.logout()
  router.push('/')
}
</script>

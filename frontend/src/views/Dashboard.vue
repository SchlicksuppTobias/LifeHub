<template>
  <div class="dashboard">
    <header class="header">
      <h1>Dashboard</h1>
      <button class="logout-btn" @click="handleLogout">Logout</button>
    </header>

    <div class="grid">
      <button v-for="item in items"
              :key="item.name"
              class="square-btn"
              @click="goTo(item.route)">
        {{ item.name }}
      </button>
    </div>
  </div>
</template>

<script setup>
import {useAuthStore} from '../stores/auth'
import {useRouter} from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

const items = [
  {name: 'News', route: '/news'},
  {name: 'Rezepte', route: '/rezepte'},
  {name: 'Einkaufslisten', route: '/einkauf'},
  {name: 'Nährwerttabelle', route: '/nutritionSearch'},
  {name: 'Kalender', route: '/kalender'},
  {name: 'Todo Liste', route: '/todo'},
  {name: 'Sport Tracker', route: '/sport'},
  {name: 'Gesundheit Tracker', route: '/gesundheit'},
  {name: 'Profil', route: '/profile'},
  {name: 'Einstellungen', route: '/einstellungen'},
  {name: 'Shop', route: '/shop'}
]

function handleLogout() {
  auth.logout()
  router.push('/')
}

function goTo(route) {
  router.push(route)
}
</script>

<style scoped>
.dashboard {
  padding: 20px;
  min-height: 100vh;
  background: #f5f7fa;
  box-sizing: border-box;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.logout-btn {
  padding: 8px 16px;
  border: none;
  background: #e74c3c;
  color: white;
  border-radius: 8px;
  cursor: pointer;
  transition: 0.2s ease;
}

.logout-btn:hover {
  background: #c0392b;
}

.grid {
  display: grid;
  gap: 20px;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
}

.square-btn {
  aspect-ratio: 1 / 1;
  border: none;
  border-radius: 16px;
  background: white;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 10px;
}

.square-btn:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 18px rgba(0, 0, 0, 0.15);
  background: #eef2f7;
}

@media (min-width: 1200px) {
  .grid {
    grid-template-columns: repeat(5, 1fr);
  }
}
</style>

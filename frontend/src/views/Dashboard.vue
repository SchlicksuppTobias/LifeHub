<template>
  <div class="dashboard">
    <header class="header">
      <h1>Dashboard</h1>
      <button class="logout-btn" @click="handleLogout">Logout</button>
    </header>

    <div class="grid">
      <div
        v-for="item in items"
        :key="item.name"
        class="btn-wrapper"
      >
        <button
          class="square-btn"
          @click="handleClick(item)"
        >
          {{ item.name }}
        </button>

        <!-- Submenü nur für Rezepte -->
        <div
          v-if="item.submenu && activeSubmenu === item.name"
          class="submenu"
          @click.stop
        >
          <button
            v-for="sub in item.submenu"
            :key="sub.label"
            class="submenu-item"
            @click="goTo(sub.route); activeSubmenu = null"
          >
            {{ sub.label }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {ref} from 'vue'
import {useAuthStore} from '../stores/auth'
import {useRouter} from 'vue-router'

const auth = useAuthStore()
const router = useRouter()
const activeSubmenu = ref(null)

const items = [
  {name: 'News', route: '/news'},
  {
    name: 'Rezepte',
    submenu: [
      {label: 'Rezept erstellen', route: '/rezepte/erstellen'},
      {label: 'Rezepte durchsuchen', route: '/rezepte/anzeigen'},
    ]
  },
  {
    name: 'Einkaufslisten',
    submenu: [
      {label: 'Liste erstellen', route: '/einkaufsliste'},
      {label: 'Liste öffnen (per Link)', route: '/einkaufsliste/oeffnen'},
    ]
  },
  {name: 'Nährwerttabelle', route: '/nutritionSearch'},
  {name: 'Kalender', route: '/kalender'},
  {name: 'Todo Liste', route: '/todo'},
  {name: 'Sport Tracker', route: '/sport'},
  {name: 'Gesundheit Tracker', route: '/gesundheit'},
  {name: 'Profil', route: '/profile'},
  {name: 'Einstellungen', route: '/einstellungen'},
  {name: 'BMI Rechner', route: '/bmi'},
  {name: 'Wetter', route: '/weather'},
  {name: 'Shop', route: '/shop'}
]

function handleClick(item) {
  if (item.submenu) {
    activeSubmenu.value = activeSubmenu.value === item.name ? null : item.name
  } else {
    activeSubmenu.value = null
    goTo(item.route)
  }
}

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

.btn-wrapper {
  position: relative;
}

.square-btn {
  width: 100%;
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

.submenu {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  right: 0;
  background: white;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
  overflow: hidden;
  z-index: 100;
}

.submenu-item {
  width: 100%;
  padding: 12px 16px;
  border: none;
  background: transparent;
  text-align: left;
  cursor: pointer;
  font-size: 15px;
  font-weight: 500;
  color: #333;
  transition: background 0.15s;
}

.submenu-item:not(:last-child) {
  border-bottom: 1px solid #f0f0f0;
}

.submenu-item:hover {
  background: #f5f7fa;
}

@media (min-width: 1200px) {
  .grid {
    grid-template-columns: repeat(5, 1fr);
  }
}
</style>

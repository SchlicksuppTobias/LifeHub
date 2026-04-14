import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import Home from '../views/Home.vue'
import Dashboard from '../views/Dashboard.vue'
import NutritionSearch from "@/views/NutritionSearch.vue";
import Profile from "@/views/Profile.vue";

const routes = [
  { path: '/', component: Home },
  { path: '/register', component: () => import('../views/Register.vue') },
  { path: '/impressum', component: () => import('../views/Impressum.vue') },
  { path: '/datenschutz', component: () => import('../views/Datenschutz.vue') },
  { path: '/agb', component: () => import('../views/AGB.vue') },
  { path: '/kontakt', component: () => import('../views/Kontakt.vue') },
  {
    path: '/dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/nutritionSearch',
    component: NutritionSearch,
    meta: { requiresAuth: true }
  },
  {
    path: '/profile',
    component: Profile,
    meta: { requiresAuth: true }
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const auth = useAuthStore()
  next()
})

export default router

<template>
  <div class="page">

    <!-- LOADING -->
    <div v-if="loading" class="state">
      Lade Rezept...
    </div>

    <!-- ERROR -->
    <div v-else-if="error" class="state">
      Rezept konnte nicht geladen werden 😕
    </div>

    <!-- CONTENT -->
    <template v-else-if="recipe">
      <header class="page-header">
        <button class="back-btn" @click="router.back()">← Zurück</button>
      </header>

      <!-- HERO IMAGE -->
      <div class="hero">
        <img
          v-if="recipe.image_path"
          :src="`/${recipe.image_path}`"
          class="hero-image"
          alt="Rezeptbild"
        />
        <div v-else class="hero-image placeholder">🍽️</div>
      </div>

      <h1 class="title">{{ recipe.title }}</h1>

      <!-- TIMES -->
      <div v-if="hasAnyTime" class="times-row">
        <div v-if="recipe.prep_time_value" class="time-pill">
          <span class="time-label">Vorbereitung</span>
          <span class="time-value">{{ recipe.prep_time_value }} {{ recipe.prep_time_unit }}</span>
        </div>
        <div v-if="recipe.cook_time_value" class="time-pill">
          <span class="time-label">Koch-/Backzeit</span>
          <span class="time-value">{{ recipe.cook_time_value }} {{ recipe.cook_time_unit }}</span>
        </div>
        <div v-if="recipe.rest_time_value" class="time-pill">
          <span class="time-label">Ruhezeit</span>
          <span class="time-value">{{ recipe.rest_time_value }} {{ recipe.rest_time_unit }}</span>
        </div>
      </div>

      <!-- INGREDIENTS -->
      <section class="section">
        <h2 class="section-title">Zutaten</h2>
        <ul v-if="ingredients.length" class="ingredient-list">
          <li v-for="(ing, i) in ingredients" :key="i" class="ingredient-item">
            <span class="ingredient-amount">
              {{ ing.amount }}<span v-if="ing.unit"> {{ ing.unit }}</span>
            </span>
            <span class="ingredient-name">{{ ing.name }}</span>
          </li>
        </ul>
        <p v-else class="empty-text">Keine Zutaten angegeben.</p>
      </section>

      <!-- INSTRUCTIONS -->
      <section class="section">
        <h2 class="section-title">Zubereitung</h2>
        <p class="instructions">{{ recipe.instructions }}</p>
      </section>

      <!-- META -->
      <p v-if="recipe.created_at" class="created-at">
        Erstellt am {{ formatDate(recipe.created_at) }}
      </p>
    </template>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

const recipe = ref(null)
const loading = ref(true)
const error = ref(false)

onMounted(() => {
  fetchRecipe()
})

async function fetchRecipe() {
  loading.value = true
  error.value = false

  try {
    const id = route.params.id
    const res = await fetch(`/api/getRecipe.php?id=${encodeURIComponent(id)}`)

    if (!res.ok) throw new Error()

    recipe.value = await res.json()
  } catch (e) {
    error.value = true
  } finally {
    loading.value = false
  }
}

// 🧠 Zutaten-JSON sicher parsen
const ingredients = computed(() => {
  if (!recipe.value?.ingredients) return []

  try {
    const parsed = JSON.parse(recipe.value.ingredients)
    return Array.isArray(parsed) ? parsed : []
  } catch {
    return []
  }
})

const hasAnyTime = computed(() => {
  if (!recipe.value) return false
  return !!(recipe.value.prep_time_value || recipe.value.cook_time_value || recipe.value.rest_time_value)
})

function formatDate(dateStr) {
  // erwartet Format "YYYY-MM-DD HH:MM:SS"
  const d = new Date(dateStr.replace(' ', 'T'))
  if (isNaN(d.getTime())) return dateStr

  return d.toLocaleDateString('de-DE', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  })
}
</script>

<style scoped>
.page {
  padding: 24px;
  max-width: 760px;
  margin: 0 auto;
  background: #f6f7fb;
  min-height: 100vh;
  box-sizing: border-box;
}

.page-header {
  display: flex;
  align-items: center;
  margin-bottom: 16px;
}

.back-btn {
  padding: 6px 12px;
  border: none;
  background: white;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}

.state {
  text-align: center;
  padding: 60px 20px;
  color: #777;
}

/* HERO */
.hero {
  border-radius: 16px;
  overflow: hidden;
  margin-bottom: 16px;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
}

.hero-image {
  width: 100%;
  height: 260px;
  object-fit: cover;
  display: block;
  background: #eee;
}

.hero-image.placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 56px;
}

.title {
  font-size: 26px;
  margin: 0 0 16px;
  color: #1a1a1a;
}

/* TIMES */
.times-row {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 24px;
}

.time-pill {
  display: flex;
  flex-direction: column;
  gap: 2px;
  background: white;
  border-radius: 12px;
  padding: 10px 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.time-label {
  font-size: 12px;
  color: #999;
}

.time-value {
  font-size: 15px;
  font-weight: 600;
  color: #1a1a1a;
}

/* SECTIONS */
.section {
  background: white;
  border-radius: 16px;
  padding: 20px;
  margin-bottom: 18px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.section-title {
  font-size: 17px;
  margin: 0 0 14px;
  color: #1a1a1a;
}

/* INGREDIENTS */
.ingredient-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.ingredient-item {
  display: flex;
  gap: 10px;
  font-size: 15px;
  padding-bottom: 8px;
  border-bottom: 1px solid #f0f0f0;
}

.ingredient-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.ingredient-amount {
  flex-shrink: 0;
  min-width: 70px;
  color: #5b8dee;
  font-weight: 600;
}

.ingredient-name {
  color: #1a1a1a;
}

.empty-text {
  color: #999;
  font-size: 14px;
  margin: 0;
}

/* INSTRUCTIONS */
.instructions {
  white-space: pre-wrap;
  line-height: 1.7;
  color: #333;
  margin: 0;
  font-size: 15px;
}

.created-at {
  text-align: center;
  font-size: 13px;
  color: #aaa;
  margin: 8px 0 32px;
}
</style>

<template>
  <div class="page">

    <!-- HEADER -->
    <header class="header">
      <h1>🍽️ Rezepte entdecken</h1>

      <!-- SEARCH -->
      <input
        v-model="search"
        class="search"
        type="text"
        placeholder="Suche nach Rezepten..."
      />
    </header>

    <!-- LOADING -->
    <div v-if="loading" class="state">
      Lade Rezepte...
    </div>

    <!-- EMPTY -->
    <div v-else-if="recipes.length === 0" class="state">
      Keine Rezepte gefunden 😕
    </div>

    <!-- GRID -->
    <div v-else class="grid">
      <div
        v-for="recipe in recipes"
        :key="recipe.id"
        class="card"
        @click="openRecipe(recipe.id)"
      >
        <img
          v-if="recipe.image_path"
          :src="`/${recipe.image_path}`"
          class="image"
          alt="Rezeptbild"
        />
        <div v-else class="image placeholder">🍽️</div>

        <div class="content">
          <h3>{{ recipe.title }}</h3>
          <p class="meta">
            {{ formatIngredients(recipe.ingredients) }}
          </p>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const recipes = ref([])
const search = ref('')
const loading = ref(false)

let debounceTimer = null

// 🚀 Initial: Top 10 laden
onMounted(() => {
  fetchRecipes()
})

// 🔍 Suche mit Debounce + Reload
watch(search, () => {
  clearTimeout(debounceTimer)

  debounceTimer = setTimeout(() => {
    fetchRecipes()
  }, 300)
})

// 📡 API CALL
async function fetchRecipes() {
  loading.value = true

  try {
    const url = search.value
      ? `/api/getRecipes.php?search=${encodeURIComponent(search.value)}`
      : `/api/getRecipes.php?limit=10`

    const res = await fetch(url)

    if (!res.ok) throw new Error()

    recipes.value = await res.json()
  } catch (e) {
    recipes.value = []
  } finally {
    loading.value = false
  }
}

// 📄 Detailseite
function openRecipe(id) {
  router.push(`/rezepte/${id}`)
}

// 🧠 Zutaten hübsch anzeigen
function formatIngredients(ingredients) {
  if (!ingredients) return ''

  try {
    const parsed = JSON.parse(ingredients)

    if (Array.isArray(parsed)) {
      return parsed
        .map(i => i.name)
        .filter(Boolean)
        .slice(0, 3)
        .join(', ') + '...'
    }

    return ingredients
  } catch {
    return ingredients.length > 60
      ? ingredients.slice(0, 60) + '...'
      : ingredients
  }
}
</script>

<style scoped>
.page {
  padding: 24px;
  max-width: 1100px;
  margin: 0 auto;
  background: #f6f7fb;
  min-height: 100vh;
  box-sizing: border-box;
}

/* HEADER */
.header {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 20px;
}

.header h1 {
  margin: 0;
  font-size: 26px;
}

/* SEARCH */
.search {
  padding: 12px 14px;
  border-radius: 12px;
  border: 1px solid #ddd;
  font-size: 15px;
  outline: none;
  background: white;
}

.search:focus {
  border-color: #4f7cff;
}

/* STATES */
.state {
  text-align: center;
  padding: 40px;
  color: #777;
}

/* GRID */
.grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 18px;
}

/* CARD */
.card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  cursor: pointer;
  box-shadow: 0 6px 18px rgba(0,0,0,0.06);
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 24px rgba(0,0,0,0.1);
}

/* IMAGE */
.image {
  width: 100%;
  height: 140px;
  object-fit: cover;
  background: #eee;
}

.placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 40px;
  height: 140px;
}

/* CONTENT */
.content {
  padding: 12px;
}

.content h3 {
  margin: 0;
  font-size: 16px;
  margin-bottom: 6px;
}

.meta {
  font-size: 13px;
  color: #777;
}
</style>

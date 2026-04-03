<template>
  <div class="home">
    <h1>Willkommen</h1>

    <section class="news">
      <h2>Neuigkeiten</h2>

      <div v-if="loading" class="loading">Lädt...</div>
      <div v-else-if="error" class="error">{{ error }}</div>
      <div v-else-if="news.length === 0" class="empty">Noch keine Neuigkeiten.</div>

      <div v-else class="news-grid">
        <article v-for="item in news" :key="item.id" class="news-card">
          <h3>{{ item.title }}</h3>
          <p>{{ item.content }}</p>
          <small>{{ item.created_at }}</small>
        </article>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const news = ref([])
const loading = ref(true)
const error = ref(null)

onMounted(async () => {
  try {
    const res = await fetch('/api/news.php')
    if (!res.ok) throw new Error('Fehler beim Laden')
    news.value = await res.json()
  } catch (e) {
    console.log('Fehler:', e)  // ← das ist neu
    news.value = [
      { id: 1, title: 'Erster Eintrag', content: 'Das Backend kommt bald!', created_at: '2025-01-01' },
      { id: 2, title: 'Zweiter Eintrag', content: 'Vue läuft bereits.', created_at: '2025-01-02' },
    ]
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.home {
  max-width: 1000px;
  margin: 0 auto;
}
h1 {
  font-size: 2rem;
  margin-bottom: 2rem;
}
.news-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
}
.news-card {
  background: #f5f5f5;
  border-radius: 8px;
  padding: 1.5rem;
  border-left: 4px solid #4CAF50;
}
.news-card h3 {
  margin: 0 0 0.5rem;
}
.news-card small {
  color: #888;
}
.loading, .error, .empty {
  padding: 2rem;
  text-align: center;
  color: #888;
}
</style>

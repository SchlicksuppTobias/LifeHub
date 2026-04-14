<template>
  <div class="home">
    <div class="header">
      <h1>Willkommen</h1>
      <div class="auth-buttons">
        <button class="btn btn-outline" @click="showLogin = true">Anmelden</button>
        <router-link to="/register" class="btn btn-primary">Registrieren</router-link>
      </div>
    </div>

    <!-- Login Modal -->
    <Transition name="modal">
      <div v-if="showLogin" class="modal-overlay" @click.self="showLogin = false">
        <div class="modal">
          <button class="modal-close" @click="showLogin = false">✕</button>
          <h2>Anmelden</h2>

          <div v-if="loginError" class="login-error">{{ loginError }}</div>

          <div class="form-group">
            <label for="email">E-Mail</label>
            <input id="email" v-model="loginForm.email" type="email" placeholder="name@beispiel.de" @keyup.enter="handleLogin"/>
          </div>
          <div class="form-group">
            <label for="password">Passwort</label>
            <input id="password" v-model="loginForm.password" type="password" placeholder="••••••••" @keyup.enter="handleLogin"/>
          </div>

          <button class="btn btn-primary btn-full" :disabled="loginLoading" @click="handleLogin">
            {{ loginLoading ? 'Wird angemeldet…' : 'Anmelden' }}
          </button>

          <p class="modal-footer">
            Noch kein Konto?
            <router-link to="/register" @click="showLogin = false">Jetzt registrieren</router-link>
          </p>
        </div>
      </div>
    </Transition>

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
import { useRouter } from 'vue-router'

const router = useRouter()
const news = ref([])
const loading = ref(true)
const error = ref(null)

const showLogin = ref(false)
const loginLoading = ref(false)
const loginError = ref(null)
const loginForm = ref({ email: '', password: '' })

async function handleLogin() {
  loginError.value = null
  loginLoading.value = true

  try {
    const res = await fetch('/api/login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(loginForm.value),
    })

    const data = await res.json()

    if (!res.ok) {
      throw new Error(data.error || data.message || 'Login fehlgeschlagen')
    }

    localStorage.setItem('token', data.token)
    localStorage.setItem('user_id', data.user_id)

    loginForm.value = { email: '', password: '' }

    showLogin.value = false
    router.push('/dashboard')

  } catch (e) {
    loginError.value = e.message
  } finally {
    loginLoading.value = false
  }
}

onMounted(async () => {
  try {
    const res = await fetch('/api/news.php')
    if (!res.ok) throw new Error('Fehler beim Laden')
    news.value = await res.json()
  } catch (e) {
    console.log('Fehler:', e)
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

/* Header mit Buttons */
.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 2rem;
}
h1 {
  font-size: 2rem;
  margin: 0;
}
.auth-buttons {
  display: flex;
  gap: 0.75rem;
}

/* Buttons */
.btn {
  padding: 0.5rem 1.25rem;
  border-radius: 6px;
  font-size: 0.95rem;
  cursor: pointer;
  text-decoration: none;
  border: none;
  transition: opacity 0.15s;
}
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-outline {
  background: transparent;
  border: 1.5px solid #4CAF50;
  color: #4CAF50;
}
.btn-outline:hover { background: #4CAF5015; }
.btn-primary {
  background: #4CAF50;
  color: #fff;
  display: inline-block;
}
.btn-primary:hover { opacity: 0.88; }
.btn-full { width: 100%; margin-top: 0.5rem; }

/* Modal Overlay */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
}
.modal {
  background: #fff;
  border-radius: 12px;
  padding: 2rem;
  width: min(420px, 90vw);
  position: relative;
  box-shadow: 0 8px 32px rgba(0,0,0,0.15);
}
.modal h2 {
  margin: 0 0 1.5rem;
  font-size: 1.4rem;
}
.modal-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: none;
  border: none;
  font-size: 1rem;
  cursor: pointer;
  color: #888;
  line-height: 1;
  padding: 0.25rem;
}
.modal-close:hover { color: #333; }

/* Formular */
.form-group {
  margin-bottom: 1rem;
}
.form-group label {
  display: block;
  font-size: 0.9rem;
  margin-bottom: 0.3rem;
  color: #555;
}
.form-group input {
  width: 100%;
  padding: 0.6rem 0.8rem;
  border: 1.5px solid #ddd;
  border-radius: 6px;
  font-size: 1rem;
  box-sizing: border-box;
  transition: border-color 0.15s;
}
.form-group input:focus {
  outline: none;
  border-color: #4CAF50;
}
.login-error {
  background: #fdecea;
  color: #c0392b;
  border-radius: 6px;
  padding: 0.6rem 0.8rem;
  font-size: 0.9rem;
  margin-bottom: 1rem;
}
.modal-footer {
  text-align: center;
  margin: 1rem 0 0;
  font-size: 0.9rem;
  color: #666;
}
.modal-footer a { color: #4CAF50; }

/* Modal Transition */
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-active .modal, .modal-leave-active .modal { transition: transform 0.2s; }
.modal-enter-from .modal, .modal-leave-to .modal { transform: translateY(-12px); }

/* News (unverändert) */
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
.news-card h3 { margin: 0 0 0.5rem; }
.news-card small { color: #888; }
.loading, .error, .empty { padding: 2rem; text-align: center; color: #888; }
</style>

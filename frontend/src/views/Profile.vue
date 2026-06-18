```vue
<template>
  <div class="profile">

    <div class="page-header">
      <button
        class="back-btn"
        @click="$router.push('/dashboard')"
      >
        ← Zurück
      </button>

      <h1>Mein Profil</h1>
    </div>

    <div class="profile-card">

      <!-- AVATAR -->
      <div class="avatar-section">
        <img :src="form.avatar || defaultAvatar" class="avatar" />
        <input type="file" accept="image/*" @change="handleImageUpload" />
      </div>

      <!-- FORM -->
      <form @submit.prevent="saveProfile">

        <div class="grid">

          <!-- Persönliche Daten -->
          <div class="form-group">
            <label>Vorname</label>
            <input v-model="form.first_name" type="text" />
          </div>

          <div class="form-group">
            <label>Nachname</label>
            <input v-model="form.last_name" type="text" />
          </div>

          <div class="form-group">
            <label>Email</label>
            <input v-model="form.email" type="email" />
          </div>

          <div class="form-group">
            <label>Telefon</label>
            <input v-model="form.phone" type="text" />
          </div>

          <!-- Adresse -->
          <div class="form-group">
            <label>Straße</label>
            <input v-model="form.street" type="text" />
          </div>

          <div class="form-group">
            <label>Hausnummer</label>
            <input v-model="form.house_number" type="text" />
          </div>

          <div class="form-group">
            <label>PLZ</label>
            <input v-model="form.postal_code" type="text" />
          </div>

          <div class="form-group">
            <label>Ort</label>
            <input v-model="form.city" type="text" />
          </div>

          <div class="form-group">
            <label>Bundesland</label>
            <input v-model="form.state" type="text" />
          </div>

          <div class="form-group">
            <label>Land</label>
            <input v-model="form.country" type="text" />
          </div>

          <!-- Körperdaten -->
          <div class="form-group">
            <label>Geburtsdatum</label>
            <input v-model="form.birth_date" type="date" />
          </div>

          <div class="form-group">
            <label>Geschlecht</label>
            <select v-model="form.gender">
              <option value="">-- bitte wählen --</option>
              <option value="männlich">Männlich</option>
              <option value="weiblich">Weiblich</option>
              <option value="divers">Divers</option>
            </select>
          </div>

          <div class="form-group">
            <label>Größe (cm)</label>
            <input v-model="form.height" type="number" min="0" />
          </div>

          <div class="form-group">
            <label>Gewicht (kg)</label>
            <input v-model="form.weight" type="number" min="0" />
          </div>

          <div class="form-group">
            <label>Zielgewicht (kg)</label>
            <input v-model="form.weightGoal" type="number" min="0" />
          </div>

          <div class="form-group full">
            <label>Bio</label>
            <textarea v-model="form.bio"></textarea>
          </div>

        </div>

        <button class="btn-save" :disabled="loading">
          {{ loading ? 'Speichern...' : 'Speichern' }}
        </button>

      </form>

      <div v-if="savedMessage" :class="['message', savedMessage.type]">
        {{ savedMessage.text }}
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const defaultAvatar = 'https://via.placeholder.com/150'

const loading = ref(false)
const savedMessage = ref(null)

const form = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',

  street: '',
  house_number: '',
  postal_code: '',
  city: '',
  state: '',
  country: '',

  birth_date: '',
  gender: '',
  weight: '',
  weightGoal: '',
  height: '',

  bio: '',
  avatar: ''
})

onMounted(async () => {
  await loadProfile()
})

/**
 * LOAD PROFILE FROM DB
 */
async function loadProfile() {
  loading.value = true

  try {
    const res = await fetch('/api/getProfile.php')
    const json = await res.json()

    if (json.success) {
      form.value = {
        ...form.value,
        ...json.data
      }
    } else {
      throw new Error(json.message || 'Fehler beim Laden')
    }

  } catch (e) {
    console.error(e)

    // Fallback-Daten (damit UI nicht leer ist)
    form.value = {
      first_name: 'Max',
      last_name: 'Mustermann',
      email: 'max@example.com',
      phone: '01761234567',

      street: 'Musterstraße',
      house_number: '12A',
      postal_code: '70173',
      city: 'Stuttgart',
      state: 'Baden-Württemberg',
      country: 'Deutschland',

      birth_date: '1990-01-01',
      gender: 'männlich',
      weight: 80,
      weightGoal: 75,
      height: 180,

      bio: 'Ich liebe gutes Essen 🍝',
      avatar: ''
    }
  } finally {
    loading.value = false
  }
}

/**
 * IMAGE UPLOAD (BASE64)
 */
function handleImageUpload(e) {
  const file = e.target.files[0]
  if (!file) return

  const reader = new FileReader()
  reader.onload = () => {
    form.value.avatar = reader.result
  }
  reader.readAsDataURL(file)
}

/**
 * SAVE PROFILE TO DB
 */
async function saveProfile() {
  loading.value = true

  try {
    const res = await fetch('/api/saveProfile.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(form.value)
    })

    const json = await res.json()

    if (!json.success) {
      throw new Error(json.error || 'Speichern fehlgeschlagen')
    }

    savedMessage.value = { type: 'success', text: 'Profil erfolgreich gespeichert!' }

  } catch (e) {
    console.error(e)
    savedMessage.value = { type: 'error', text: 'Fehler beim Speichern.' }
  } finally {
    loading.value = false

    setTimeout(() => {
      savedMessage.value = null
    }, 2500)
  }
}
</script>

<style scoped>
.profile {
  max-width: 900px;
  margin: auto;
  padding: 20px;
}

.page-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 20px;
}

.page-header h1 {
  margin: 0;
}

.back-btn {
  border: 1px solid #ccc;
  background: #f5f5f5;
  border-radius: 8px;
  padding: 8px 14px;
  cursor: pointer;
  font-size: 14px;
  white-space: nowrap;
  transition: background 0.2s;
}

.back-btn:hover {
  background: #e8e8e8;
}

.profile-card {
  background: #fff;
  padding: 2rem;
  border-radius: 14px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
}

.avatar-section {
  text-align: center;
  margin-bottom: 2rem;
}

.avatar {
  width: 140px;
  height: 140px;
  border-radius: 50%;
  object-fit: cover;
  margin-bottom: 1rem;
}

.grid {
  display: grid;
  gap: 1rem;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
}

.form-group label {
  font-size: 0.85rem;
  margin-bottom: 0.3rem;
  display: block;
  color: #555;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 0.6rem;
  border-radius: 8px;
  border: 1px solid #ddd;
  box-sizing: border-box;
  background: #fff;
  font-size: 0.95rem;
  color: #333;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #4CAF50;
}

.full {
  grid-column: 1 / -1;
}

.btn-save {
  margin-top: 1.5rem;
  padding: 0.8rem 1.5rem;
  background: #4CAF50;
  color: #fff;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.95rem;
}

.btn-save:hover {
  opacity: 0.9;
}

.btn-save:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.message {
  margin-top: 1rem;
  padding: 0.8rem;
  border-radius: 8px;
  text-align: center;
  font-weight: 500;
}

.message.success {
  background: #eafaf1;
  color: #2ecc71;
}

.message.error {
  background: #fdecea;
  color: #e74c3c;
}
</style>
```

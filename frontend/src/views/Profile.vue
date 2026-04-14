<template>
  <div class="profile">
    <h1>Mein Profil</h1>

    <div class="profile-card">
      <!-- Avatar -->
      <div class="avatar-section">
        <img :src="form.avatar || defaultAvatar" class="avatar" />
        <input type="file" accept="image/*" @change="handleImageUpload" />
      </div>

      <!-- Formular -->
      <form @submit.prevent="saveProfile">
        <div class="grid">
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
            <label>Geburtsdatum</label>
            <input v-model="form.birth_date" type="date" />
          </div>

          <div class="form-group">
            <label>Gewicht (kg)</label>
            <input v-model="form.weight" type="number" />
          </div>

          <div class="form-group">
            <label>Größe (cm)</label>
            <input v-model="form.height" type="number" />
          </div>
        </div>

        <button class="btn-save">
          Speichern
        </button>
      </form>

      <div v-if="savedMessage" class="success">
        {{ savedMessage }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const defaultAvatar = 'https://via.placeholder.com/150'

const form = ref({
  first_name: '',
  last_name: '',
  email: '',
  birth_date: '',
  weight: '',
  height: '',
  avatar: ''
})

const savedMessage = ref(null)

onMounted(() => {
  const saved = localStorage.getItem('profile')

  if (saved) {
    form.value = JSON.parse(saved)
  } else {
    // Dummy Daten zum Testen
    form.value = {
      first_name: 'Max',
      last_name: 'Mustermann',
      email: 'max@example.com',
      birth_date: '1990-01-01',
      weight: 80,
      height: 180,
      avatar: ''
    }
  }
})

function handleImageUpload(e) {
  const file = e.target.files[0]
  if (!file) return

  const reader = new FileReader()

  reader.onload = () => {
    form.value.avatar = reader.result
  }

  reader.readAsDataURL(file)
}

function saveProfile() {
  localStorage.setItem('profile', JSON.stringify(form.value))

  savedMessage.value = 'Profil erfolgreich gespeichert!'
  setTimeout(() => {
    savedMessage.value = null
  }, 2500)
}
</script>

<style scoped>
.profile {
  max-width: 900px;
  margin: auto;
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

.form-group input {
  width: 100%;
  padding: 0.6rem;
  border-radius: 8px;
  border: 1px solid #ddd;
}

.form-group input:focus {
  outline: none;
  border-color: #4CAF50;
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
}

.btn-save:hover {
  opacity: 0.9;
}

.success {
  margin-top: 1rem;
  padding: 0.8rem;
  background: #eafaf1;
  color: #2ecc71;
  border-radius: 8px;
  text-align: center;
}
</style>

<template>
  <div class="page">
    <header class="page-header">
      <button class="back-btn" @click="router.push('/dashboard')">← Dashboard</button>
      <h1>Einkaufsliste öffnen</h1>
    </header>

    <section class="section">
      <p class="hint">
        Füge hier den geteilten Link oder die ID einer Einkaufsliste ein, um sie zu öffnen und zu bearbeiten.
      </p>

      <form class="form" @submit.prevent="openList">
        <input
          v-model="input"
          type="text"
          class="input"
          placeholder="z.B. https://.../einkaufsliste/3f29... oder nur die ID"
          autofocus
        />

        <p v-if="errorMsg" class="error-msg">{{ errorMsg }}</p>

        <button type="submit" class="submit-btn" :disabled="!input.trim()">
          Liste öffnen
        </button>
      </form>
    </section>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const input = ref('')
const errorMsg = ref('')

const uuidPattern = /[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i

function openList() {
  errorMsg.value = ''

  const value = input.value.trim()
  const match = value.match(uuidPattern)

  if (!match) {
    errorMsg.value = 'Das sieht nicht nach einem gültigen Link oder einer gültigen ID aus.'
    return
  }

  router.push(`/einkaufsliste/${match[0]}`)
}
</script>

<style scoped>
.page {
  padding: 20px;
  min-height: 100vh;
  background: #f5f7fa;
  box-sizing: border-box;
  max-width: 560px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 28px;
}

.page-header h1 {
  margin: 0;
  font-size: 22px;
  font-weight: 600;
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

.section {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.hint {
  font-size: 14px;
  color: #666;
  margin: 0 0 20px;
  line-height: 1.5;
}

.form {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.input {
  width: 100%;
  padding: 12px 14px;
  border: 1.5px solid #e0e0e0;
  border-radius: 10px;
  font-size: 15px;
  background: #fafafa;
  box-sizing: border-box;
  color: #1a1a1a;
}

.input:focus {
  outline: none;
  border-color: #5b8dee;
  background: white;
}

.error-msg {
  color: #e74c3c;
  font-size: 14px;
  margin: 0;
}

.submit-btn {
  padding: 13px 24px;
  background: #3d5bd9;
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s, opacity 0.2s;
}

.submit-btn:hover {
  background: #2f4abf;
}

.submit-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>

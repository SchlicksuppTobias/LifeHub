<template>
  <div class="register-wrapper">
    <div class="register-card">
      <div class="card-header">
        <div class="logo-mark">
          <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
            <rect width="32" height="32" rx="8" fill="#1a1a2e"/>
            <path d="M8 16L14 22L24 10" stroke="#4f8ef7" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <h1>Konto erstellen</h1>
        <p class="subtitle">Registriere dich mit deiner E-Mail-Adresse</p>
      </div>

      <form @submit.prevent="submitForm" class="register-form" novalidate>

        <!-- E-Mail -->
        <div class="field-group">
          <label for="email">E-Mail-Adresse <span class="req">*</span></label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            placeholder="max@beispiel.de"
            autocomplete="email"
            :class="{ error: errors.email }"
          />
          <span class="error-msg" v-if="errors.email">{{ errors.email }}</span>
        </div>

        <!-- Passwort -->
        <div class="field-group">
          <label for="passwort">Passwort <span class="req">*</span></label>
          <div class="pw-input-wrap">
            <input
              id="passwort"
              v-model="form.passwort"
              :type="showPw ? 'text' : 'password'"
              placeholder="••••••••"
              autocomplete="new-password"
              :class="{ error: errors.passwort }"
            />
            <button type="button" class="eye-btn" @click="showPw = !showPw" :aria-label="showPw ? 'Passwort verbergen' : 'Passwort anzeigen'">
              <svg v-if="!showPw" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            </button>
          </div>

          <!-- Stärkeanzeige -->
          <div class="pw-strength" v-if="form.passwort">
            <div class="strength-bars">
              <span class="bar" :class="{ active: pwStrength >= 1, red: pwStrength === 1, yellow: pwStrength === 2, green: pwStrength >= 3 }"></span>
              <span class="bar" :class="{ active: pwStrength >= 2, yellow: pwStrength === 2, green: pwStrength >= 3 }"></span>
              <span class="bar" :class="{ active: pwStrength >= 3, green: pwStrength >= 3 }"></span>
            </div>
            <span class="strength-label" :class="{ red: pwStrength === 1, yellow: pwStrength === 2, green: pwStrength >= 3 }">
              {{ pwStrengthLabel }}
            </span>
          </div>
          <span class="error-msg" v-if="errors.passwort">{{ errors.passwort }}</span>
        </div>

        <!-- Passwort wiederholen -->
        <div class="field-group">
          <label for="passwortWdh">Passwort wiederholen <span class="req">*</span></label>
          <div class="pw-input-wrap">
            <input
              id="passwortWdh"
              v-model="form.passwortWdh"
              :type="showPwWdh ? 'text' : 'password'"
              placeholder="••••••••"
              autocomplete="new-password"
              :class="{ error: errors.passwortWdh }"
            />
            <button type="button" class="eye-btn" @click="showPwWdh = !showPwWdh" :aria-label="showPwWdh ? 'Passwort verbergen' : 'Passwort anzeigen'">
              <svg v-if="!showPwWdh" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            </button>
          </div>

          <!-- Match-Indikator -->
          <div class="pw-match" v-if="form.passwortWdh">
            <span v-if="form.passwort === form.passwortWdh" class="match-ok">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              Passwörter stimmen überein
            </span>
            <span v-else class="match-no">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              Passwörter stimmen nicht überein
            </span>
          </div>
          <span class="error-msg" v-if="errors.passwortWdh">{{ errors.passwortWdh }}</span>
        </div>

        <!-- Submit -->
        <div class="submit-area">
          <p class="server-error" v-if="serverError">{{ serverError }}</p>
          <button type="submit" class="submit-btn" :disabled="isLoading">
            <span v-if="!isLoading">Konto erstellen</span>
            <span v-else class="spinner"></span>
          </button>
          <p class="login-hint">Bereits registriert? <a href="/login">Jetzt anmelden</a></p>
        </div>

      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const form = ref({
  email: '',
  passwort: '',
  passwortWdh: ''
})

const errors = ref({})
const serverError = ref('')
const isLoading = ref(false)
const showPw = ref(false)
const showPwWdh = ref(false)

const pwStrength = computed(() => {
  const p = form.value.passwort
  if (!p) return 0
  let score = 0
  if (p.length >= 8) score++
  if (/[A-Z]/.test(p) && /[a-z]/.test(p)) score++
  if (/[0-9]/.test(p) && /[^A-Za-z0-9]/.test(p)) score++
  return score
})

const pwStrengthLabel = computed(() => {
  return ['', 'Schwach', 'Mittel', 'Stark'][pwStrength.value] || ''
})

function validate() {
  const e = {}
  if (!form.value.email.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email))
    e.email = 'Gültige E-Mail-Adresse erforderlich'
  if (form.value.passwort.length < 8)
    e.passwort = 'Passwort muss mindestens 8 Zeichen haben'
  if (form.value.passwort !== form.value.passwortWdh)
    e.passwortWdh = 'Passwörter stimmen nicht überein'
  errors.value = e
  return Object.keys(e).length === 0
}

async function submitForm() {
  serverError.value = ''
  if (!validate()) return
  isLoading.value = true
  try {
    const res = await fetch('/api/register.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({
        email: form.value.email,
        passwort: form.value.passwort
      })
    })
    const data = await res.json()
    if (!res.ok) {
      serverError.value = data.message || 'Registrierung fehlgeschlagen. Bitte versuche es erneut.'
    } else {
      if (data.token) {
        const expires = new Date()
        expires.setDate(expires.getDate() + 30)
        document.cookie = `auth_token=${data.token}; expires=${expires.toUTCString()}; path=/; SameSite=Strict`
      }
      window.location.href = '/dashboard'
    }
  } catch {
    serverError.value = 'Netzwerkfehler. Bitte überprüfe deine Verbindung.'
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap');

*, *::before, *::after {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

:root {
  --bg: #0d0f1a;
  --surface: #13162a;
  --surface2: #1a1e35;
  --border: #252a45;
  --border-focus: #4f8ef7;
  --text: #e8eaf6;
  --text-muted: #6b7099;
  --accent: #4f8ef7;
  --accent2: #7c6af7;
  --red: #ef4444;
  --yellow: #f59e0b;
  --green: #22c55e;
  --radius: 10px;
}

.register-wrapper {
  font-family: 'Sora', sans-serif;
  min-height: 100vh;
  background: var(--bg);
  background-image: radial-gradient(ellipse 60% 40% at 70% 0%, rgba(79, 142, 247, 0.08) 0%, transparent 60%),
  radial-gradient(ellipse 40% 30% at 10% 80%, rgba(124, 106, 247, 0.07) 0%, transparent 50%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 16px 60px;
  color: var(--text);
}

.register-card {
  width: 100%;
  max-width: 440px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 18px;
  overflow: hidden;
  box-shadow: 0 24px 64px rgba(0, 0, 0, 0.5);
}

.card-header {
  padding: 32px 36px 24px;
  border-bottom: 1px solid var(--border);
  background: linear-gradient(135deg, rgba(79, 142, 247, 0.06) 0%, transparent 60%);
}

.logo-mark {
  margin-bottom: 16px;
}

.card-header h1 {
  font-size: 1.65rem;
  font-weight: 700;
  letter-spacing: -0.03em;
  color: var(--text);
  margin-bottom: 6px;
}

.subtitle {
  font-size: 0.82rem;
  color: var(--text-muted);
}

.req {
  color: #ef4444;
  font-size: 0.8em;
}

.register-form {
  padding: 28px 36px 36px;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.field-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

label {
  font-size: 0.78rem;
  font-weight: 500;
  color: #a0a6cc;
  letter-spacing: 0.01em;
}

input {
  background: var(--surface2);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 0.88rem;
  font-family: 'Sora', sans-serif;
  color: var(--text);
  outline: none;
  transition: border-color 0.18s, box-shadow 0.18s;
  width: 100%;
}

input::placeholder {
  color: var(--text-muted);
}

input:focus {
  border-color: var(--border-focus);
  box-shadow: 0 0 0 3px rgba(79, 142, 247, 0.12);
}

input.error {
  border-color: var(--red);
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.pw-input-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.pw-input-wrap input {
  padding-right: 42px;
}

.eye-btn {
  position: absolute;
  right: 10px;
  background: none;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  transition: color 0.15s;
}

.eye-btn:hover {
  color: var(--text);
}

.pw-strength {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 2px;
}

.strength-bars {
  display: flex;
  gap: 5px;
}

.bar {
  width: 40px;
  height: 4px;
  border-radius: 2px;
  background: var(--border);
  transition: background 0.25s;
}

.bar.active.red {
  background: var(--red);
}

.bar.active.yellow {
  background: var(--yellow);
}

.bar.active.green {
  background: var(--green);
}

.strength-label {
  font-size: 0.75rem;
  font-weight: 600;
  font-family: 'JetBrains Mono', monospace;
}

.strength-label.red {
  color: var(--red);
}

.strength-label.yellow {
  color: var(--yellow);
}

.strength-label.green {
  color: var(--green);
}

.pw-match {
  display: flex;
  align-items: center;
  font-size: 0.74rem;
  margin-top: 2px;
}

.match-ok {
  display: flex;
  align-items: center;
  gap: 5px;
  color: var(--green);
}

.match-no {
  display: flex;
  align-items: center;
  gap: 5px;
  color: var(--red);
}

.error-msg {
  font-size: 0.74rem;
  color: var(--red);
}

.submit-area {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 4px;
}

.server-error {
  font-size: 0.8rem;
  color: var(--red);
  background: rgba(239, 68, 68, 0.08);
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 8px;
  padding: 10px 14px;
}

.submit-btn {
  width: 100%;
  padding: 13px;
  background: linear-gradient(135deg, var(--accent) 0%, var(--accent2) 100%);
  border: none;
  border-radius: var(--radius);
  font-family: 'Sora', sans-serif;
  font-size: 0.9rem;
  font-weight: 600;
  color: #fff;
  cursor: pointer;
  transition: opacity 0.18s, transform 0.12s;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 46px;
}

.submit-btn:hover:not(:disabled) {
  opacity: 0.88;
  transform: translateY(-1px);
}

.submit-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  display: inline-block;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.login-hint {
  text-align: center;
  font-size: 0.8rem;
  color: var(--text-muted);
}

.login-hint a {
  color: var(--accent);
  text-decoration: none;
}

.login-hint a:hover {
  text-decoration: underline;
}

@media (max-width: 480px) {
  .register-card {
    border-radius: 12px;
  }

  .card-header, .register-form {
    padding-left: 20px;
    padding-right: 20px;
  }
}
</style>

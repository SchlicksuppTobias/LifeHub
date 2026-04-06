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
        <p class="subtitle">Mit <span class="required-star">*</span> markierte Felder sind Pflichtfelder</p>
      </div>

      <form @submit.prevent="submitForm" class="register-form" novalidate>

        <!-- Persönliche Daten -->
        <fieldset>
          <legend>Persönliche Daten</legend>
          <div class="row-2">
            <div class="field-group">
              <label for="vorname">Vorname <span class="req">*</span></label>
              <input id="vorname" v-model="form.vorname" type="text" placeholder="Max" autocomplete="given-name" :class="{ error: errors.vorname }" />
              <span class="error-msg" v-if="errors.vorname">{{ errors.vorname }}</span>
            </div>
            <div class="field-group">
              <label for="nachname">Nachname <span class="req">*</span></label>
              <input id="nachname" v-model="form.nachname" type="text" placeholder="Mustermann" autocomplete="family-name" :class="{ error: errors.nachname }" />
              <span class="error-msg" v-if="errors.nachname">{{ errors.nachname }}</span>
            </div>
          </div>

          <!-- Geburtstag -->
          <div class="field-group">
            <label for="geburtstag">Geburtstag <span class="optional">(optional)</span></label>
            <div class="birthday-row">
              <select v-model="form.gebTag" :class="{ error: errors.geburtstag }">
                <option value="">Tag</option>
                <option v-for="d in 31" :key="d" :value="String(d).padStart(2,'0')">{{ d }}</option>
              </select>
              <select v-model="form.gebMonat" :class="{ error: errors.geburtstag }">
                <option value="">Monat</option>
                <option v-for="(m, i) in monate" :key="i" :value="String(i+1).padStart(2,'0')">{{ m }}</option>
              </select>
              <input
                v-model="form.gebJahr"
                type="number"
                placeholder="Jahr"
                min="1900"
                :max="new Date().getFullYear()"
                class="year-input"
                :class="{ error: errors.geburtstag }"
              />
            </div>
            <span class="error-msg" v-if="errors.geburtstag">{{ errors.geburtstag }}</span>
          </div>

          <!-- Email -->
          <div class="field-group">
            <label for="email">E-Mail-Adresse <span class="req">*</span></label>
            <input id="email" v-model="form.email" type="email" placeholder="max@beispiel.de" autocomplete="email" :class="{ error: errors.email }" />
            <span class="error-msg" v-if="errors.email">{{ errors.email }}</span>
          </div>
        </fieldset>

        <!-- Adresse -->
        <fieldset>
          <legend>Adresse</legend>
          <div class="row-3">
            <div class="field-group flex-3">
              <label for="strasse">Straße <span class="optional">(optional)</span></label>
              <input id="strasse" v-model="form.strasse" type="text" placeholder="Musterstraße" autocomplete="street-address" :class="{ error: errors.strasse }" />
              <span class="error-msg" v-if="errors.strasse">{{ errors.strasse }}</span>
            </div>
            <div class="field-group flex-1">
              <label for="hausnummer">Nr. <span class="optional">(optional)</span></label>
              <input id="hausnummer" v-model="form.hausnummer" type="text" placeholder="12a" :class="{ error: errors.hausnummer }" />
              <span class="error-msg" v-if="errors.hausnummer">{{ errors.hausnummer }}</span>
            </div>
          </div>
          <div class="row-2">
            <div class="field-group flex-1">
              <label for="plz">Postleitzahl <span class="optional">(optional)</span></label>
              <input id="plz" v-model="form.plz" type="text" placeholder="12345" maxlength="5" autocomplete="postal-code" :class="{ error: errors.plz }" />
              <span class="error-msg" v-if="errors.plz">{{ errors.plz }}</span>
            </div>
            <div class="field-group flex-2">
              <label for="ort">Ort <span class="optional">(optional)</span></label>
              <input id="ort" v-model="form.ort" type="text" placeholder="Musterstadt" autocomplete="address-level2" :class="{ error: errors.ort }" />
              <span class="error-msg" v-if="errors.ort">{{ errors.ort }}</span>
            </div>
          </div>
        </fieldset>

        <!-- Passwort -->
        <fieldset>
          <legend>Sicherheit</legend>

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
            <span class="error-msg" v-if="errors.passwortWdh">{{ errors.passwortWdh }}</span>
          </div>
        </fieldset>

        <!-- Kennwort (Verschlüsselung) -->
        <fieldset class="kennwort-fieldset">
          <legend>Kennwort für private Daten</legend>

          <div class="field-group">
            <div class="kennwort-label-row">
              <label for="kennwort">Kennwort festlegen</label>
              <button type="button" class="help-btn" @click="showKennwortModal = true" aria-label="Was ist das Kennwort?">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              </button>
            </div>
            <div class="pw-input-wrap">
              <input
                id="kennwort"
                v-model="form.kennwort"
                :type="showKennwort ? 'text' : 'password'"
                placeholder="Sicheres Kennwort wählen"
                autocomplete="off"
                :class="{ error: errors.kennwort }"
              />
              <button type="button" class="eye-btn" @click="showKennwort = !showKennwort" :aria-label="showKennwort ? 'Kennwort verbergen' : 'Kennwort anzeigen'">
                <svg v-if="!showKennwort" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
              </button>
            </div>
            <span class="error-msg" v-if="errors.kennwort">{{ errors.kennwort }}</span>
            <p class="kennwort-warning">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              Bei Verlust des Kennworts kann dieses nicht wiederhergestellt werden.
            </p>
          </div>
        </fieldset>

        <!-- Submit -->
        <div class="submit-area">
          <p class="server-error" v-if="serverError">{{ serverError }}</p>
          <button type="submit" class="submit-btn" :disabled="isLoading">
            <span v-if="!isLoading">Konto erstellen</span>
            <span v-else class="spinner"></span>
          </button>
        </div>

      </form>
    </div>

    <!-- Kennwort Modal -->
    <Transition name="modal">
      <div class="modal-backdrop" v-if="showKennwortModal" @click.self="showKennwortModal = false">
        <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="modal-title">
          <button class="modal-close" @click="showKennwortModal = false" aria-label="Schließen">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
          <div class="modal-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </div>
          <h2 id="modal-title">Was ist das Kennwort?</h2>
          <div class="modal-content">
            <p>Das <strong>Kennwort</strong> ist ein separates Geheimnis, das ausschließlich dir bekannt ist und <em>niemals</em> an uns übertragen wird.</p>
            <p>Es wird verwendet, um deine <strong>privaten und sensiblen Daten</strong> lokal in deinem Browser zu verschlüsseln, bevor sie gespeichert werden. Selbst wir als Betreiber können diese Daten ohne dein Kennwort <strong>nicht einsehen oder entschlüsseln</strong>.</p>
            <div class="modal-tip">
              <strong>💡 Wichtiger Hinweis:</strong> Wähle ein Kennwort, das du dir gut merken kannst – oder notiere es an einem sicheren Ort. Bei Verlust gibt es <strong>keine Möglichkeit der Wiederherstellung</strong>.
            </div>
            <p class="modal-diff">
              <strong>Unterschied zum Passwort:</strong><br>
              Das <em>Passwort</em> schützt deinen Login-Zugang. Das <em>Kennwort</em> schützt die Inhalte deiner privaten Daten – eine zusätzliche Sicherheitsschicht.
            </p>
          </div>
          <button class="modal-ok" @click="showKennwortModal = false">Verstanden</button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const monate = ['Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember']

const form = ref({
  vorname: '', nachname: '',
  gebTag: '', gebMonat: '', gebJahr: '',
  email: '',
  strasse: '', hausnummer: '', plz: '', ort: '',
  passwort: '', passwortWdh: '',
  kennwort: ''
})

const errors = ref({})
const serverError = ref('')
const isLoading = ref(false)
const showPw = ref(false)
const showPwWdh = ref(false)
const showKennwort = ref(false)
const showKennwortModal = ref(false)

// Passwort-Stärke
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
  const labels = ['', 'Schwach', 'Mittel', 'Stark']
  return labels[pwStrength.value] || ''
})

function validate() {
  const e = {}
  if (!form.value.vorname.trim()) e.vorname = 'Vorname ist erforderlich'
  if (!form.value.nachname.trim()) e.nachname = 'Nachname ist erforderlich'

  // Geburtstag: nur validieren wenn teilweise ausgefüllt
  const { gebTag, gebMonat, gebJahr } = form.value
  const anyBirthFilled = gebTag || gebMonat || gebJahr
  if (anyBirthFilled) {
    const yearStr = String(gebJahr)
    if (!gebTag || !gebMonat || yearStr.length !== 4)
      e.geburtstag = 'Bitte vollständiges Geburtsdatum angeben'
  }

  if (!form.value.email.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email))
    e.email = 'Gültige E-Mail-Adresse erforderlich'

  // PLZ nur prüfen wenn ausgefüllt
  if (form.value.plz.trim() && !/^\d{5}$/.test(form.value.plz))
    e.plz = 'Gültige PLZ (5 Ziffern) erforderlich'

  if (form.value.passwort.length < 8) e.passwort = 'Passwort muss mindestens 8 Zeichen haben'
  if (form.value.passwort !== form.value.passwortWdh) e.passwortWdh = 'Passwörter stimmen nicht überein'
  if (form.value.kennwort.length < 6) e.kennwort = 'Kennwort muss mindestens 6 Zeichen haben'
  errors.value = e
  return Object.keys(e).length === 0
}

function setAuthCookie(token) {
  const expires = new Date()
  expires.setDate(expires.getDate() + 30) // 30 Tage gültig
  document.cookie = `auth_token=${token}; expires=${expires.toUTCString()}; path=/; SameSite=Strict`
}

async function submitForm() {
  serverError.value = ''
  if (!validate()) return
  isLoading.value = true
  try {
    const { gebTag, gebMonat, gebJahr } = form.value
    const geburtstag = (gebTag && gebMonat && gebJahr)
      ? `${gebJahr}-${gebMonat}-${gebTag}`
      : null

    const payload = {
      vorname: form.value.vorname,
      nachname: form.value.nachname,
      ...(geburtstag && { geburtstag }),
      email: form.value.email,
      ...(form.value.strasse.trim() && { strasse: form.value.strasse }),
      ...(form.value.hausnummer.trim() && { hausnummer: form.value.hausnummer }),
      ...(form.value.plz.trim() && { plz: form.value.plz }),
      ...(form.value.ort.trim() && { ort: form.value.ort }),
      passwort: form.value.passwort,
      kennwort: form.value.kennwort
    }
    const res = await fetch('/api/register.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
    const data = await res.json()
    if (!res.ok) {
      serverError.value = data.message || 'Registrierung fehlgeschlagen. Bitte versuche es erneut.'
    } else {
      // Cookie setzen (token vom Server oder eigenen generieren)
      const token = data.token || data.session_token || 'registered'
      setAuthCookie(token)
      // Weiterleitung zum Dashboard
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

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

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
  background-image:
    radial-gradient(ellipse 60% 40% at 70% 0%, rgba(79,142,247,0.08) 0%, transparent 60%),
    radial-gradient(ellipse 40% 30% at 10% 80%, rgba(124,106,247,0.07) 0%, transparent 50%);
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 40px 16px 60px;
  color: var(--text);
}

.register-card {
  width: 100%;
  max-width: 560px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 18px;
  overflow: hidden;
  box-shadow: 0 24px 64px rgba(0,0,0,0.5);
}

.card-header {
  padding: 32px 36px 24px;
  border-bottom: 1px solid var(--border);
  background: linear-gradient(135deg, rgba(79,142,247,0.06) 0%, transparent 60%);
}

.logo-mark { margin-bottom: 16px; }

.card-header h1 {
  font-size: 1.75rem;
  font-weight: 700;
  letter-spacing: -0.03em;
  color: var(--text);
  margin-bottom: 6px;
}

.req { color: #ef4444; font-size: 0.8em; }
.optional { color: var(--text-muted); font-size: 0.75em; font-weight: 400; }
.required-star { color: #ef4444; }

.subtitle { font-size: 0.82rem; color: var(--text-muted); }

.register-form { padding: 28px 36px 36px; }

fieldset {
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 20px 20px 16px;
  margin-bottom: 20px;
}

legend {
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--accent);
  padding: 0 8px;
}

.kennwort-fieldset {
  border-color: rgba(124,106,247,0.35);
  background: rgba(124,106,247,0.03);
}

.kennwort-fieldset legend { color: var(--accent2); }

.field-group {
  display: flex;
  flex-direction: column;
  margin-bottom: 14px;
}
.field-group:last-child { margin-bottom: 0; }

label {
  font-size: 0.78rem;
  font-weight: 500;
  color: #a0a6cc;
  margin-bottom: 6px;
  letter-spacing: 0.01em;
}

input, select {
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
  -webkit-appearance: none;
  appearance: none;
}

input::placeholder { color: var(--text-muted); }

input:focus, select:focus {
  border-color: var(--border-focus);
  box-shadow: 0 0 0 3px rgba(79,142,247,0.12);
}

input.error, select.error {
  border-color: var(--red);
  box-shadow: 0 0 0 3px rgba(239,68,68,0.1);
}

select {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7099' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  padding-right: 36px;
  cursor: pointer;
}

select option { background: var(--surface2); }

.row-2 { display: flex; gap: 14px; }
.row-2 .field-group { flex: 1; }
.row-3 { display: flex; gap: 14px; margin-bottom: 0; }
.flex-3 { flex: 3; }
.flex-1 { flex: 1; }
.flex-2 { flex: 2; }
.row-3 .field-group { margin-bottom: 14px; }

.birthday-row {
  display: flex;
  gap: 10px;
}

.birthday-row select { flex: 1; }
.year-input { flex: 1.2; }

/* Password input */
.pw-input-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.pw-input-wrap input { padding-right: 42px; }

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
.eye-btn:hover { color: var(--text); }

/* Password strength */
.pw-strength {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 8px;
}

.strength-bars {
  display: flex;
  gap: 5px;
}

.bar {
  width: 36px;
  height: 4px;
  border-radius: 2px;
  background: var(--border);
  transition: background 0.25s;
}
.bar.active.red { background: var(--red); }
.bar.active.yellow { background: var(--yellow); }
.bar.active.green { background: var(--green); }

.strength-label {
  font-size: 0.75rem;
  font-weight: 600;
  font-family: 'JetBrains Mono', monospace;
}
.strength-label.red { color: var(--red); }
.strength-label.yellow { color: var(--yellow); }
.strength-label.green { color: var(--green); }

/* Error messages */
.error-msg {
  font-size: 0.74rem;
  color: var(--red);
  margin-top: 5px;
}

/* Kennwort label row */
.kennwort-label-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 6px;
}

.kennwort-label-row label { margin-bottom: 0; }

.help-btn {
  background: none;
  border: 1px solid var(--border);
  border-radius: 50%;
  width: 22px;
  height: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--accent2);
  cursor: pointer;
  padding: 0;
  transition: background 0.15s, border-color 0.15s;
  flex-shrink: 0;
}
.help-btn:hover {
  background: rgba(124,106,247,0.12);
  border-color: var(--accent2);
}

/* Kennwort warning */
.kennwort-warning {
  display: flex;
  align-items: flex-start;
  gap: 5px;
  font-size: 0.72rem;
  color: var(--red);
  margin-top: 8px;
  line-height: 1.5;
}
.kennwort-warning svg { flex-shrink: 0; margin-top: 1px; }

/* Submit */
.submit-area { margin-top: 8px; }

.server-error {
  font-size: 0.8rem;
  color: var(--red);
  background: rgba(239,68,68,0.08);
  border: 1px solid rgba(239,68,68,0.2);
  border-radius: 8px;
  padding: 10px 14px;
  margin-bottom: 14px;
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
  letter-spacing: 0.01em;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 46px;
}

.submit-btn:hover:not(:disabled) {
  opacity: 0.88;
  transform: translateY(-1px);
}

.submit-btn:disabled { opacity: 0.55; cursor: not-allowed; }

.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  display: inline-block;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* Modal */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.7);
  backdrop-filter: blur(6px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-box {
  background: var(--surface);
  border: 1px solid rgba(124,106,247,0.3);
  border-radius: 16px;
  padding: 32px 28px 28px;
  max-width: 460px;
  width: 100%;
  position: relative;
  box-shadow: 0 32px 80px rgba(0,0,0,0.6), 0 0 0 1px rgba(124,106,247,0.1);
}

.modal-close {
  position: absolute;
  top: 14px;
  right: 14px;
  background: none;
  border: 1px solid var(--border);
  border-radius: 6px;
  color: var(--text-muted);
  cursor: pointer;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.15s, background 0.15s;
}
.modal-close:hover { color: var(--text); background: var(--surface2); }

.modal-icon {
  width: 52px;
  height: 52px;
  border-radius: 12px;
  background: linear-gradient(135deg, rgba(124,106,247,0.18), rgba(79,142,247,0.12));
  border: 1px solid rgba(124,106,247,0.25);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--accent2);
  margin-bottom: 16px;
}

.modal-box h2 {
  font-size: 1.2rem;
  font-weight: 700;
  margin-bottom: 16px;
  letter-spacing: -0.02em;
}

.modal-content p {
  font-size: 0.85rem;
  color: #b0b5d8;
  line-height: 1.7;
  margin-bottom: 12px;
}

.modal-content strong { color: var(--text); font-weight: 600; }
.modal-content em { color: #d4d8f5; font-style: normal; font-weight: 500; }

.modal-tip {
  background: rgba(79,142,247,0.08);
  border: 1px solid rgba(79,142,247,0.18);
  border-radius: 8px;
  padding: 12px 14px;
  font-size: 0.82rem;
  color: #b8c4f0;
  line-height: 1.6;
  margin-bottom: 12px;
}

.modal-diff {
  font-size: 0.82rem !important;
  background: var(--surface2);
  border-radius: 8px;
  padding: 12px 14px;
  border: 1px solid var(--border);
  color: #9098c0 !important;
}

.modal-ok {
  margin-top: 20px;
  width: 100%;
  padding: 11px;
  background: linear-gradient(135deg, var(--accent2), var(--accent));
  border: none;
  border-radius: 8px;
  font-family: 'Sora', sans-serif;
  font-size: 0.88rem;
  font-weight: 600;
  color: #fff;
  cursor: pointer;
  transition: opacity 0.15s;
}
.modal-ok:hover { opacity: 0.85; }

/* Modal transition */
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-active .modal-box, .modal-leave-active .modal-box { transition: transform 0.2s ease, opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-from .modal-box, .modal-leave-to .modal-box { transform: scale(0.95) translateY(8px); opacity: 0; }

@media (max-width: 500px) {
  .register-card { border-radius: 12px; }
  .card-header, .register-form { padding-left: 20px; padding-right: 20px; }
  .row-2, .row-3 { flex-direction: column; gap: 0; }
  .birthday-row { flex-wrap: wrap; }
}
</style>

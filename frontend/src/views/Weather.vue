```vue
<template>
  <div class="weather-page" :class="bgClass">

    <!-- Back -->
    <button class="back-btn" @click="router.push('/dashboard')">← Zurück</button>

    <!-- Location Search -->
    <div class="search-area">
      <div class="search-box" :class="{ focused: focused }">
        <span class="search-icon">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
            <circle cx="12" cy="10" r="3"/>
          </svg>
        </span>
        <input
          v-model="locationInput"
          type="text"
          placeholder="Stadt oder PLZ eingeben…"
          @focus="focused = true"
          @blur="focused = false"
          @keyup.enter="saveLocation"
        />
        <button class="search-submit" @click="saveLocation" :disabled="saving">
          <svg v-if="!saving" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
          </svg>
          <span v-else class="dot-spin"></span>
        </button>
      </div>
      <p v-if="errorMsg" class="error-msg">{{ errorMsg }}</p>
    </div>

    <!-- Loading initial -->
    <div v-if="loading" class="center-state">
      <div class="loader">
        <span></span><span></span><span></span>
      </div>
      <p>Wetterdaten werden geladen…</p>
    </div>

    <!-- No location yet -->
    <div v-else-if="!weather" class="center-state">
      <div class="empty-icon">🌍</div>
      <p class="empty-text">Gib eine Stadt oder PLZ ein<br>um das aktuelle Wetter zu sehen.</p>
    </div>

    <!-- Weather Data -->
    <template v-else>

      <!-- Hero -->
      <div class="hero">
        <div class="hero-left">
          <div class="location-name">{{ weather.location.query }}</div>
          <div class="weather-icon-big">{{ weather.current.icon }}</div>
          <div class="temp-big">
            {{ weather.current.temp }}<span class="deg">°C</span>
          </div>
          <div class="weather-label">{{ weather.current.label }}</div>
          <div class="feels-like">Gefühlt {{ weather.current.feels_like }}°C</div>
        </div>

        <div class="hero-right">
          <div class="stat-card">
            <span class="stat-icon">💧</span>
            <span class="stat-val">{{ weather.current.humidity }}%</span>
            <span class="stat-label">Luftfeuchtigkeit</span>
          </div>
          <div class="stat-card">
            <span class="stat-icon">💨</span>
            <span class="stat-val">{{ weather.current.wind_speed }} km/h</span>
            <span class="stat-label">Wind {{ weather.current.wind_dir }}</span>
          </div>
          <div class="stat-card">
            <span class="stat-icon">🌧️</span>
            <span class="stat-val">{{ weather.current.precip }} mm</span>
            <span class="stat-label">Niederschlag</span>
          </div>
          <div class="stat-card">
            <span class="stat-icon">🌅</span>
            <span class="stat-val">{{ weather.forecast[0].sunrise }}</span>
            <span class="stat-label">Sonnenaufgang</span>
          </div>
          <div class="stat-card">
            <span class="stat-icon">🌇</span>
            <span class="stat-val">{{ weather.forecast[0].sunset }}</span>
            <span class="stat-label">Sonnenuntergang</span>
          </div>
        </div>
      </div>

      <!-- 7-Day Forecast -->
      <div class="forecast-title">7-Tage-Vorschau</div>
      <div class="forecast-strip">
        <div
          v-for="(day, i) in weather.forecast"
          :key="day.date"
          class="forecast-day"
          :class="{ today: i === 0 }"
        >
          <div class="fc-weekday">{{ i === 0 ? 'Heute' : day.weekday }}</div>
          <div class="fc-icon">{{ day.icon }}</div>
          <div class="fc-max">{{ day.max }}°</div>
          <div class="fc-min">{{ day.min }}°</div>
          <div class="fc-precip" v-if="day.precip > 0">{{ day.precip }}mm</div>
        </div>
      </div>

    </template>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router       = useRouter()
const weather      = ref(null)
const loading      = ref(false)
const saving       = ref(false)
const errorMsg     = ref('')
const locationInput = ref('')
const focused      = ref(false)

// Dynamic background based on weather + day/night
const bgClass = computed(() => {
  if (!weather.value) return 'bg-default'
  const code  = weather.value.current.code
  const isDay = weather.value.current.is_day
  if (code === 0 || code === 1) return isDay ? 'bg-clear-day' : 'bg-clear-night'
  if (code <= 3)                return isDay ? 'bg-cloudy-day' : 'bg-cloudy-night'
  if (code >= 95)               return 'bg-storm'
  if (code >= 71)               return 'bg-snow'
  if (code >= 51)               return 'bg-rain'
  return isDay ? 'bg-clear-day' : 'bg-clear-night'
})

onMounted(async () => {
  loading.value = true
  try {
    const res  = await fetch('/api/weather.php')
    const data = await res.json()
    if (data.success && data.location) {
      weather.value = data
      locationInput.value = data.location.query
    }
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})

async function saveLocation() {
  const q = locationInput.value.trim()
  if (!q) return
  saving.value  = true
  errorMsg.value = ''
  try {
    const res  = await fetch('/api/weather.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ location: q })
    })
    const data = await res.json()
    if (!data.success) {
      errorMsg.value = data.error || 'Fehler beim Laden.'
      return
    }
    weather.value = data
    locationInput.value = data.location.query
  } catch (e) {
    errorMsg.value = 'Netzwerkfehler.'
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Playfair+Display:ital@1&display=swap');

.weather-page {
  min-height: 100vh;
  padding: 28px 24px 60px;
  font-family: 'Outfit', sans-serif;
  color: #fff;
  transition: background 1s ease;
  position: relative;
  max-width: 860px;
  margin: 0 auto;
}

/* ── Backgrounds ── */
.bg-default      { background: linear-gradient(160deg, #1a2a4a 0%, #0d1b2e 100%); }
.bg-clear-day    { background: linear-gradient(160deg, #1e88e5 0%, #039be5 50%, #f9a825 100%); }
.bg-clear-night  { background: linear-gradient(160deg, #0d1b3e 0%, #1a237e 60%, #311b92 100%); }
.bg-cloudy-day   { background: linear-gradient(160deg, #546e7a 0%, #78909c 60%, #90a4ae 100%); }
.bg-cloudy-night { background: linear-gradient(160deg, #263238 0%, #37474f 100%); }
.bg-rain         { background: linear-gradient(160deg, #1a237e 0%, #283593 50%, #37474f 100%); }
.bg-snow         { background: linear-gradient(160deg, #546e7a 0%, #b0bec5 60%, #eceff1 100%); color: #263238; }
.bg-storm        { background: linear-gradient(160deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); }

/* ── Back Button ── */
.back-btn {
  background: rgba(255,255,255,0.15);
  border: 1px solid rgba(255,255,255,0.25);
  color: inherit;
  border-radius: 10px;
  padding: 8px 16px;
  cursor: pointer;
  font-family: 'Outfit', sans-serif;
  font-size: 14px;
  backdrop-filter: blur(6px);
  transition: background 0.2s;
  margin-bottom: 28px;
  display: inline-block;
}
.back-btn:hover { background: rgba(255,255,255,0.25); }

/* ── Search ── */
.search-area { margin-bottom: 36px; }

.search-box {
  display: flex;
  align-items: center;
  background: rgba(255,255,255,0.15);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: 16px;
  padding: 0 6px 0 16px;
  gap: 10px;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.search-box.focused {
  border-color: rgba(255,255,255,0.5);
  box-shadow: 0 0 0 3px rgba(255,255,255,0.1);
}

.search-icon { opacity: 0.7; display: flex; flex-shrink: 0; }

.search-box input {
  flex: 1;
  background: transparent;
  border: none;
  outline: none;
  color: inherit;
  font-family: 'Outfit', sans-serif;
  font-size: 15px;
  padding: 14px 0;
}
.search-box input::placeholder { opacity: 0.5; }

.search-submit {
  background: rgba(255,255,255,0.2);
  border: none;
  border-radius: 10px;
  width: 38px;
  height: 38px;
  cursor: pointer;
  color: inherit;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s;
  flex-shrink: 0;
}
.search-submit:hover:not(:disabled) { background: rgba(255,255,255,0.35); }
.search-submit:disabled { opacity: 0.5; cursor: default; }

.dot-spin {
  display: inline-block;
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.error-msg {
  margin-top: 10px;
  font-size: 13px;
  color: #ffcdd2;
  padding: 8px 12px;
  background: rgba(229,57,53,0.25);
  border-radius: 8px;
  border: 1px solid rgba(229,57,53,0.4);
}

/* ── Loading / Empty ── */
.center-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 80px 20px;
  gap: 16px;
  opacity: 0.8;
}

.loader {
  display: flex;
  gap: 8px;
}
.loader span {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: rgba(255,255,255,0.7);
  animation: bounce 1s infinite ease-in-out;
}
.loader span:nth-child(2) { animation-delay: 0.15s; }
.loader span:nth-child(3) { animation-delay: 0.3s; }

@keyframes bounce {
  0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
  40%           { transform: scale(1);   opacity: 1; }
}

.empty-icon { font-size: 3rem; }
.empty-text { text-align: center; opacity: 0.7; line-height: 1.7; font-size: 15px; }

/* ── Hero ── */
.hero {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 32px;
  margin-bottom: 48px;
  flex-wrap: wrap;
}

.hero-left {
  flex: 1;
  min-width: 200px;
}

.location-name {
  font-size: 15px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  opacity: 0.7;
  margin-bottom: 12px;
}

.weather-icon-big {
  font-size: 4.5rem;
  line-height: 1;
  margin-bottom: 12px;
}

.temp-big {
  font-family: 'Outfit', sans-serif;
  font-size: 6rem;
  font-weight: 700;
  line-height: 1;
  letter-spacing: -3px;
}
.deg {
  font-size: 3rem;
  font-weight: 300;
  vertical-align: super;
}

.weather-label {
  font-size: 1.3rem;
  font-weight: 400;
  margin-top: 8px;
  opacity: 0.9;
}

.feels-like {
  font-size: 13px;
  opacity: 0.6;
  margin-top: 4px;
}

/* ── Stat Cards ── */
.hero-right {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  flex-shrink: 0;
}

.stat-card {
  background: rgba(255,255,255,0.12);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255,255,255,0.15);
  border-radius: 14px;
  padding: 14px 16px;
  display: flex;
  flex-direction: column;
  gap: 3px;
  min-width: 110px;
}

.stat-icon { font-size: 1.2rem; }
.stat-val  { font-size: 1rem; font-weight: 600; }
.stat-label { font-size: 11px; opacity: 0.6; text-transform: uppercase; letter-spacing: 0.06em; }

/* ── Forecast ── */
.forecast-title {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  opacity: 0.6;
  margin-bottom: 14px;
}

.forecast-strip {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 8px;
}

.forecast-day {
  background: rgba(255,255,255,0.1);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255,255,255,0.12);
  border-radius: 14px;
  padding: 14px 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 5px;
  transition: background 0.2s;
}
.forecast-day:hover { background: rgba(255,255,255,0.18); }
.forecast-day.today {
  background: rgba(255,255,255,0.22);
  border-color: rgba(255,255,255,0.35);
}

.fc-weekday  { font-size: 11px; font-weight: 600; opacity: 0.7; text-transform: uppercase; }
.fc-icon     { font-size: 1.6rem; }
.fc-max      { font-size: 15px; font-weight: 700; }
.fc-min      { font-size: 12px; opacity: 0.55; }
.fc-precip   { font-size: 11px; opacity: 0.6; color: #90caf9; }

/* ── Responsive ── */
@media (max-width: 640px) {
  .weather-page { padding: 20px 16px 48px; }
  .temp-big     { font-size: 4.5rem; }
  .hero-right   { grid-template-columns: 1fr 1fr; }
  .forecast-strip { grid-template-columns: repeat(4, 1fr); }
  .forecast-day:nth-child(n+5) { display: none; }
}
</style>
```

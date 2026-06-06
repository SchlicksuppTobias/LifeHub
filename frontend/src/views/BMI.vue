<script setup>
import { ref, computed } from 'vue'

const unit = ref('metric')
const heightCm = ref(175)
const weightKg = ref(75)
const heightFt = ref(5)
const heightIn = ref(9)
const weightLbs = ref(165)

const bmi = computed(() => {
  let h, w
  if (unit.value === 'metric') {
    h = heightCm.value / 100
    w = weightKg.value
  } else {
    const totalIn = heightFt.value * 12 + heightIn.value
    h = totalIn * 0.0254
    w = weightLbs.value * 0.453592
  }
  if (!h || !w) return null
  return w / (h * h)
})

const categoryInfo = computed(() => {
  const b = bmi.value
  if (b === null) return null
  if (b < 18.5) return { label: 'Untergewicht', cls: 'cat-underweight', color: '#1d4ed8', tip: 'Dein BMI liegt unter 18,5. Ausgewogene, nährstoffreiche Ernährung kann helfen, ein gesundes Gewicht zu erreichen.', bmiClass: 'Klasse I' }
  if (b < 25)   return { label: 'Normalgewicht', cls: 'cat-normal',      color: '#2d6a4f', tip: 'Dein BMI liegt im gesunden Bereich. Halte deinen aktiven Lebensstil bei und ernähre dich ausgewogen.', bmiClass: 'Normal' }
  if (b < 30)   return { label: 'Übergewicht',   cls: 'cat-overweight',  color: '#b45309', tip: 'Leichtes Übergewicht kann das Risiko für Herzerkrankungen erhöhen. Kleine Änderungen in Ernährung und Bewegung helfen bereits.', bmiClass: 'Klasse I' }
  if (b < 35)   return { label: 'Adipositas I',  cls: 'cat-obese',       color: '#b91c1c', tip: 'BMI über 30. Ein Gespräch mit deinem Arzt über gesunde Gewichtsabnahme wird empfohlen.', bmiClass: 'Klasse II' }
  return          { label: 'Adipositas II', cls: 'cat-obese',       color: '#b91c1c', tip: 'BMI über 35. Bitte konsultiere einen Arzt für eine individuelle Beratung und Unterstützung.', bmiClass: 'Klasse III' }
})

const categoryLabel = computed(() => categoryInfo.value?.label ?? '')
const categoryClass  = computed(() => categoryInfo.value?.cls ?? '')
const categoryColor  = computed(() => categoryInfo.value?.color ?? '#1a1714')
const tip            = computed(() => categoryInfo.value?.tip ?? '')
const bmiClass       = computed(() => categoryInfo.value?.bmiClass ?? '')

const idealWeightRange = computed(() => {
  let h
  if (unit.value === 'metric') {
    h = heightCm.value / 100
  } else {
    const totalIn = heightFt.value * 12 + heightIn.value
    h = totalIn * 0.0254
  }
  const low  = (18.5 * h * h).toFixed(0)
  const high = (24.9 * h * h).toFixed(0)
  return unit.value === 'metric'
    ? `${low}–${high} kg`
    : `${Math.round(low * 2.205)}–${Math.round(high * 2.205)} lbs`
})

const needlePos = computed(() => {
  const b = bmi.value
  if (b === null) return 0
  const min = 10, max = 45
  return Math.min(100, Math.max(0, ((b - min) / (max - min)) * 100))
})
</script>

<template>
  <div class="container">
    <!-- Header -->
    <div class="header">
      <div class="header-label">Gesundheits-Tool</div>
      <h1>Body-Mass<br /><em>Index</em></h1>
      <p>Berechne deinen BMI anhand von Größe und Gewicht — schnell, genau und ohne Anmeldung.</p>
    </div>

    <!-- Input Card -->
    <div class="card">
      <div class="toggle-group">
        <button class="toggle-btn" :class="{ active: unit === 'metric' }" @click="unit = 'metric'">Metrisch</button>
        <button class="toggle-btn" :class="{ active: unit === 'imperial' }" @click="unit = 'imperial'">Imperial</button>
      </div>

      <!-- Metrisch -->
      <template v-if="unit === 'metric'">
        <div class="field">
          <label>Größe</label>
          <div class="input-row">
            <div class="slider-wrap">
              <input type="range" min="120" max="220" step="1" v-model.number="heightCm" />
            </div>
            <div class="value-display">
              <span class="value-num">{{ heightCm }}</span>
              <span class="value-unit">cm</span>
            </div>
          </div>
        </div>
        <div class="divider" />
        <div class="field">
          <label>Gewicht</label>
          <div class="input-row">
            <div class="slider-wrap">
              <input type="range" min="30" max="200" step="0.5" v-model.number="weightKg" />
            </div>
            <div class="value-display">
              <span class="value-num">{{ weightKg }}</span>
              <span class="value-unit">kg</span>
            </div>
          </div>
        </div>
      </template>

      <!-- Imperial -->
      <template v-else>
        <div class="field">
          <label>Größe</label>
          <div class="input-row">
            <div class="height-inputs">
              <div class="ft-in-field">
                <span>Fuß (ft)</span>
                <input class="num-input" type="number" min="3" max="8" v-model.number="heightFt" />
              </div>
              <div class="ft-in-field">
                <span>Zoll (in)</span>
                <input class="num-input" type="number" min="0" max="11" v-model.number="heightIn" />
              </div>
            </div>
          </div>
        </div>
        <div class="divider" />
        <div class="field">
          <label>Gewicht</label>
          <div class="input-row">
            <div class="slider-wrap">
              <input type="range" min="66" max="440" step="1" v-model.number="weightLbs" />
            </div>
            <div class="value-display">
              <span class="value-num">{{ weightLbs }}</span>
              <span class="value-unit">lbs</span>
            </div>
          </div>
        </div>
      </template>
    </div>

    <!-- Result Card -->
    <div class="result-card" :class="{ 'has-result': bmi !== null }">
      <template v-if="bmi !== null">
        <div class="result-header">
          <div class="bmi-big" :style="{ color: categoryColor }">{{ bmi.toFixed(1) }}</div>
          <div class="bmi-label-stack">
            <div class="bmi-category" :class="categoryClass">{{ categoryLabel }}</div>
            <div class="bmi-sub">kg/m²</div>
          </div>
        </div>

        <div class="gauge-track">
          <div class="gauge-needle" :style="{ left: needlePos + '%' }" />
        </div>
        <div class="gauge-labels">
          <span>Untergewicht</span>
          <span>Normal</span>
          <span>Übergewicht</span>
          <span>Adipositas</span>
        </div>

        <div class="info-grid">
          <div class="info-tile">
            <div class="tile-label">Idealgewicht</div>
            <div class="tile-val">{{ idealWeightRange }}</div>
          </div>
          <div class="info-tile">
            <div class="tile-label">BMI-Klasse</div>
            <div class="tile-val">{{ bmiClass }}</div>
          </div>
        </div>

        <div v-if="tip" class="tip">{{ tip }}</div>
      </template>

      <template v-else>
        <div class="empty-state">
          <div class="empty-icon">⚖️</div>
          <p>Bewege die Regler, um deinen BMI zu berechnen.</p>
        </div>
      </template>
    </div>
  </div>
</template>

<style scoped>
.container {
  font-family: 'DM Sans', sans-serif;
  max-width: 520px;
  width: 100%;
  margin: 0 auto;
  padding: 3rem 1.25rem 4rem;
  color: #1a1714;

  --bg: #f5f0e8;
  --surface: #faf7f2;
  --card: #ffffff;
  --text: #1a1714;
  --muted: #7a7068;
  --border: #e0d9cf;
  --accent: #2d6a4f;
  --accent-light: #d8f3dc;
  --accent-muted: #74c69d;
  --track-bg: linear-gradient(
    to right,
    #4895ef 0%, #4895ef 18.5%,
    #2d6a4f 18.5%, #2d6a4f 60%,
    #f4a261 60%, #f4a261 75%,
    #e63946 75%, #e63946 100%
  );
}

/* ── Header ── */
.header { margin-bottom: 2.5rem; }

.header-label {
  font-size: 11px;
  font-weight: 500;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--accent);
  margin-bottom: 0.5rem;
}

.header h1 {
  font-family: 'DM Serif Display', serif;
  font-size: 3rem;
  font-weight: 400;
  line-height: 1.05;
  color: var(--text);
}

.header h1 em {
  font-style: italic;
  color: var(--accent);
}

.header p {
  margin-top: 0.75rem;
  font-size: 14px;
  color: var(--muted);
  line-height: 1.6;
  font-weight: 300;
}

/* ── Input Card ── */
.card {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 20px;
  padding: 2rem;
  margin-bottom: 1rem;
}

.toggle-group {
  display: flex;
  gap: 6px;
  background: var(--bg);
  border-radius: 10px;
  padding: 4px;
  margin-bottom: 2rem;
  width: fit-content;
}

.toggle-btn {
  padding: 6px 20px;
  border: none;
  background: transparent;
  border-radius: 8px;
  font-family: 'DM Sans', sans-serif;
  font-size: 13px;
  font-weight: 500;
  color: var(--muted);
  cursor: pointer;
  transition: all 0.18s;
}

.toggle-btn.active {
  background: var(--card);
  color: var(--text);
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
}

.field { margin-bottom: 1.75rem; }
.field:last-child { margin-bottom: 0; }

.field label {
  display: block;
  font-size: 12px;
  font-weight: 500;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--muted);
  margin-bottom: 0.75rem;
}

.input-row {
  display: flex;
  align-items: center;
  gap: 12px;
}

.slider-wrap { flex: 1; }

input[type='range'] {
  -webkit-appearance: none;
  appearance: none;
  width: 100%;
  height: 3px;
  border-radius: 2px;
  background: var(--border);
  outline: none;
  cursor: pointer;
}

input[type='range']::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: var(--card);
  border: 2px solid var(--accent);
  box-shadow: 0 2px 8px rgba(45, 106, 79, 0.18);
  cursor: pointer;
  transition: transform 0.15s;
}

input[type='range']::-webkit-slider-thumb:hover { transform: scale(1.15); }

input[type='range']::-moz-range-thumb {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: var(--card);
  border: 2px solid var(--accent);
  cursor: pointer;
}

.value-display {
  display: flex;
  align-items: baseline;
  gap: 3px;
  min-width: 80px;
  justify-content: flex-end;
}

.value-num {
  font-family: 'DM Serif Display', serif;
  font-size: 2rem;
  line-height: 1;
  color: var(--text);
}

.value-unit {
  font-size: 12px;
  font-weight: 500;
  color: var(--muted);
}

.height-inputs {
  display: flex;
  gap: 10px;
  flex: 1;
}

.ft-in-field {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.ft-in-field span {
  font-size: 11px;
  color: var(--muted);
  margin-bottom: 4px;
  font-weight: 500;
}

.num-input {
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 10px 12px;
  font-family: 'DM Serif Display', serif;
  font-size: 1.25rem;
  color: var(--text);
  background: var(--surface);
  width: 100%;
  outline: none;
  text-align: center;
  transition: border-color 0.2s;
}

.num-input:focus { border-color: var(--accent); }

.divider {
  height: 1px;
  background: var(--border);
  margin: 1.5rem 0;
}

/* ── Result Card ── */
.result-card {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 20px;
  padding: 2rem;
  overflow: hidden;
  transition: border-color 0.3s;
}

.result-card.has-result { border-color: var(--accent-muted); }

.result-header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  margin-bottom: 1.5rem;
}

.bmi-big {
  font-family: 'DM Serif Display', serif;
  font-size: 5rem;
  line-height: 1;
  transition: color 0.4s;
}

.bmi-label-stack { text-align: right; }

.bmi-category {
  font-size: 14px;
  font-weight: 500;
  padding: 4px 12px;
  border-radius: 20px;
  display: inline-block;
  margin-bottom: 4px;
}

.bmi-sub {
  font-size: 11px;
  color: var(--muted);
}

.cat-underweight { background: #dbeafe; color: #1d4ed8; }
.cat-normal      { background: var(--accent-light); color: var(--accent); }
.cat-overweight  { background: #fef3c7; color: #b45309; }
.cat-obese       { background: #fee2e2; color: #b91c1c; }

/* ── Gauge ── */
.gauge-track {
  height: 6px;
  border-radius: 4px;
  background: var(--track-bg);
  position: relative;
  margin-bottom: 0.5rem;
}

.gauge-needle {
  position: absolute;
  top: 50%;
  transform: translate(-50%, -50%);
  width: 14px;
  height: 14px;
  background: var(--text);
  border: 2px solid var(--card);
  border-radius: 50%;
  transition: left 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
}

.gauge-labels {
  display: flex;
  justify-content: space-between;
  font-size: 10px;
  color: var(--muted);
  font-weight: 500;
}

/* ── Info Tiles ── */
.info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-top: 1.5rem;
}

.info-tile {
  background: var(--surface);
  border-radius: 12px;
  padding: 14px;
}

.tile-label {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  font-weight: 500;
  color: var(--muted);
  margin-bottom: 4px;
}

.tile-val {
  font-family: 'DM Serif Display', serif;
  font-size: 1.4rem;
  color: var(--text);
}

/* ── Tip ── */
.tip {
  margin-top: 1rem;
  font-size: 12px;
  color: var(--muted);
  line-height: 1.6;
  padding: 12px 14px;
  background: var(--bg);
  border-radius: 10px;
  border-left: 3px solid var(--accent-muted);
}

/* ── Empty State ── */
.empty-state {
  text-align: center;
  padding: 2rem 0 1rem;
}

.empty-icon {
  width: 56px;
  height: 56px;
  background: var(--bg);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
  font-size: 24px;
}

.empty-state p {
  font-size: 14px;
  color: var(--muted);
}

/* ── Responsive ── */
@media (max-width: 480px) {
  .container { padding: 2rem 1rem 3rem; }
  .header h1 { font-size: 2.25rem; }
  .bmi-big { font-size: 3.5rem; }
}
</style>

<template>
  <div class="app">
    <header class="app-header">
      <div class="header-inner">
        <div class="logo">
          <span class="logo-icon">◈</span>
          <span class="logo-text">NutriBase</span>
        </div>
        <p class="tagline">Nährwertdaten auf einen Blick</p>
        <p class="per100">Alle Angaben pro 100 g</p>
      </div>
    </header>

    <main class="main">
      <div class="search-wrapper">
        <div class="search-box" :class="{ focused: searchFocused }">
          <span class="search-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
          </span>
          <input
            v-model="searchQuery"
            @focus="searchFocused = true"
            @blur="searchFocused = false"
            @input="onSearch"
            type="text"
            placeholder="Lebensmittel suchen…"
            class="search-input"
            autocomplete="off"
          />
          <button v-if="searchQuery" @click="clearSearch" class="clear-btn" title="Leeren">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
        <div class="result-count" v-if="!loading">
          <span>{{ items.length }} Einträge</span>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="state-box">
        <div class="spinner"><span></span><span></span><span></span></div>
        <p>Lade Daten…</p>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="state-box error-state">
        <span class="state-icon">⚠</span>
        <p>{{ error }}</p>
        <button @click="fetchData" class="retry-btn">Erneut versuchen</button>
      </div>

      <!-- Empty -->
      <div v-else-if="items.length === 0" class="state-box">
        <span class="state-icon">∅</span>
        <p>Keine Ergebnisse für <strong>„{{ searchQuery }}"</strong></p>
      </div>

      <!-- Cards -->
      <div v-else class="cards-grid">
        <div
          v-for="(item, i) in sortedItems"
          :key="i"
          class="card"
          :class="{ expanded: expandedIndex === i }"
        >
          <!-- Card Header -->
          <div class="card-header" @click="toggle(i)">
            <div class="card-title-block">
<!--              <span class="card-code">{{ item.bls_code }}</span>-->
              <h3 class="card-name">{{ item.name_de }}</h3>
              <span class="card-name-en">{{ item.name_en }}</span>
            </div>
            <div class="card-macros">
              <div class="macro">
                <span class="macro-val">{{ fmt(item.energy_kcal) }}</span>
                <span class="macro-label">kcal</span>
              </div>
              <div class="macro">
                <span class="macro-val">{{ fmt(item.protein) }}</span>
                <span class="macro-label">Eiweiß g</span>
              </div>
              <div class="macro">
                <span class="macro-val">{{ fmt(item.fat) }}</span>
                <span class="macro-label">Fett g</span>
              </div>
              <div class="macro">
                <span class="macro-val">{{ fmt(item.carbohydrates) }}</span>
                <span class="macro-label">KH g</span>
              </div>
            </div>
            <span class="expand-icon">{{ expandedIndex === i ? '▲' : '▼' }}</span>
          </div>

          <!-- Expanded Details -->
          <div v-if="expandedIndex === i" class="card-details">
            <div class="detail-group" v-for="group in detailGroups" :key="group.label">
              <div class="group-title">{{ group.label }}</div>
              <div class="group-rows">
                <div
                  v-for="col in group.cols"
                  :key="col.key"
                  class="detail-row"
                  v-show="item[col.key] != null && item[col.key] !== ''"
                >
                  <span class="detail-label">{{ col.label }}</span>
                  <span class="detail-val">
                    {{ fmtVal(item[col.key]) }}
                    <span class="detail-unit" v-if="col.unit">{{ col.unit }}</span>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const items         = ref([])
const loading       = ref(false)
const error         = ref(null)
const searchQuery   = ref('')
const searchFocused = ref(false)
const expandedIndex = ref(null)
let   debounceTimer = null

// ── Detail-Gruppen ─────────────────────────────────────────────────────
const detailGroups = [
  {
    label: 'Energie & Grundnährstoffe',
    cols: [
      { key: 'energy_kj',      label: 'Energie',         unit: 'kJ'   },
      { key: 'energy_kcal',    label: 'Energie',          unit: 'kcal' },
      { key: 'water',          label: 'Wasser',           unit: 'g'    },
      { key: 'protein',        label: 'Eiweiß',           unit: 'g'    },
      { key: 'fat',            label: 'Fett',             unit: 'g'    },
      { key: 'carbohydrates',  label: 'Kohlenhydrate',    unit: 'g'    },
      { key: 'fiber',          label: 'Ballaststoffe',    unit: 'g'    },
      { key: 'alcohol',        label: 'Alkohol',          unit: 'g'    },
      { key: 'ash',            label: 'Rohasche',         unit: 'g'    },
      { key: 'cholesterol',    label: 'Cholesterin',      unit: 'mg'   },
      { key: 'salt',           label: 'Salz',             unit: 'g'    },
    ]
  },
  {
    label: 'Zucker & Kohlenhydrate',
    cols: [
      { key: 'sugar',          label: 'Zucker gesamt',    unit: 'g' },
      { key: 'glucose',        label: 'Glukose',          unit: 'g' },
      { key: 'fructose',       label: 'Fruktose',         unit: 'g' },
      { key: 'saccharose',     label: 'Saccharose',       unit: 'g' },
      { key: 'lactose',        label: 'Laktose',          unit: 'g' },
      { key: 'maltose',        label: 'Maltose',          unit: 'g' },
      { key: 'starch',         label: 'Stärke',           unit: 'g' },
      { key: 'oligosaccharide',label: 'Oligosaccharide',  unit: 'g' },
    ]
  },
  {
    label: 'Fettsäuren',
    cols: [
      { key: 'fatty_acids_saturated_total',       label: 'Gesättigte FS gesamt',      unit: 'g' },
      { key: 'fatty_acids_monounsaturated_total',  label: 'Einfach ungesättigte FS',   unit: 'g' },
      { key: 'fatty_acids_polyunsaturated_total',  label: 'Mehrfach ungesättigte FS',  unit: 'g' },
      { key: 'omega3_fatty_acids_total',           label: 'Omega-3 gesamt',            unit: 'g' },
      { key: 'omega6_fatty_acids_total',           label: 'Omega-6 gesamt',            unit: 'g' },
      { key: 'alpha_linolenic_acid',               label: 'Alpha-Linolensäure',        unit: 'g' },
      { key: 'eicosapentaenoic_acid',              label: 'EPA',                       unit: 'g' },
      { key: 'docosapentaenoic_acid',              label: 'DPA',                       unit: 'g' },
      { key: 'docosahexaenoic_acid',               label: 'DHA',                       unit: 'g' },
      { key: 'linoleic_acid',                      label: 'Linolsäure',                unit: 'g' },
      { key: 'arachidonic_acid',                   label: 'Arachidonsäure',            unit: 'g' },
      { key: 'oleic_acid',                         label: 'Ölsäure',                   unit: 'g' },
      { key: 'palmitic_acid',                      label: 'Palmitinsäure',             unit: 'g' },
      { key: 'stearic_acid',                       label: 'Stearinsäure',              unit: 'g' },
      { key: 'myristic_acid',                      label: 'Myristinsäure',             unit: 'g' },
      { key: 'erucic_acid',                        label: 'Erucasäure',                unit: 'g' },
    ]
  },
  {
    label: 'Vitamine',
    cols: [
      { key: 'vitamin_a_re',      label: 'Vitamin A (RE)',     unit: 'µg' },
      { key: 'retinol',           label: 'Retinol',            unit: 'µg' },
      { key: 'beta_carotene',     label: 'Beta-Carotin',       unit: 'µg' },
      { key: 'vitamin_d',         label: 'Vitamin D',          unit: 'µg' },
      { key: 'vitamin_e',         label: 'Vitamin E',          unit: 'mg' },
      { key: 'alpha_tocopherol',  label: 'Alpha-Tocopherol',   unit: 'mg' },
      { key: 'vitamin_k',         label: 'Vitamin K gesamt',   unit: 'µg' },
      { key: 'vitamin_k1',        label: 'Vitamin K1',         unit: 'µg' },
      { key: 'vitamin_k2',        label: 'Vitamin K2',         unit: 'µg' },
      { key: 'thiamin',           label: 'Thiamin (B1)',        unit: 'mg' },
      { key: 'riboflavin',        label: 'Riboflavin (B2)',     unit: 'mg' },
      { key: 'niacin',            label: 'Niacin (B3)',         unit: 'mg' },
      { key: 'niacin_equivalent', label: 'Niacin-Äquivalent',  unit: 'mg' },
      { key: 'pantothenic_acid',  label: 'Pantothensäure (B5)', unit: 'mg' },
      { key: 'vitamin_b6',        label: 'Vitamin B6',          unit: 'µg' },
      { key: 'biotin',            label: 'Biotin (B7)',          unit: 'µg' },
      { key: 'folate_equivalent', label: 'Folat-Äquivalent',    unit: 'µg' },
      { key: 'folate',            label: 'Folat',               unit: 'µg' },
      { key: 'folic_acid',        label: 'Folsäure',            unit: 'µg' },
      { key: 'vitamin_b12',       label: 'Vitamin B12',         unit: 'µg' },
      { key: 'vitamin_c',         label: 'Vitamin C',           unit: 'mg' },
    ]
  },
  {
    label: 'Mineralstoffe & Spurenelemente',
    cols: [
      { key: 'sodium',      label: 'Natrium',    unit: 'mg' },
      { key: 'chloride',    label: 'Chlorid',    unit: 'mg' },
      { key: 'potassium',   label: 'Kalium',     unit: 'mg' },
      { key: 'calcium',     label: 'Calcium',    unit: 'mg' },
      { key: 'magnesium',   label: 'Magnesium',  unit: 'mg' },
      { key: 'phosphorus',  label: 'Phosphor',   unit: 'mg' },
      { key: 'iron',        label: 'Eisen',      unit: 'mg' },
      { key: 'zinc',        label: 'Zink',       unit: 'mg' },
      { key: 'iodide',      label: 'Jod',        unit: 'µg' },
      { key: 'copper',      label: 'Kupfer',     unit: 'µg' },
      { key: 'manganese',   label: 'Mangan',     unit: 'µg' },
      { key: 'fluoride',    label: 'Fluorid',    unit: 'µg' },
      { key: 'chromium',    label: 'Chrom',      unit: 'µg' },
      { key: 'molybdenum',  label: 'Molybdän',   unit: 'µg' },
    ]
  },
  {
    label: 'Aminosäuren',
    cols: [
      { key: 'amino_acids_indispensable_total', label: 'Essentielle AS gesamt',  unit: 'g' },
      { key: 'leucine',        label: 'Leucin',          unit: 'g' },
      { key: 'isoleucine',     label: 'Isoleucin',       unit: 'g' },
      { key: 'valine',         label: 'Valin',           unit: 'g' },
      { key: 'lysine',         label: 'Lysin',           unit: 'g' },
      { key: 'methionine',     label: 'Methionin',       unit: 'g' },
      { key: 'phenylalanine',  label: 'Phenylalanin',    unit: 'g' },
      { key: 'threonine',      label: 'Threonin',        unit: 'g' },
      { key: 'trypthophan',    label: 'Tryptophan',      unit: 'g' },
      { key: 'histidine',      label: 'Histidin',        unit: 'g' },
      { key: 'alanine',        label: 'Alanin',          unit: 'g' },
      { key: 'arginine',       label: 'Arginin',         unit: 'g' },
      { key: 'glutamic_acid',  label: 'Glutaminsäure',   unit: 'g' },
      { key: 'glycine',        label: 'Glycin',          unit: 'g' },
      { key: 'proline',        label: 'Prolin',          unit: 'g' },
      { key: 'serine',         label: 'Serin',           unit: 'g' },
      { key: 'tyrosine',       label: 'Tyrosin',         unit: 'g' },
      { key: 'cystine',        label: 'Cystin',          unit: 'g' },
      { key: 'aspartic_acid',  label: 'Asparaginsäure',  unit: 'g' },
      { key: 'nitrogen_total', label: 'Stickstoff gesamt', unit: 'g' },
    ]
  },
]

// ── Fetch ──────────────────────────────────────────────────────────────
async function fetchData() {
  loading.value = true
  error.value   = null
  expandedIndex.value = null
  try {
    const params = searchQuery.value ? `?search=${encodeURIComponent(searchQuery.value)}` : ''
    const res    = await fetch(`/api/nutrition.php${params}`)
    if (!res.ok) throw new Error(`HTTP ${res.status}`)
    const data = await res.json()
    if (data.error) throw new Error(data.error)
    items.value = data
  } catch (e) {
    error.value = e.message || 'Unbekannter Fehler'
  } finally {
    loading.value = false
  }
}

fetchData()

function onSearch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(fetchData, 350)
}

function clearSearch() {
  searchQuery.value = ''
  fetchData()
}

function toggle(i) {
  expandedIndex.value = expandedIndex.value === i ? null : i
}

const sortedItems = computed(() =>
  [...items.value].sort((a, b) =>
    String(a.name_de ?? '').localeCompare(String(b.name_de ?? ''), 'de')
  )
)

function fmt(v) {
  if (v == null || v === '') return '—'
  const n = parseFloat(v)
  return isNaN(n) ? v : n % 1 === 0 ? n.toString() : n.toFixed(1)
}

function fmtVal(v) {
  if (v == null || v === '') return '—'
  const n = parseFloat(v)
  return isNaN(n) ? v : n % 1 === 0 ? n.toString() : n.toFixed(2)
}
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Mono:wght@400;500&family=DM+Sans:wght@300;400;500&display=swap');

:root {
  --bg:      #0e1117;
  --surface: #161b26;
  --surface2:#1a2236;
  --border:  #252d3d;
  --accent:  #5fffb0;
  --accent2: #38bdf8;
  --text:    #e2e8f0;
  --muted:   #64748b;
  --danger:  #f87171;
  --radius:  12px;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.app {
  min-height: 100vh;
  background: var(--bg);
  color: var(--text);
  font-family: 'DM Sans', sans-serif;
  font-size: 14px;
}

/* Header */
.app-header {
  background: linear-gradient(135deg, #0e1117 0%, #161b26 100%);
  border-bottom: 1px solid var(--border);
  padding: 2rem 1.5rem 1.5rem;
}
.header-inner { max-width: 900px; margin: 0 auto; }
.logo { display: flex; align-items: center; gap: .6rem; margin-bottom: .35rem; }
.logo-icon { font-size: 1.6rem; color: var(--accent); filter: drop-shadow(0 0 8px var(--accent)); }
.logo-text {
  font-family: 'DM Serif Display', serif;
  font-size: 1.85rem;
  letter-spacing: -.5px;
  background: linear-gradient(90deg, var(--accent), var(--accent2));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.tagline { color: var(--muted); font-size: .82rem; letter-spacing: .04em; text-transform: uppercase; font-weight: 300; }

/* Main */
.main { max-width: 900px; margin: 0 auto; padding: 2rem 1.5rem 4rem; }

/* Search */
.search-wrapper { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.75rem; flex-wrap: wrap; }
.search-box {
  display: flex; align-items: center; flex: 1; min-width: 240px;
  background: var(--surface); border: 1px solid var(--border);
  border-radius: var(--radius); padding: 0 .9rem; gap: .6rem;
  transition: border-color .2s, box-shadow .2s;
}
.search-box.focused { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(95,255,176,.12); }
.search-icon { color: var(--muted); display: flex; flex-shrink: 0; }
.search-input {
  flex: 1; background: transparent; border: none; outline: none;
  color: var(--text); font-family: 'DM Sans', sans-serif;
  font-size: .95rem; padding: .75rem 0;
}
.search-input::placeholder { color: var(--muted); }
.clear-btn {
  background: none; border: none; cursor: pointer; color: var(--muted);
  display: flex; align-items: center; padding: .2rem; border-radius: 4px; transition: color .15s;
}
.clear-btn:hover { color: var(--text); }
.result-count { font-size: .8rem; color: var(--muted); font-family: 'DM Mono', monospace; white-space: nowrap; }

/* States */
.state-box {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; padding: 5rem 2rem; gap: 1rem;
  color: var(--muted); text-align: center;
}
.state-icon { font-size: 2.5rem; opacity: .4; }
.error-state { color: var(--danger); }
.error-state .state-icon { opacity: .7; }
.retry-btn {
  margin-top: .5rem; padding: .5rem 1.2rem;
  border: 1px solid var(--danger); background: transparent;
  color: var(--danger); border-radius: 8px; cursor: pointer;
  font-family: 'DM Sans', sans-serif; font-size: .85rem; transition: background .15s;
}
.retry-btn:hover { background: rgba(248,113,113,.1); }

/* Spinner */
.spinner { display: flex; gap: 6px; }
.spinner span {
  width: 8px; height: 8px; border-radius: 50%;
  background: var(--accent); animation: bounce 1s infinite ease-in-out;
}
.spinner span:nth-child(2) { animation-delay: .15s; }
.spinner span:nth-child(3) { animation-delay: .3s; }
@keyframes bounce {
  0%, 80%, 100% { transform: scale(.6); opacity: .4; }
  40%           { transform: scale(1);  opacity: 1;  }
}

/* Cards */
.cards-grid { display: flex; flex-direction: column; gap: .75rem; }

.card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  overflow: hidden;
  transition: border-color .2s;
}
.card.expanded { border-color: rgba(95,255,176,.3); }

.card-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.25rem;
  cursor: pointer;
  user-select: none;
  transition: background .12s;
}
.card-header:hover { background: rgba(255,255,255,.02); }

.card-title-block { flex: 1; min-width: 0; }

.card-code {
  font-family: 'DM Mono', monospace;
  font-size: .7rem;
  color: var(--muted);
  letter-spacing: .05em;
  display: block;
  margin-bottom: .15rem;
}
.card-name {
  font-size: .95rem;
  font-weight: 500;
  color: #f1f5f9;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.card-name-en {
  font-size: .75rem;
  color: var(--muted);
  display: block;
  margin-top: .1rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.card-macros { display: flex; gap: 1rem; flex-shrink: 0; }

.macro { display: flex; flex-direction: column; align-items: center; min-width: 44px; }
.macro-val {
  font-family: 'DM Mono', monospace;
  font-size: .88rem;
  font-weight: 500;
  color: var(--accent);
}
.macro-label {
  font-size: .65rem;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: .04em;
  margin-top: .1rem;
}
.expand-icon { color: var(--muted); font-size: .65rem; flex-shrink: 0; }

/* Detail Panel */
.card-details {
  border-top: 1px solid var(--border);
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.group-title {
  font-size: .7rem;
  font-weight: 500;
  letter-spacing: .08em;
  text-transform: uppercase;
  color: var(--accent2);
  margin-bottom: .6rem;
  padding-bottom: .4rem;
  border-bottom: 1px solid var(--border);
}

.group-rows {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: .1rem 1rem;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  padding: .3rem 0;
  border-bottom: 1px solid rgba(37,45,61,.6);
  gap: .5rem;
}
.detail-label { font-size: .8rem; color: var(--muted); flex-shrink: 0; }
.detail-val { font-family: 'DM Mono', monospace; font-size: .8rem; color: var(--text); text-align: right; }
.detail-unit { color: var(--muted); font-size: .72rem; margin-left: .2rem; }

/* Responsive */
@media (max-width: 600px) {
  .app-header { padding: 1.25rem 1rem 1rem; }
  .main { padding: 1.25rem 1rem 3rem; }
  .logo-text { font-size: 1.5rem; }
  .card-macros { gap: .5rem; }
  .macro-label { display: none; }
  .group-rows { grid-template-columns: 1fr; }
}
</style>

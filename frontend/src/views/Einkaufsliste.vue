<template>
  <div class="page">

    <!-- LOADING -->
    <div v-if="loading" class="state">
      Lade Einkaufsliste...
    </div>

    <!-- ERROR -->
    <div v-else-if="error" class="state">
      Einkaufsliste konnte nicht geladen werden 😕
    </div>

    <!-- CONTENT -->
    <template v-else>
      <header class="page-header">
        <button class="back-btn" @click="router.push('/dashboard')">← Dashboard</button>
        <h1>{{ list.title }}</h1>
      </header>

      <!-- SHARE -->
      <div class="share-box">
        <input
          class="share-input"
          type="text"
          readonly
          :value="shareUrl"
          @focus="$event.target.select()"
        />
        <button class="share-btn" @click="copyLink">
          {{ copied ? 'Kopiert ✓' : 'Link kopieren' }}
        </button>
      </div>
      <p class="share-hint">Jeder mit diesem Link kann die Liste bearbeiten.</p>

      <!-- ADD ITEM -->
      <form class="add-form" @submit.prevent="addItem">
        <input
          v-model="newItem.amount"
          type="text"
          class="input input--amount"
          placeholder="Menge"
        />
        <input
          v-model="newItem.unit"
          type="text"
          class="input input--unit"
          placeholder="Einheit"
        />
        <input
          v-model="newItem.name"
          type="text"
          class="input input--name"
          placeholder="Was wird gebraucht?"
          required
        />
        <button type="submit" class="add-btn" :disabled="adding">+</button>
      </form>

      <!-- ITEMS -->
      <div v-if="items.length === 0" class="state state--small">
        Noch keine Einträge. Füge oben etwas hinzu 🛒
      </div>

      <ul v-else class="item-list">
        <li
          v-for="item in items"
          :key="item.id"
          class="item"
          :class="{ 'item--checked': item.checked }"
        >
          <label class="item-checkbox">
            <input
              type="checkbox"
              :checked="item.checked"
              @change="toggleItem(item)"
            />
            <span class="checkmark"></span>
          </label>

          <div class="item-text">
            <span v-if="item.amount" class="item-amount">
              {{ item.amount }}<span v-if="item.unit"> {{ item.unit }}</span>
            </span>
            <span class="item-name">{{ item.name }}</span>
          </div>

          <button class="delete-btn" @click="deleteItem(item)">✕</button>
        </li>
      </ul>
    </template>

  </div>
</template>

<script setup>
import {ref, reactive, computed, onMounted, watch} from 'vue'
import {useRouter, useRoute} from 'vue-router'

const router = useRouter()
const route = useRoute()

const loading = ref(true)
const error = ref(false)
const adding = ref(false)
const copied = ref(false)

const list = ref({title: 'Einkaufsliste'})
const items = ref([])

const newItem = reactive({
  amount: '',
  unit: '',
  name: ''
})

const shareUrl = computed(() => {
  return `${window.location.origin}/einkaufsliste/${route.params.uniqueId}`
})

onMounted(() => {
  loadOrCreate()
})

// reagiert, wenn sich die uniqueId in der URL ändert
// (z.B. nach router.replace() oder beim Navigieren zwischen zwei Listen,
// da Vue Router die Komponenten-Instanz hier wiederverwendet)
watch(
  () => route.params.uniqueId,
  () => {
    loadOrCreate()
  }
)

async function loadOrCreate() {
  // Keine uniqueId in der URL -> neue Liste anlegen und dorthin weiterleiten
  if (!route.params.uniqueId) {
    await createNewList()
  } else {
    await fetchList()
  }
}

async function createNewList() {
  loading.value = true
  error.value = false

  try {
    const res = await fetch('/api/createShoppingList.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({user_id: 1, title: 'Einkaufsliste'})
    })

    if (!res.ok) throw new Error()

    const data = await res.json()

    router.replace(`/einkaufsliste/${data.unique_id}`)
  } catch (e) {
    error.value = true
    loading.value = false
  }
}

async function fetchList() {
  loading.value = true
  error.value = false

  try {
    const res = await fetch(`/api/getShoppingList.php?uniqueId=${encodeURIComponent(route.params.uniqueId)}`)

    if (!res.ok) throw new Error()

    const data = await res.json()

    list.value = {title: data.title, created_at: data.created_at}
    items.value = data.items
  } catch (e) {
    error.value = true
  } finally {
    loading.value = false
  }
}

async function addItem() {
  if (!newItem.name.trim()) return

  adding.value = true

  try {
    const res = await fetch('/api/addShoppingItem.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({
        unique_id: route.params.uniqueId,
        name: newItem.name.trim(),
        amount: newItem.amount.trim(),
        unit: newItem.unit.trim()
      })
    })

    if (!res.ok) throw new Error()

    const data = await res.json()

    items.value.push(data.item)

    newItem.amount = ''
    newItem.unit = ''
    newItem.name = ''
  } catch (e) {
    // Optional: Fehlermeldung anzeigen
  } finally {
    adding.value = false
  }
}

async function toggleItem(item) {
  const previous = item.checked
  item.checked = !item.checked // optimistisches UI-Update

  try {
    const res = await fetch('/api/updateShoppingItem.php', {
      method: 'PATCH',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({
        id: item.id,
        unique_id: route.params.uniqueId,
        checked: item.checked
      })
    })

    if (!res.ok) throw new Error()
  } catch (e) {
    item.checked = previous // bei Fehler zurücksetzen
  }
}

async function deleteItem(item) {
  const previousItems = [...items.value]
  items.value = items.value.filter(i => i.id !== item.id) // optimistisches UI-Update

  try {
    const res = await fetch('/api/deleteShoppingItem.php', {
      method: 'DELETE',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({
        id: item.id,
        unique_id: route.params.uniqueId
      })
    })

    if (!res.ok) throw new Error()
  } catch (e) {
    items.value = previousItems // bei Fehler zurücksetzen
  }
}

async function copyLink() {
  try {
    await navigator.clipboard.writeText(shareUrl.value)
    copied.value = true
    setTimeout(() => {
      copied.value = false
    }, 2000)
  } catch (e) {
    // Clipboard nicht verfügbar -> Input ist trotzdem fokussierbar/selektierbar
  }
}
</script>

<style scoped>
.page {
  padding: 24px;
  max-width: 640px;
  margin: 0 auto;
  background: #f6f7fb;
  min-height: 100vh;
  box-sizing: border-box;
}

.page-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 20px;
}

.page-header h1 {
  margin: 0;
  font-size: 22px;
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

.state {
  text-align: center;
  padding: 60px 20px;
  color: #777;
}

.state--small {
  padding: 30px 20px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

/* SHARE */
.share-box {
  display: flex;
  gap: 8px;
  margin-bottom: 6px;
}

.share-input {
  flex: 1;
  padding: 10px 12px;
  border: 1.5px solid #e0e0e0;
  border-radius: 10px;
  font-size: 13px;
  background: white;
  color: #555;
}

.share-btn {
  padding: 10px 16px;
  background: #3d5bd9;
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  transition: background 0.2s;
}

.share-btn:hover {
  background: #2f4abf;
}

.share-hint {
  font-size: 12px;
  color: #999;
  margin: 0 0 24px;
}

/* ADD FORM */
.add-form {
  display: flex;
  gap: 8px;
  margin-bottom: 20px;
}

.input {
  padding: 12px 14px;
  border: 1.5px solid #e0e0e0;
  border-radius: 12px;
  font-size: 15px;
  background: white;
  box-sizing: border-box;
  color: #1a1a1a;
}

.input:focus {
  outline: none;
  border-color: #5b8dee;
}

.input--amount {
  width: 70px;
  flex-shrink: 0;
}

.input--unit {
  width: 80px;
  flex-shrink: 0;
}

.input--name {
  flex: 1;
}

.add-btn {
  width: 44px;
  flex-shrink: 0;
  background: #3d5bd9;
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 22px;
  line-height: 1;
  cursor: pointer;
  transition: background 0.2s, opacity 0.2s;
}

.add-btn:hover {
  background: #2f4abf;
}

.add-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* ITEM LIST */
.item-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.item {
  display: flex;
  align-items: center;
  gap: 12px;
  background: white;
  border-radius: 14px;
  padding: 12px 14px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  transition: opacity 0.2s;
}

.item--checked {
  opacity: 0.5;
}

.item-checkbox {
  position: relative;
  display: flex;
  align-items: center;
  cursor: pointer;
  flex-shrink: 0;
}

.item-checkbox input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  width: 22px;
  height: 22px;
  margin: 0;
}

.checkmark {
  width: 22px;
  height: 22px;
  border-radius: 7px;
  border: 1.5px solid #ccc;
  background: white;
  display: inline-block;
  position: relative;
  transition: background 0.2s, border-color 0.2s;
}

.item-checkbox input:checked + .checkmark {
  background: #3d5bd9;
  border-color: #3d5bd9;
}

.item-checkbox input:checked + .checkmark::after {
  content: '';
  position: absolute;
  left: 7px;
  top: 3px;
  width: 5px;
  height: 10px;
  border: solid white;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}

.item-text {
  flex: 1;
  display: flex;
  gap: 8px;
  align-items: baseline;
  min-width: 0;
}

.item--checked .item-name {
  text-decoration: line-through;
}

.item-amount {
  flex-shrink: 0;
  color: #5b8dee;
  font-weight: 600;
  font-size: 14px;
}

.item-name {
  font-size: 15px;
  color: #1a1a1a;
  overflow-wrap: anywhere;
}

.delete-btn {
  background: none;
  border: none;
  color: #ccc;
  cursor: pointer;
  font-size: 15px;
  flex-shrink: 0;
  padding: 4px;
  transition: color 0.2s;
}

.delete-btn:hover {
  color: #e74c3c;
}
</style>

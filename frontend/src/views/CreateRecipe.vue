<template>
  <div class="page">
    <header class="page-header">
      <button class="back-btn" @click="router.push('/')">← Dashboard</button>
      <h1>Rezept erstellen</h1>
    </header>

    <form class="form" @submit.prevent="submitRecipe">

      <!-- Titel -->
      <section class="section">
        <label class="label">Rezeptname *</label>
        <input
          v-model="form.title"
          type="text"
          class="input"
          placeholder="z.B. Spaghetti Bolognese"
          required
        />
      </section>

      <!-- Bild Upload (optional) -->
      <section class="section">
        <label class="label">Bild <span class="optional">(optional)</span></label>
        <div
          class="upload-area"
          :class="{ 'upload-area--has-image': imagePreview }"
          @click="$refs.fileInput.click()"
          @dragover.prevent
          @drop.prevent="handleDrop"
        >
          <img v-if="imagePreview" :src="imagePreview" class="image-preview" alt="Vorschau" />
          <template v-else>
            <div class="upload-icon">+</div>
            <p class="upload-text">Bild auswählen oder hierher ziehen</p>
          </template>
          <input
            ref="fileInput"
            type="file"
            accept="image/*"
            style="display: none"
            @change="handleFileChange"
          />
        </div>
        <button
          v-if="imagePreview"
          type="button"
          class="remove-btn"
          @click="removeImage"
        >Bild entfernen</button>
      </section>

      <!-- Zutaten -->
      <section class="section">
        <label class="label">Zutaten *</label>

        <div
          v-for="(ingredient, index) in form.ingredients"
          :key="index"
          class="ingredient-row"
        >
          <input
            v-model="ingredient.amount"
            type="number"
            min="0"
            step="0.1"
            class="input input--amount"
            placeholder="Menge"
          />
          <select v-model="ingredient.unit" class="input input--unit">
            <option value="">–</option>
            <option value="g">g</option>
            <option value="kg">kg</option>
            <option value="ml">ml</option>
            <option value="l">l</option>
            <option value="EL">EL</option>
            <option value="TL">TL</option>
            <option value="Tasse">Tasse</option>
            <option value="Prise">Prise</option>
            <option value="Stück">Stück</option>
            <option value="Scheibe">Scheibe</option>
            <option value="Zehe">Zehe</option>
            <option value="Bund">Bund</option>
            <option value="Dose">Dose</option>
            <option value="Packung">Packung</option>
            <option value="nach Belieben">nach Belieben</option>
          </select>
          <div class="autocomplete-wrapper">
            <input
              v-model="ingredient.name"
              type="text"
              class="input"
              placeholder="Zutat eingeben..."
              autocomplete="off"
              @input="fetchSuggestions(index)"
              @focus="activeSuggestion = index"
              @blur="delayClose"
            />
            <ul
              v-if="activeSuggestion === index && suggestions[index]?.length"
              class="suggestions"
            >
              <li
                v-for="s in suggestions[index]"
                :key="s.id"
                @mousedown.prevent="selectSuggestion(index, s)"
              >
                {{ s.name }}
              </li>
            </ul>
          </div>
          <button
            type="button"
            class="remove-btn remove-btn--inline"
            @click="removeIngredient(index)"
            :disabled="form.ingredients.length === 1"
          >✕</button>
        </div>

        <button type="button" class="add-btn" @click="addIngredient">
          + Zutat hinzufügen
        </button>
      </section>

      <!-- Anleitung -->
      <section class="section">
        <label class="label">Zubereitung *</label>
        <textarea
          v-model="form.instructions"
          class="input textarea"
          placeholder="Beschreibe Schritt für Schritt die Zubereitung..."
          rows="6"
          required
        ></textarea>
      </section>

      <!-- Zeiten (optional) -->
      <section class="section">
        <label class="label">Zeiten <span class="optional">(optional)</span></label>
        <div class="times-grid">
          <div class="time-field">
            <label class="sublabel">Vorbereitungszeit</label>
            <div class="time-row">
              <input
                v-model.number="form.prepTime.value"
                type="number"
                min="0"
                class="input input--time"
                placeholder="0"
              />
              <select v-model="form.prepTime.unit" class="input input--timeunit">
                <option value="min">min</option>
                <option value="h">h</option>
              </select>
            </div>
          </div>
          <div class="time-field">
            <label class="sublabel">Kochzeit / Backzeit</label>
            <div class="time-row">
              <input
                v-model.number="form.cookTime.value"
                type="number"
                min="0"
                class="input input--time"
                placeholder="0"
              />
              <select v-model="form.cookTime.unit" class="input input--timeunit">
                <option value="min">min</option>
                <option value="h">h</option>
              </select>
            </div>
          </div>
          <div class="time-field">
            <label class="sublabel">Ruhe-/Wartezeit</label>
            <div class="time-row">
              <input
                v-model.number="form.restTime.value"
                type="number"
                min="0"
                class="input input--time"
                placeholder="0"
              />
              <select v-model="form.restTime.unit" class="input input--timeunit">
                <option value="min">min</option>
                <option value="h">h</option>
              </select>
            </div>
          </div>
        </div>
      </section>

      <!-- Submit -->
      <div class="form-footer">
        <p v-if="successMsg" class="success-msg">{{ successMsg }}</p>
        <p v-if="errorMsg" class="error-msg">{{ errorMsg }}</p>
        <button type="submit" class="submit-btn" :disabled="loading">
          {{ loading ? 'Wird gespeichert...' : 'Rezept speichern' }}
        </button>
      </div>

    </form>
  </div>
</template>

<script setup>
import {ref, reactive} from 'vue'
import {useRouter} from 'vue-router'

const router = useRouter()

const imagePreview = ref(null)
const imageFile = ref(null)
const loading = ref(false)
const errorMsg = ref('')
const successMsg = ref('')
const activeSuggestion = ref(null)
const suggestions = ref({})

const form = reactive({
  title: '',
  instructions: '',
  ingredients: [{amount: '', unit: 'g', name: '', ingredient_id: null}],
  prepTime: {value: '', unit: 'min'},
  cookTime: {value: '', unit: 'min'},
  restTime: {value: '', unit: 'min'},
})

function resetForm() {
  form.title = ''
  form.instructions = ''
  form.ingredients = [{amount: '', unit: 'g', name: '', ingredient_id: null}]
  form.prepTime = {value: '', unit: 'min'}
  form.cookTime = {value: '', unit: 'min'}
  form.restTime = {value: '', unit: 'min'}
  imageFile.value = null
  imagePreview.value = null
  suggestions.value = {}
}

function addIngredient() {
  form.ingredients.push({amount: '', unit: 'g', name: '', ingredient_id: null})
}

function removeIngredient(index) {
  if (form.ingredients.length > 1) {
    form.ingredients.splice(index, 1)
    const updated = {...suggestions.value}
    delete updated[index]
    suggestions.value = updated
  }
}

function handleFileChange(e) {
  const file = e.target.files[0]
  if (file) setImage(file)
}

function handleDrop(e) {
  const file = e.dataTransfer.files[0]
  if (file && file.type.startsWith('image/')) setImage(file)
}

function setImage(file) {
  imageFile.value = file
  imagePreview.value = URL.createObjectURL(file)
}

function removeImage() {
  imagePreview.value = null
  imageFile.value = null
}

let suggestionTimer = null

async function fetchSuggestions(index) {
  const query = form.ingredients[index].name
  if (!query || query.length < 2) {
    suggestions.value[index] = []
    return
  }
  clearTimeout(suggestionTimer)
  suggestionTimer = setTimeout(async () => {
    try {
      const res = await fetch(`/api/ingredientsSearch.php?search=${encodeURIComponent(query)}`)
      if (!res.ok) throw new Error()
      const data = await res.json()
      suggestions.value = {...suggestions.value, [index]: data}
    } catch {
      suggestions.value = {...suggestions.value, [index]: []}
    }
  }, 250)
}

function selectSuggestion(index, suggestion) {
  form.ingredients[index].name = suggestion.name
  form.ingredients[index].ingredient_id = suggestion.id
  suggestions.value = {...suggestions.value, [index]: []}
  activeSuggestion.value = null
}

function delayClose() {
  setTimeout(() => {
    activeSuggestion.value = null
  }, 150)
}

async function submitRecipe() {
  errorMsg.value = ''
  successMsg.value = ''
  loading.value = true

  try {
    const data = new FormData()
    data.append('title', form.title)
    data.append('instructions', form.instructions)

    if (imageFile.value) data.append('image', imageFile.value)

    if (form.prepTime.value) {
      data.append('prep_time_value', form.prepTime.value)
      data.append('prep_time_unit', form.prepTime.unit)
    }
    if (form.cookTime.value) {
      data.append('cook_time_value', form.cookTime.value)
      data.append('cook_time_unit', form.cookTime.unit)
    }
    if (form.restTime.value) {
      data.append('rest_time_value', form.restTime.value)
      data.append('rest_time_unit', form.restTime.unit)
    }

    // form.ingredients.forEach((ing, i) => {
    //   const n = i + 1
    //   data.append(`ingredient_${n}_name`, ing.name)
    //   data.append(`ingredient_${n}_amount`, ing.amount)
    //   data.append(`ingredient_${n}_unit`, ing.unit)
    //   if (ing.ingredient_id) {
    //     data.append(`ingredient_${n}_id`, ing.ingredient_id)
    //   }
    // })
    // data.append
    data.append('ingredients', JSON.stringify(form.ingredients))
    data.append('user_id', '1')

    const res = await fetch('/api/saveRecipe.php', {
      method: 'POST',
      body: data
    })

    if (!res.ok) {
      const err = await res.json().catch(() => ({}))
      throw new Error(err.message ?? 'Serverfehler')
    }

    resetForm()
    successMsg.value = 'Rezept gespeichert!'
    setTimeout(() => {
      successMsg.value = ''
    }, 3000)

  } catch (e) {
    errorMsg.value = e.message || 'Fehler beim Speichern. Bitte nochmal versuchen.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.page {
  padding: 20px;
  min-height: 100vh;
  background: #f5f7fa;
  box-sizing: border-box;
  max-width: 720px;
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

.form {
  display: flex;
  flex-direction: column;
  gap: 28px;
}

.section {
  background: white;
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.label {
  display: block;
  font-size: 15px;
  font-weight: 600;
  margin-bottom: 10px;
  color: #1a1a1a;
}

.sublabel {
  display: block;
  font-size: 13px;
  color: #666;
  margin-bottom: 6px;
}

.optional {
  font-weight: 400;
  color: #999;
  font-size: 13px;
}

.input {
  width: 100%;
  padding: 10px 12px;
  border: 1.5px solid #e0e0e0;
  border-radius: 10px;
  font-size: 15px;
  background: #fafafa;
  box-sizing: border-box;
  transition: border-color 0.2s;
  color: #1a1a1a;
}

.input:focus {
  outline: none;
  border-color: #5b8dee;
  background: white;
}

.textarea {
  resize: vertical;
  min-height: 140px;
  line-height: 1.6;
}

.upload-area {
  border: 2px dashed #d0d0d0;
  border-radius: 12px;
  padding: 32px;
  text-align: center;
  cursor: pointer;
  transition: border-color 0.2s, background 0.2s;
  background: #fafafa;
}

.upload-area:hover {
  border-color: #5b8dee;
  background: #f0f4ff;
}

.upload-area--has-image {
  padding: 0;
  border-style: solid;
  border-color: #d0d0d0;
  overflow: hidden;
}

.upload-icon {
  font-size: 32px;
  color: #bbb;
  margin-bottom: 8px;
}

.upload-text {
  margin: 0;
  font-size: 14px;
  color: #888;
}

.image-preview {
  width: 100%;
  max-height: 240px;
  object-fit: cover;
  display: block;
}

.ingredient-row {
  display: flex;
  gap: 8px;
  align-items: flex-start;
  margin-bottom: 10px;
}

.input--amount {
  width: 80px;
  flex-shrink: 0;
}

.input--unit {
  width: 110px;
  flex-shrink: 0;
}

.autocomplete-wrapper {
  position: relative;
  flex: 1;
}

.autocomplete-wrapper .input {
  width: 100%;
}

.suggestions {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1.5px solid #e0e0e0;
  border-top: none;
  border-radius: 0 0 10px 10px;
  list-style: none;
  margin: 0;
  padding: 0;
  z-index: 50;
  max-height: 200px;
  overflow-y: auto;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.10);
}

.suggestions li {
  padding: 10px 14px;
  cursor: pointer;
  font-size: 14px;
  color: #1a1a1a;
}

.suggestions li:hover {
  background: #f0f4ff;
}

.times-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 16px;
}

.time-row {
  display: flex;
  gap: 8px;
}

.input--time {
  flex: 1;
}

.input--timeunit {
  width: 70px;
  flex-shrink: 0;
}

.add-btn {
  margin-top: 4px;
  padding: 9px 16px;
  background: #eef2ff;
  color: #3d5bd9;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: background 0.2s;
}

.add-btn:hover {
  background: #dde6ff;
}

.remove-btn {
  margin-top: 8px;
  padding: 6px 12px;
  background: none;
  border: none;
  color: #e74c3c;
  cursor: pointer;
  font-size: 13px;
}

.remove-btn--inline {
  margin-top: 0;
  padding: 10px 8px;
  font-size: 16px;
  flex-shrink: 0;
}

.remove-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.form-footer {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 10px;
  padding-bottom: 40px;
}

.submit-btn {
  padding: 13px 32px;
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
  opacity: 0.6;
  cursor: not-allowed;
}

.error-msg {
  color: #e74c3c;
  font-size: 14px;
  margin: 0;
}

.success-msg {
  color: #27ae60;
  font-size: 14px;
  margin: 0;
}
</style>

```vue
<template>
  <div class="calendar-page">
    <div class="calendar-card">

      <div class="card-header">
        <button class="back-btn" @click="router.push('/dashboard')">
          ← Zurück
        </button>
      </div>

      <div class="calendar-header">
        <button class="nav-btn" @click="previousMonth">
          ←
        </button>

        <div class="month-selector">
          <select v-model="currentMonth" @change="generateCalendar">
            <option
              v-for="(month, index) in months"
              :key="index"
              :value="index"
            >
              {{ month }}
            </option>
          </select>

          <select v-model="currentYear" @change="generateCalendar">
            <option
              v-for="year in availableYears"
              :key="year"
              :value="year"
            >
              {{ year }}
            </option>
          </select>
        </div>

        <button class="nav-btn" @click="nextMonth">
          →
        </button>
      </div>

      <div class="calendar-weekdays">
        <div v-for="day in weekdays" :key="day">
          {{ day }}
        </div>
      </div>

      <div class="calendar-grid">
        <div
          v-for="(day, index) in calendarDays"
          :key="index"
          class="calendar-day"
          :class="{
            empty: !day,
            today: isToday(day),
            selected: isSelected(day)
          }"
          @click="selectDate(day)"
        >
          {{ day }}
        </div>
      </div>

      <div class="selected-date" v-if="selectedDate">
        📅 Ausgewähltes Datum:
        <strong>{{ formattedSelectedDate }}</strong>
      </div>

      <div class="actions">
        <button class="today-btn" @click="goToToday">
          Heute
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const today = new Date()

const currentMonth = ref(today.getMonth())
const currentYear = ref(today.getFullYear())

const selectedDate = ref(null)

const calendarDays = ref([])

const months = [
  'Januar',
  'Februar',
  'März',
  'April',
  'Mai',
  'Juni',
  'Juli',
  'August',
  'September',
  'Oktober',
  'November',
  'Dezember'
]

const weekdays = ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So']

const availableYears = computed(() => {
  const years = []

  const current = new Date().getFullYear()

  for (let year = current - 30; year <= current + 30; year++) {
    years.push(year)
  }

  return years
})

onMounted(() => {
  generateCalendar()
})

function generateCalendar() {
  const year = currentYear.value
  const month = currentMonth.value

  const firstDay = new Date(year, month, 1)

  let startDay = firstDay.getDay()

  // Sonntag = 0 → auf Ende verschieben
  startDay = startDay === 0 ? 6 : startDay - 1

  const daysInMonth = new Date(year, month + 1, 0).getDate()

  const days = []

  for (let i = 0; i < startDay; i++) {
    days.push(null)
  }

  for (let day = 1; day <= daysInMonth; day++) {
    days.push(day)
  }

  calendarDays.value = days
}

function previousMonth() {
  if (currentMonth.value === 0) {
    currentMonth.value = 11
    currentYear.value--
  } else {
    currentMonth.value--
  }

  generateCalendar()
}

function nextMonth() {
  if (currentMonth.value === 11) {
    currentMonth.value = 0
    currentYear.value++
  } else {
    currentMonth.value++
  }

  generateCalendar()
}

function selectDate(day) {
  if (!day) return

  selectedDate.value = new Date(
    currentYear.value,
    currentMonth.value,
    day
  )
}

function isSelected(day) {
  if (!day || !selectedDate.value) return false

  return (
    selectedDate.value.getDate() === day &&
    selectedDate.value.getMonth() === currentMonth.value &&
    selectedDate.value.getFullYear() === currentYear.value
  )
}

function isToday(day) {
  if (!day) return false

  return (
    day === today.getDate() &&
    currentMonth.value === today.getMonth() &&
    currentYear.value === today.getFullYear()
  )
}

function goToToday() {
  currentMonth.value = today.getMonth()
  currentYear.value = today.getFullYear()

  selectedDate.value = new Date()

  generateCalendar()
}

const formattedSelectedDate = computed(() => {
  if (!selectedDate.value) return ''

  return selectedDate.value.toLocaleDateString('de-DE')
})
</script>

<style scoped>
.calendar-page {
  min-height: 100vh;
  background: #f4f6f9;
  padding: 30px;
  display: flex;
  justify-content: center;
  align-items: flex-start;
}

.calendar-card {
  width: 100%;
  max-width: 900px;
  background: white;
  border-radius: 20px;
  padding: 25px;
  box-shadow: 0 12px 35px rgba(0,0,0,0.08);
}

.card-header {
  margin-bottom: 16px;
}

.back-btn {
  border: 1px solid #ddd;
  background: #f5f5f5;
  border-radius: 10px;
  padding: 8px 14px;
  cursor: pointer;
  font-size: 14px;
  white-space: nowrap;
  transition: background 0.2s;
}

.back-btn:hover {
  background: #e8e8e8;
}

.calendar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 25px;
}

.month-selector {
  display: flex;
  gap: 12px;
}

.month-selector select {
  padding: 10px 14px;
  border-radius: 10px;
  border: 1px solid #ddd;
  font-size: 15px;
  background: white;
}

.nav-btn {
  width: 44px;
  height: 44px;
  border: none;
  border-radius: 12px;
  background: #4f7cff;
  color: white;
  font-size: 18px;
  cursor: pointer;
}

.nav-btn:hover {
  opacity: 0.9;
}

.calendar-weekdays {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  margin-bottom: 10px;
}

.calendar-weekdays div {
  text-align: center;
  font-weight: 600;
  color: #666;
  padding: 10px;
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 8px;
}

.calendar-day {
  aspect-ratio: 1;
  background: #fafafa;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all .15s ease;
  font-size: 15px;
}

.calendar-day:hover {
  background: #eaf1ff;
}

.calendar-day.empty {
  background: transparent;
  cursor: default;
}

.calendar-day.today {
  border: 2px solid #4f7cff;
  font-weight: bold;
}

.calendar-day.selected {
  background: #4f7cff;
  color: white;
  font-weight: bold;
}

.selected-date {
  margin-top: 25px;
  padding: 14px;
  border-radius: 12px;
  background: #f5f8ff;
  color: #2c3e50;
}

.actions {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}

.today-btn {
  padding: 10px 18px;
  border: none;
  border-radius: 10px;
  background: #4f7cff;
  color: white;
  cursor: pointer;
}

.today-btn:hover {
  opacity: 0.9;
}

@media (max-width: 700px) {
  .calendar-page {
    padding: 12px;
  }

  .calendar-card {
    padding: 16px;
  }

  .month-selector {
    flex-direction: column;
  }

  .calendar-day {
    font-size: 13px;
  }
}
</style>
```

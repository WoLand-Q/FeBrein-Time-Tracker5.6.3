<template>
  <div class="user-dashboard">
    <h1 class="page-title">Таймер користувача</h1>

    <!-- Фильтры дат -->
    <div class="filters">
      <label>
        Початкова дата:
        <input type="date" v-model="startDate" class="date-input" />
      </label>
      <label>
        Кінцева дата:
        <input type="date" v-model="endDate" class="date-input" />
      </label>
      <button @click="fetchDashboardData" class="apply-btn">
        Застосувати
      </button>
    </div>

    <!-- Карточки статистики (всего/актив/перерыв/офлайн) -->
    <div class="stats-cards">
      <div class="card">
        <h3>Всього користувачів</h3>
        <p>{{ stats.totalUsers }}</p>
      </div>
      <div class="card">
        <h3>Зараз працюють</h3>
        <p>{{ stats.activeUsers }}</p>
      </div>
      <div class="card">
        <h3>На перерві</h3>
        <p>{{ stats.onBreak }}</p>
      </div>
      <div class="card">
        <h3>Офлайн</h3>
        <p>{{ stats.offlineUsers }}</p>
      </div>
    </div>

    <!-- Суммарный блок (итоговые цифры по работе и перерывам) -->
    <div class="summary-block">
      <h2>Сумарно ({{ startDate || '...' }} - {{ endDate || '...' }})</h2>
      <p>Відпрацьовано: {{ convertToHoursAndMinutes(stats.total_work_minutes) }}</p>
      <p>Перерви: {{ convertToHoursAndMinutes(stats.total_break_minutes) }}</p>
    </div>

    <!-- Таблица детализации -->
    <div class="logs-block">
      <h2>Деталізація (події за період)</h2>
      <table>
        <thead>
        <tr>
          <th>Користувач</th>
          <th>Дата</th>
          <th>Початок роботи</th>
          <th>Початок перерви</th>
          <th>Кінець перерви</th>
          <th>Кінець роботи</th>
          <th>Поточний статус</th>
          <th>Загальний час (робота)</th>
          <th>Загальний час (перерви)</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="row in stats.details" :key="row.user_id">
          <td>{{ row.user_name }}</td>
          <td>{{ row.dateUsed || '-' }}</td>
          <td>{{ formatTime(row.startWork) }}</td>
          <td>{{ formatTime(row.startBreak) }}</td>
          <td>{{ formatTime(row.endBreak) }}</td>
          <td>{{ formatTime(row.endWork) }}</td>
          <td>
            <!-- Подсветка статуса (зелёный/жёлтый/красный) -->
            <span :class="statusClass(row.status)">
                <template v-if="row.status === 'active'">Працює</template>
                <template v-else-if="row.status === 'on_break'">На перерві</template>
                <template v-else>Офлайн</template>
              </span>
          </td>
          <td>{{ convertToHoursAndMinutes(row.work_minutes) }}</td>
          <td>{{ convertToHoursAndMinutes(row.break_minutes) }}</td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@/api'

// Перевод минут в "X год Y хв"
function convertToHoursAndMinutes(totalMinutes) {
  const h = Math.floor(totalMinutes / 60)
  const m = totalMinutes % 60
  return `${h} год ${m} хв`
}

// Из "YYYY-MM-DD HH:mm:ss" берём только "HH:mm" (без секунд)
function formatTime(dtString) {
  if (!dtString) return '-'
  const [datePart, timePart] = dtString.split(' ')
  if (!timePart) return '-'
  const [hh, mm] = timePart.split(':')
  return `${hh}:${mm}`
}

// CSS-класс для подсветки статуса
function statusClass(status) {
  switch (status) {
    case 'active':   return 'status-active'
    case 'on_break': return 'status-break'
    default:         return 'status-offline'
  }
}

// По умолчанию берём сегодняшнюю дату
const todayStr = new Date().toISOString().slice(0, 10)
const startDate = ref(todayStr)
const endDate   = ref(todayStr)

// Реактивный объект
const stats = reactive({
  totalUsers: 0,
  activeUsers: 0,
  onBreak: 0,
  offlineUsers: 0,
  total_work_minutes: 0,
  total_break_minutes: 0,
  details: []
})

async function fetchDashboardData() {
  try {
    const params = {}
    if (startDate.value) params.start_date = startDate.value
    if (endDate.value)   params.end_date   = endDate.value

    // 1) Логи
    const respLogs = await api.get('/api/userTimeLogs', { params })
    const allLogs  = respLogs.data.data || []

    // 2) Пользователи
    const respUsers = await api.get('/api/users')
    const allUsers  = respUsers.data.data || respUsers.data || []

    // Группируем логи
    const logsByUser = {}
    allLogs.forEach(log => {
      const uid = log.user_id
      if (!logsByUser[uid]) logsByUser[uid] = []
      logsByUser[uid].push(log)
    })

    let totalWorkSeconds  = 0
    let totalBreakSeconds = 0
    let activeCount  = 0
    let onBreakCount = 0
    let offlineCount = 0

    const detailMap = {}

    function parseUserLogs(userLogs) {
      // Фильтруем логи, чтобы брать только те, что в выбранном диапазоне дат
      userLogs = userLogs.filter(l => {
        const datePart = l.acted_at.split(' ')[0] // "YYYY-MM-DD"
        return datePart >= startDate.value && datePart <= endDate.value
      })
      if (userLogs.length === 0) {
        // Если после фильтра вообще ничего нет
        return {
          workSec: 0,
          breakSec: 0,
          status: 'offline',
          dateUsed: null,
          startWork: null,
          startBreak: null,
          endBreak: null,
          endWork: null
        }
      }

      userLogs.sort((a,b) => new Date(a.acted_at) - new Date(b.acted_at))

      let workSec = 0
      let breakSec = 0
      let state = 'none'
      let lastTime = null

      let startWorkTime  = null
      let startBreakTime = null
      let endBreakTime   = null
      let endWorkTime    = null
      let earliestDate   = null

      userLogs.forEach((l, idx) => {
        const eid = l.event_id
        const t   = new Date(l.acted_at).getTime() / 1000

        // Первую дату (earliestDate) берём из первой записи
        if (idx === 0) {
          earliestDate = l.acted_at.split(' ')[0]
        }

        if (eid === 1) {
          // startWork
          if (!startWorkTime) startWorkTime = l.acted_at
          if (state==='work' && lastTime!=null) {
            workSec += (t - lastTime)
          }
          if (state==='break' && lastTime!=null) {
            breakSec += (t - lastTime)
          }
          state = 'work'
          lastTime = t
        } else if (eid === 2) {
          // startBreak
          if (!startBreakTime) startBreakTime = l.acted_at
          if (state==='work' && lastTime!=null) {
            workSec += (t - lastTime)
          }
          if (state==='break' && lastTime!=null) {
            breakSec += (t - lastTime)
          }
          state = 'break'
          lastTime = t
        } else if (eid === 3) {
          // stopBreak => back to work
          if (!endBreakTime) endBreakTime = l.acted_at
          if (state==='break' && lastTime!=null) {
            breakSec += (t - lastTime)
          }
          state = 'work'
          lastTime = t
        } else if (eid === 4) {
          // stopWork
          if (!endWorkTime) endWorkTime = l.acted_at
          if (state==='work' && lastTime!=null) {
            workSec += (t - lastTime)
          }
          if (state==='break' && lastTime!=null) {
            breakSec += (t - lastTime)
          }
          state = 'none'
          lastTime = t
        }
      })

      // Если состояние не завершено
      const nowSec = Date.now() / 1000
      if (state==='work' && lastTime!=null) {
        workSec += (nowSec - lastTime)
      } else if (state==='break' && lastTime!=null) {
        breakSec += (nowSec - lastTime)
      }

      let finalStatus = 'offline'
      if (state==='work') finalStatus = 'active'
      else if (state==='break') finalStatus = 'on_break'

      return {
        workSec,
        breakSec,
        status: finalStatus,
        dateUsed: earliestDate,
        startWork: startWorkTime,
        startBreak: startBreakTime,
        endBreak: endBreakTime,
        endWork: endWorkTime
      }
    }

    // Для каждого пользователя
    allUsers.forEach(u => {
      const uid = u.id
      const userLogs = logsByUser[uid] || []
      const parsed = parseUserLogs(userLogs)

      totalWorkSeconds  += parsed.workSec
      totalBreakSeconds += parsed.breakSec

      if (parsed.status === 'active') {
        activeCount++
      } else if (parsed.status === 'on_break') {
        onBreakCount++
      } else {
        offlineCount++
      }

      detailMap[uid] = {
        user_id:      uid,
        user_name:    u.name || `Користувач #${uid}`,
        work_minutes: Math.floor(parsed.workSec / 60),
        break_minutes:Math.floor(parsed.breakSec / 60),
        status:       parsed.status,
        dateUsed:     parsed.dateUsed || null,
        startWork:    parsed.startWork || null,
        startBreak:   parsed.startBreak|| null,
        endBreak:     parsed.endBreak  || null,
        endWork:      parsed.endWork   || null
      }
    })

    // Заполняем stats
    stats.totalUsers          = allUsers.length
    stats.activeUsers         = activeCount
    stats.onBreak             = onBreakCount
    stats.offlineUsers        = offlineCount
    stats.total_work_minutes  = Math.floor(totalWorkSeconds / 60)
    stats.total_break_minutes = Math.floor(totalBreakSeconds / 60)
    stats.details             = Object.values(detailMap)
  } catch (e) {
    console.error('Ошибка fetchDashboardData:', e)
  }
}

// Вызываем fetchDashboardData() при первом показе
onMounted(() => {
  fetchDashboardData()
})
</script>

<style scoped>
.user-dashboard {
  padding: 1rem;
  background: #121212;
  color: #fff;
  min-height: 100vh;
}

/* Заголовок */
.page-title {
  font-size: 1.8rem;
  margin-bottom: 1rem;
  text-align: center;
  color: #ff4545;
}

/* Фильтры */
.filters {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}
.filters label {
  display: flex;
  align-items: center;
}
.date-input {
  margin-left: 0.5rem;
  padding: 0.25rem;
  background: #1f1f1f;
  border: 1px solid #444;
  color: #fff;
  border-radius: 4px;
}
.apply-btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  background: #ff4545;
  color: #fff;
  cursor: pointer;
}
.apply-btn:hover {
  background: #ff6565;
}

/* Карточки статистики */
.stats-cards {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 1rem;
}
.card {
  flex: 1 1 160px;
  min-width: 140px;
  background: #1f1f1f;
  border-radius: 8px;
  padding: 1rem;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  transition: transform 0.2s, box-shadow 0.3s;
}
.card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
}
.card h3 {
  font-size: 1rem;
  margin-bottom: 0.5rem;
  color: #ff4545;
}
.card p {
  font-size: 1.6rem;
  font-weight: bold;
}

/* Суммарно */
.summary-block {
  background: #1f1f1f;
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 1rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}
.summary-block h2 {
  margin: 0 0 0.5rem;
  color: #ff4545;
}

/* Таблица детализации */
.logs-block {
  background: #1f1f1f;
  border-radius: 8px;
  padding: 1rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}
.logs-block h2 {
  margin: 0 0 1rem;
  color: #ff4545;
}
.logs-block table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 0.5rem;
}
.logs-block th,
.logs-block td {
  border: 1px solid #444;
  padding: 0.75rem;
  text-align: center;
}
.logs-block th {
  background: #2d2d2d;
  color: #ff4545;
}

/* Подсветка статуса */
.status-active {
  color: #4caf50; /* зелёный */
  font-weight: 600;
}
.status-break {
  color: #ffc107; /* жёлтый */
  font-weight: 600;
}
.status-offline {
  color: #ff4545; /* красный */
  font-weight: 600;
}
</style>

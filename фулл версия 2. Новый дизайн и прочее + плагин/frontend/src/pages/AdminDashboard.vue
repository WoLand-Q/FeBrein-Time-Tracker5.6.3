<template>
  <div class="admin-dashboard">
    <!-- Заголовок -->
    <h1 class="dashboard-title">Admin Dashboard</h1>

    <!-- Блок фильтров -->
    <div class="filters">

      <!-- Блок дат (оба поля в одной строке) -->
      <div class="filter-dates">
        <label>
          Початкова дата:
          <input type="date" v-model="startDate" />
        </label>
        <label>
          Кінцева дата:
          <input type="date" v-model="endDate" />
        </label>
      </div>

      <!-- Блок выбора отдела/группы -->
      <div class="filter-row">
        <label>
          Відділ:
          <select v-model="selectedDepartment">
            <option value="">Усі</option>
            <option
                v-for="group in groups"
                :key="group.id"
                :value="group.id"
            >
              {{ group.group_name }}
            </option>
          </select>
        </label>
      </div>

      <!-- Блок выбора пользователя -->
      <div class="filter-row">
        <label>
          Користувач:
          <select v-model="selectedUser">
            <option value="">Усі</option>
            <option
                v-for="user in allUsers"
                :key="user.id"
                :value="user.id"
            >
              {{ user.name }}
            </option>
          </select>
        </label>
      </div>

      <!-- Кнопка "Применить" -->
      <button @click="fetchDashboardData" class="apply-btn">
        Застосувати
      </button>
    </div>

    <!-- Карточки статистики -->
    <div class="stats-cards">
      <div class="card">
        <h3>Всього користувачів</h3>
        <p>{{ stats.totalUsers }}</p>
      </div>
      <div class="card">
        <h3>Працюють</h3>
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

    <!-- Ещё две карточки, если хотите показывать суммарное время в формате ЧЧ:ММ -->
    <div class="stats-cards">
      <div class="card">
        <h3>Робота (загалом)</h3>
        <p>{{ formatHm(stats.total_work_minutes) }}</p>
      </div>
      <div class="card">
        <h3>Перерви (загалом)</h3>
        <p>{{ formatHm(stats.total_break_minutes) }}</p>
      </div>
    </div>

    <!-- Блок диаграмм -->
    <div class="charts-section">
      <!-- 1) Статус пользователей -->
      <div class="chart-block">
        <h2>Статус користувачів</h2>
        <ChartStatus
            :active="stats.activeUsers"
            :onBreak="stats.onBreak"
            :offline="stats.offlineUsers"
        />
      </div>

      <!-- 2) Работа / Перерывы (пирог) -->
      <div class="chart-block">
        <h2>Робота / Перерви (загалом)</h2>
        <PieWorkBreak
            :work="stats.total_work_minutes"
            :break="stats.total_break_minutes"
        />
      </div>

      <!-- 3) Тренд рабочего времени (линейный график) -->
      <div class="chart-block">
        <h2>Тренд робочого часу (показуємо щодня)</h2>
        <LineWorkTrend :trend-data="dailyTrend" />
      </div>
    </div>

    <!-- Таблица детализации -->
    <div class="details-table">
      <h2>Детальна інформація</h2>
      <table>
        <thead>
        <tr>
          <th>Користувач</th>
          <th>Відділ</th>
          <th>Початок роботи</th>
          <th>Початок перерви</th>
          <th>Кінець перерви</th>
          <th>Кінець роботи</th>
          <th>Робочий час</th>
          <th>Перерви</th>
          <th>Статус</th>
        </tr>
        </thead>
        <tbody>
        <tr
            v-for="(row, index) in stats.details"
            :key="index"
        >
          <td>{{ row.user_name }}</td>
          <td>{{ row.department_name || '—' }}</td>
          <td>{{ formatHmTime(row.startWork) }}</td>
          <td>{{ formatHmTime(row.startBreak) }}</td>
          <td>{{ formatHmTime(row.endBreak) }}</td>
          <td>{{ formatHmTime(row.endWork) }}</td>
          <td>{{ formatHm(row.work_minutes) }}</td>
          <td>{{ formatHm(row.break_minutes) }}</td>
          <td>
              <span :class="statusClass(row.status)">
                <template v-if="row.status === 'active'">Працює</template>
                <template v-else-if="row.status === 'on_break'">На перерві</template>
                <template v-else>Офлайн</template>
              </span>
          </td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@/api'

// Диаграммы:
import ChartStatus from '@/components/ChartStatus.vue'
import PieWorkBreak from '@/components/PieWorkBreak.vue'
import LineWorkTrend from '@/components/LineWorkTrend.vue'

// Фильтры
const now = new Date()
const todayStr = now.toISOString().slice(0, 10)

const startDate = ref(todayStr)
const endDate = ref(todayStr)
const selectedDepartment = ref('')
const selectedUser = ref('')

// Списки
const groups = ref([])
const allUsers = ref([])

// Статистика
const stats = reactive({
  totalUsers: 0,
  activeUsers: 0,
  onBreak: 0,
  offlineUsers: 0,
  total_work_minutes: 0,
  total_break_minutes: 0,
  details: []
})

// Тренд
const dailyTrend = ref([])

onMounted(async () => {
  await fetchAllUsers()
  await fetchGroups()
  await fetchDashboardData()
})

// Загружаем группы
async function fetchGroups() {
  try {
    const resp = await api.get('/api/groups')
    groups.value = resp.data.data || []
  } catch (err) {
    console.error('Ошибка загрузки groups:', err)
  }
}

// Загружаем пользователей
async function fetchAllUsers() {
  try {
    const respUsers = await api.get('/api/users')
    allUsers.value = respUsers.data.data || []
  } catch (err) {
    console.error('Ошибка загрузки users:', err)
  }
}

// Формат "ЧЧ:ММ" для общего количества минут (например, 141 мин => "2:21")
function formatHm(totalMinutes) {
  const h = Math.floor(totalMinutes / 60)
  const m = totalMinutes % 60
  // Допишем ведущий ноль для минут, чтобы было более аккуратно: "02:21"
  const mm = m < 10 ? `0${m}` : m
  return `${h}:${mm}`
}

// Форматирование времени события (DateTime: "2025-03-02 13:11:05" => "13:11")
function formatHmTime(dateTimeStr) {
  if (!dateTimeStr) return '-'
  const [datePart, timePart] = dateTimeStr.split(' ')
  if (!timePart) return '-'
  // timePart = "HH:MM:SS"
  const [hh, mm] = timePart.split(':')
  return `${hh}:${mm}`
}

async function fetchDashboardData() {
  try {
    // Параметры запроса
    const params = {}
    if (startDate.value)            params.start_date = startDate.value
    if (endDate.value)              params.end_date   = endDate.value
    if (selectedDepartment.value)    params.department_id = selectedDepartment.value
    if (selectedUser.value)         params.user_id = selectedUser.value

    // Логи
    const respLogs = await api.get('/api/userTimeLogs', { params })
    const allLogs = respLogs.data.data || []

    // Делам словари для групп
    const deptMap = {}
    groups.value.forEach(g => {
      deptMap[g.id] = g.group_name
    })

    // Сгруппируем логи [userId -> [...логи]]
    const logsByUser = {}
    allLogs.forEach(l => {
      const uid = l.user_id
      if (!logsByUser[uid]) {
        logsByUser[uid] = []
      }
      logsByUser[uid].push(l)
    })

    let totalWorkSeconds  = 0
    let totalBreakSeconds = 0
    let activeCount       = 0
    let onBreakCount      = 0
    let offlineCount      = 0

    const detailRows = []

    allUsers.value.forEach(u => {
      // Ручной фильтр
      if (selectedDepartment.value && u.group_id !== +selectedDepartment.value) {
        return
      }
      if (selectedUser.value && u.id !== +selectedUser.value) {
        return
      }

      const userLogs = logsByUser[u.id] || []
      userLogs.sort((a, b) => new Date(a.acted_at) - new Date(b.acted_at))

      let workSec  = 0
      let breakSec = 0
      let state = 'none'
      let lastTime = null

      // Для наглядности запомним время первого startWork, первого startBreak и т.д.
      let startWorkTime  = null
      let startBreakTime = null
      let endBreakTime   = null
      let endWorkTime    = null

      userLogs.forEach(log => {
        const eid = log.event_id
        const t = new Date(log.acted_at).getTime() / 1000

        if (eid === 1) {
          // start work
          if (!startWorkTime) startWorkTime = log.acted_at
          if (state === 'work' && lastTime)  workSec  += (t - lastTime)
          if (state === 'break' && lastTime) breakSec += (t - lastTime)
          state = 'work'
          lastTime = t
        }
        else if (eid === 2) {
          // start break
          if (!startBreakTime) startBreakTime = log.acted_at
          if (state === 'work' && lastTime)  workSec  += (t - lastTime)
          if (state === 'break' && lastTime) breakSec += (t - lastTime)
          state = 'break'
          lastTime = t
        }
        else if (eid === 3) {
          // end break => back to work
          if (!endBreakTime) endBreakTime = log.acted_at
          if (state === 'break' && lastTime) breakSec += (t - lastTime)
          state = 'work'
          lastTime = t
        }
        else if (eid === 4) {
          // stop
          if (!endWorkTime) endWorkTime = log.acted_at
          if (state === 'work' && lastTime)  workSec  += (t - lastTime)
          if (state === 'break' && lastTime) breakSec += (t - lastTime)
          state = 'none'
          lastTime = t
        }
      })

      // Если день незавершён
      const nowSec = Date.now() / 1000
      if (state === 'work' && lastTime) {
        workSec += nowSec - lastTime
      } else if (state === 'break' && lastTime) {
        breakSec += nowSec - lastTime
      }

      let finalStatus = 'offline'
      if (state==='work')      finalStatus = 'active'
      else if (state==='break') finalStatus = 'on_break'

      totalWorkSeconds  += workSec
      totalBreakSeconds += breakSec

      if (finalStatus==='active')      activeCount++
      else if (finalStatus==='on_break') onBreakCount++
      else offlineCount++

      detailRows.push({
        user_name: u.name,
        department_name: deptMap[u.group_id] || '',
        startWork:  startWorkTime,
        startBreak: startBreakTime,
        endBreak:   endBreakTime,
        endWork:    endWorkTime,
        work_minutes:  Math.floor(workSec / 60),
        break_minutes: Math.floor(breakSec / 60),
        status: finalStatus
      })
    })

    // Сортируем: сперва те, у кого суммарное время > 0
    detailRows.sort((a, b) => {
      const aSum = a.work_minutes + a.break_minutes
      const bSum = b.work_minutes + b.break_minutes
      if (aSum===0 && bSum>0) return 1
      if (aSum>0 && bSum===0) return -1
      // иначе по имени
      return a.user_name.localeCompare(b.user_name)
    })

    // Заполняем stats
    stats.totalUsers          = detailRows.length
    stats.activeUsers         = activeCount
    stats.onBreak             = onBreakCount
    stats.offlineUsers        = offlineCount
    stats.total_work_minutes  = Math.floor(totalWorkSeconds  / 60)
    stats.total_break_minutes = Math.floor(totalBreakSeconds / 60)
    stats.details = detailRows

    // ---------- Считаем dailyTrend ----------
    // Соберём логи по дате + учтём время
    const dailyMap = {}
    allLogs.forEach(log => {
      const datePart = log.acted_at.split(' ')[0]  // "YYYY-MM-DD"
      if (!dailyMap[datePart]) {
        dailyMap[datePart] = {
          workSec: 0,
          breakSec: 0,
          lastState: 'none',
          lastTime: null
        }
      }
    })



    // ПОЛНЫЙ подсчёт может быть таким же, как выше, но с агрегированием. Для примера:
    allLogs.forEach(log => {
      const datePart = log.acted_at.split(' ')[0]
      const t = new Date(log.acted_at).getTime() / 1000
      if (!dailyMap[datePart].lastTime) {
        dailyMap[datePart].lastTime = t
      }

      // если приходит event_id => 1 start, 2 start_break, 3 end_break, 4 stop
      // примитивная логика:
      if (log.event_id===1) {
        // Закрыть прошлый state
        if (dailyMap[datePart].lastState==='work') {
          dailyMap[datePart].workSec += (t - dailyMap[datePart].lastTime)
        }
        dailyMap[datePart].lastState = 'work'
        dailyMap[datePart].lastTime  = t
      }
      else if (log.event_id===2) {
        // переходим в break
        if (dailyMap[datePart].lastState==='work') {
          dailyMap[datePart].workSec += (t - dailyMap[datePart].lastTime)
        }
        dailyMap[datePart].lastState = 'break'
        dailyMap[datePart].lastTime  = t
      }
      else if (log.event_id===3) {
        // end break => work
        dailyMap[datePart].lastState = 'work'
        dailyMap[datePart].lastTime  = t
      }
      else if (log.event_id===4) {
        // stop
        if (dailyMap[datePart].lastState==='work') {
          dailyMap[datePart].workSec += (t - dailyMap[datePart].lastTime)
        }
        dailyMap[datePart].lastState = 'none'
        dailyMap[datePart].lastTime  = t
      }
    })

    // Если на какой-то дате "work" не завершён
    const nowSec = Date.now() / 1000
    for (let dateKey in dailyMap) {
      if (dailyMap[dateKey].lastState==='work' && dailyMap[dateKey].lastTime) {
        dailyMap[dateKey].workSec += nowSec - dailyMap[dateKey].lastTime
      }
    }

    // Преобразуем dailyMap в массив
    const dailyTrendArr = Object.keys(dailyMap).sort().map(dateKey => {
      const wMin = Math.floor(dailyMap[dateKey].workSec / 60)
      return { date: dateKey, work_minutes: wMin }
    })

    dailyTrend.value = dailyTrendArr

  } catch (err) {
    console.error('Ошибка в fetchDashboardData:', err)
  }
}

// Цвет для статуса
function statusClass(status) {
  switch (status) {
    case 'active':   return 'status-active'
    case 'on_break': return 'status-break'
    default:         return 'status-offline'
  }
}
</script>

<style scoped>
.admin-dashboard {
  background: #1f1f1f;
  color: #fff;
  padding: 1rem 2rem;
  border-radius: 8px;
  min-height: 90vh;
  box-shadow: 0 2px 6px rgba(0,0,0,0.5);
}

.dashboard-title {
  text-align: center;
  font-size: 2rem;
  color: #ff4545;
  margin-bottom: 1rem;
}

/* Фильтры */
.filters {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.filter-dates {
  display: flex;
  gap: 1rem;
}

.filter-row {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.filters input[type="date"],
.filters select {
  background: #2d2d2d;
  color: #fff;
  border: 1px solid #444;
  padding: 4px 8px;
  border-radius: 4px;
}

.apply-btn {
  background: #ff4545;
  border: none;
  color: #fff;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
}
.apply-btn:hover {
  background: #ff6f6f;
}

/* Карточки статистики */
.stats-cards {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  justify-content: center;
  margin-bottom: 1.5rem;
}

.card {
  background: #2d2d2d;
  border-radius: 8px;
  padding: 1rem;
  width: 180px;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.card h3 {
  color: #ff4545;
  margin-bottom: 0.5rem;
  font-size: 1rem;
}

.card p {
  font-size: 1.4rem;
  font-weight: bold;
  margin: 0;
}

/* Диаграммы */
.charts-section {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  justify-content: space-evenly;
  margin-bottom: 1.5rem;
}

.chart-block {
  flex: 1 1 300px;
  min-width: 280px;
  background: #2d2d2d;
  border-radius: 8px;
  padding: 1rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.3);
  display: flex;
  flex-direction: column;
  align-items: center;
  max-width: 400px;
  max-height: 550px;
  overflow: auto;
}

.chart-block h2 {
  color: #ff4545;
  margin-bottom: 0.5rem;
  font-size: 1rem;
  text-align: center;
}

/* Таблица */
.details-table {
  background: #2d2d2d;
  border-radius: 8px;
  padding: 1rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.3);
  margin-bottom: 1rem;
}

.details-table h2 {
  color: #ff4545;
  text-align: center;
  margin-bottom: 0.5rem;
}

.details-table table {
  width: 100%;
  border-collapse: collapse;
}

.details-table th,
.details-table td {
  border: 1px solid #444;
  padding: 0.75rem;
  text-align: center;
}

.details-table th {
  background: #1f1f1f;
  color: #ff4545;
}

/* Цвет статуса */
.status-active {
  color: #4caf50;
  font-weight: bold;
}
.status-break {
  color: #ffc107;
  font-weight: bold;
}
.status-offline {
  color: #ff4545;
  font-weight: bold;
}
</style>

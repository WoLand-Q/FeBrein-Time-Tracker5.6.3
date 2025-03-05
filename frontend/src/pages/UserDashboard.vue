<template>
  <div class="user-dashboard">
    <!-- Фильтры (даты, пользователь) -->
    <div class="filters">
      <label>
        Початкова дата:
        <input type="date" v-model="startDate" class="date-input" />
      </label>
      <label>
        Кінцева дата:
        <input type="date" v-model="endDate" class="date-input" />
      </label>

      <!-- Фильтр по пользователю -->
      <label>
        Користувач:
        <select v-model="selectedUser" class="user-select">
          <option value="">Усі</option>
          <option v-for="u in allUsers" :key="u.id" :value="u.id">
            {{ u.name }}
          </option>
        </select>
      </label>

      <button @click="fetchDashboardData" class="apply-btn">
        Застосувати
      </button>
    </div>

    <!-- Карточки статистики (общее по всем пользователям, как прежде) -->
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

    <!-- Сумарно за період (теперь учитывает только filteredDetails) -->
    <div class="summary-block">
      <h3>Сумарно за період</h3>
      <div class="flex-sum">
        <div class="sum-card">
          <h4>Робочий час</h4>
          <!-- Здесь уже sumFilteredWorkMinutes, а не stats.total_work_minutes -->
          <p>{{ convertToHoursAndMinutes(summaryFiltered.totalWork) }}</p>
        </div>
        <div class="sum-card">
          <h4>Час перерв</h4>
          <!-- Аналогично sumFilteredBreakMinutes -->
          <p>{{ convertToHoursAndMinutes(summaryFiltered.totalBreak) }}</p>
        </div>
      </div>
    </div>

    <!-- Таблица детализации (filteredDetails) -->
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
        <tr v-for="(row, idx) in filteredDetails" :key="idx">
          <td>{{ row.user_name }}</td>
          <td>{{ row.dateUsed || '-' }}</td>
          <td>{{ formatTime(row.startWork) }}</td>
          <td>{{ formatTime(row.startBreak) }}</td>
          <td>{{ formatTime(row.endBreak) }}</td>
          <td>{{ formatTime(row.endWork) }}</td>
          <td>
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

<script>
import { ref, reactive, onMounted, onBeforeUnmount, computed } from 'vue'
import api from '@/api'

// Утилиты
function convertToHoursAndMinutes(totalMinutes) {
  const h = Math.floor(totalMinutes / 60)
  const m = totalMinutes % 60
  return `${h} год ${m} хв`
}
function formatTime(dtString) {
  if (!dtString) return '-'
  const [datePart, timePart] = dtString.split(' ')
  if (!timePart) return '-'
  const [hh, mm] = timePart.split(':')
  return `${hh}:${mm}`
}
function statusClass(status) {
  switch (status) {
    case 'active':   return 'status-active'
    case 'on_break': return 'status-break'
    default:         return 'status-offline'
  }
}

export default {
  name: 'DashboardPage',
  setup() {
    // Даты по умолчанию = сегодня
    const now = new Date()
    const todayStr = now.toISOString().slice(0, 10)
    const startDate = ref(todayStr)
    const endDate = ref(todayStr)

    // Фильтр пользователя
    const selectedUser = ref('')
    const allUsers = ref([])

    // Статистика (для всех пользователей)
    const stats = reactive({
      totalUsers: 0,
      activeUsers: 0,
      onBreak: 0,
      offlineUsers: 0,
      total_work_minutes: 0,     // (Общее, не используется в summary-block)
      total_break_minutes: 0,    // (Общее, не используется в summary-block)
      details: []                // Все записи, не отфильтрованные
    })

    let pollingInterval = null

    async function fetchDashboardData() {
      try {
        const params = {}
        if (startDate.value) params.start_date = startDate.value
        if (endDate.value)   params.end_date   = endDate.value

        const respLogs = await api.get('/api/userTimeLogs', { params })
        let allLogs = respLogs.data.data || []

        // Можно дополнительно фильтровать по датам, если нужно
        allLogs = allLogs.filter(log => {
          const datePart = log.acted_at.split(' ')[0]
          return datePart >= startDate.value && datePart <= endDate.value
        })

        const respUsers = await api.get('/api/users')
        const userData  = respUsers.data.data || respUsers.data || []
        allUsers.value  = userData

        // Формируем логи
        const logsMap = {}
        allLogs.forEach(log => {
          const uid = log.user_id
          const datePart = log.acted_at.split(' ')[0]
          if (!logsMap[uid]) logsMap[uid] = {}
          if (!logsMap[uid][datePart]) logsMap[uid][datePart] = []
          logsMap[uid][datePart].push(log)
        })

        let totalWorkSeconds  = 0
        let totalBreakSeconds = 0
        let activeCount       = 0
        let onBreakCount      = 0
        let offlineCount      = 0

        const detailRows = []

        userData.forEach(u => {
          const uid = u.id
          const userLogsByDate = logsMap[uid] || {}

          const dateKeys = Object.keys(userLogsByDate)
          if (dateKeys.length === 0) {
            // Нет логов => офлайн
            detailRows.push({
              user_id: uid,
              user_name: u.name,
              dateUsed: null,
              status: 'offline',
              work_minutes: 0,
              break_minutes: 0,
              startWork: null,
              startBreak: null,
              endBreak: null,
              endWork: null
            })
            offlineCount++
            return
          }

          // Для каждого дня
          dateKeys.forEach(dateKey => {
            const logsForThatDay = [...userLogsByDate[dateKey]].sort((a,b) =>
                new Date(a.acted_at) - new Date(b.acted_at)
            )

            let workSec = 0
            let breakSec = 0
            let state = 'none'
            let lastTime = null

            let startWorkTime  = null
            let startBreakTime = null
            let endBreakTime   = null
            let endWorkTime    = null

            logsForThatDay.forEach(log => {
              const eid = log.event_id
              const t   = new Date(log.acted_at).getTime() / 1000

              // Переключаемся между work/break
              if (eid === 1) {
                if (!startWorkTime) startWorkTime = log.acted_at
                if (state==='work' && lastTime!=null) {
                  workSec += (t - lastTime)
                } else if (state==='break' && lastTime!=null) {
                  breakSec += (t - lastTime)
                }
                state = 'work'
                lastTime = t
              } else if (eid === 2) {
                if (!startBreakTime) startBreakTime = log.acted_at
                if (state==='work' && lastTime!=null) {
                  workSec += (t - lastTime)
                } else if (state==='break' && lastTime!=null) {
                  breakSec += (t - lastTime)
                }
                state = 'break'
                lastTime = t
              } else if (eid === 3) {
                if (!endBreakTime) endBreakTime = log.acted_at
                if (state==='break' && lastTime!=null) {
                  breakSec += (t - lastTime)
                }
                state = 'work'
                lastTime = t
              } else if (eid === 4) {
                if (!endWorkTime) endWorkTime = log.acted_at
                if (state==='work' && lastTime!=null) {
                  workSec += (t - lastTime)
                } else if (state==='break' && lastTime!=null) {
                  breakSec += (t - lastTime)
                }
                state = 'none'
                lastTime = t
              }
            })

            // Если день незавершён
            const nowSec = Date.now() / 1000
            if (state==='work' && lastTime!=null) {
              workSec += (nowSec - lastTime)
            } else if (state==='break' && lastTime!=null) {
              breakSec += (nowSec - lastTime)
            }

            let finalStatus = 'offline'
            if (state==='work')   finalStatus = 'active'
            else if (state==='break') finalStatus = 'on_break'

            const wMin = Math.floor(workSec/60)
            const bMin = Math.floor(breakSec/60)

            detailRows.push({
              user_id: uid,
              user_name: u.name,
              dateUsed: dateKey,
              status: finalStatus,
              work_minutes: wMin,
              break_minutes: bMin,
              startWork:  startWorkTime,
              startBreak: startBreakTime,
              endBreak:   endBreakTime,
              endWork:    endWorkTime
            })

            totalWorkSeconds  += workSec
            totalBreakSeconds += breakSec

            if (finalStatus==='active')   activeCount++
            else if (finalStatus==='on_break') onBreakCount++
            else offlineCount++
          })
        })

        // Сортируем
        detailRows.sort((a,b) => {
          if (a.dateUsed && !b.dateUsed) return -1
          if (!a.dateUsed && b.dateUsed) return 1
          const nameA = a.user_name?.toLowerCase() || ''
          const nameB = b.user_name?.toLowerCase() || ''
          if (nameA < nameB) return -1
          if (nameA > nameB) return 1
          if (!a.dateUsed) return 1
          if (!b.dateUsed) return -1
          return a.dateUsed.localeCompare(b.dateUsed)
        })

        // Заполняем stats
        stats.details = detailRows
        stats.totalUsers          = userData.length
        stats.activeUsers         = activeCount
        stats.onBreak             = onBreakCount
        stats.offlineUsers        = offlineCount
        stats.total_work_minutes  = Math.floor(totalWorkSeconds / 60)
        stats.total_break_minutes = Math.floor(totalBreakSeconds / 60)

      } catch (err) {
        console.error("fetchDashboardData error:", err)
      }
    }

    onMounted(() => {
      fetchDashboardData()
      pollingInterval = setInterval(() => {
        fetchDashboardData()
      }, 10000)
    })
    onBeforeUnmount(() => {
      if (pollingInterval) clearInterval(pollingInterval)
    })

    // Фильтр по пользователю (для таблицы детализации)
    const filteredDetails = computed(() => {
      if (!selectedUser.value) {
        return stats.details
      } else {
        return stats.details.filter(r => r.user_id === selectedUser.value)
      }
    })

    // Вычисляем общее время работы/перерывов ИМЕННО по filteredDetails
    const summaryFiltered = computed(() => {
      let totalWork = 0
      let totalBreak = 0
      for (const row of filteredDetails.value) {
        totalWork  += row.work_minutes
        totalBreak += row.break_minutes
      }
      return { totalWork, totalBreak }
    })

    return {
      startDate,
      endDate,
      selectedUser,
      allUsers,
      stats,

      fetchDashboardData,
      convertToHoursAndMinutes,
      formatTime,
      statusClass,
      filteredDetails,

      // Новый вычислимый объект
      summaryFiltered
    }
  }
}
</script>

<style scoped>
.user-dashboard {
  padding: 1rem;
  background: #121212;
  color: #fff;
  min-height: 100vh;
}

/* Фильтры */
.filters {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
  justify-content: center;
}
.filters label {
  display: flex;
  align-items: center;
  font-size: 0.95rem;
}
.date-input {
  margin-left: 0.4rem;
  padding: 0.25rem 0.5rem;
  background: #1f1f1f;
  border: 1px solid #444;
  color: #fff;
  border-radius: 4px;
}
.user-select {
  margin-left: 0.4rem;
  padding: 0.25rem 0.5rem;
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
  margin-bottom: 1.5rem;
  justify-content: center;
}
.card {
  flex: 1 1 160px;
  max-width: 200px;
  background: #1f1f1f;
  border-radius: 8px;
  padding: 1rem;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.3);
}
.card h3 {
  font-size: 1rem;
  margin-bottom: 0.5rem;
  color: #ff4545;
}
.card p {
  font-size: 1.6rem;
  font-weight: bold;
  margin: 0;
}

.summary-block {
  background: #1f1f1f;
  border-radius: 8px;
  padding: 1rem;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.3);
  max-width: 1000px;
  margin: -1rem auto 1.5rem auto;
}
.summary-block h3 {
  margin: 0 0 0.5rem;
  color: #ff4545;
}
.flex-sum {
  display: flex;
  gap: 1rem;
  justify-content: center;
}
.sum-card {
  background: #2d2d2d;
  padding: 1rem;
  border-radius: 4px;
  width: 130px;
}
.sum-card h4 {
  margin: 0 0 0.25rem;
  color: #ff4545;
}
.sum-card p {
  font-size: 1.2rem;
  margin: 0;
  font-weight: bold;
}

/* Таблица лога */
.logs-block {
  background: #1f1f1f;
  border-radius: 8px;
  padding: 1rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.3);
  max-width: 1000px;
  margin: 0 auto;
}
.logs-block h2 {
  margin: 0 0 1rem;
  color: #ff4545;
  text-align: center;
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
  color: #4caf50;
  font-weight: 600;
}
.status-break {
  color: #ffc107;
  font-weight: 600;
}
.status-offline {
  color: #ff4545;
  font-weight: 600;
}
</style>

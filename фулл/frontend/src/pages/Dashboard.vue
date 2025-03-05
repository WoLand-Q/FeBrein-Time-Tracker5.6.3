<template>
  <div class="dashboard-wrapper">
    <!-- Заголовок -->
    <h1 class="main-title">Дашбоард</h1>

    <!-- Верхняя секция: Фильтры дат + Карточки -->
    <div class="top-section">
      <!-- Блок фильтров -->
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

      <!-- Карточки статистики -->
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
    </div>

    <!-- Средняя секция: суммарные показатели, диаграмма статусов и блок "Работа/Перерывы" -->
    <div class="middle-section">
      <!-- Суммарные показатели -->
      <div class="summary-block">
        <h2>Сумарно ({{ startDate || '...' }} - {{ endDate || '...' }})</h2>
        <p>Відпрацьовано (хв): {{ stats.total_work_minutes }}</p>
        <p>Перерви (хв): {{ stats.total_break_minutes }}</p>
      </div>

      <!-- Новая диаграмма статусов (актив/перерыв/офлайн) -->
      <div class="online-status-block">
        <h2>Статус користувачів</h2>
        <!-- Диаграмма статусов -->
        <Chart :data="onlineStatusChartData" />

        <!-- Пример таблицы со статусами (необязательно) -->
        <div class="details-section">
          <h2>Хто в онлайні / офлайні</h2>
          <table>
            <thead>
            <tr>
              <th>Користувач</th>
              <th>Статус</th>
            </tr>
            </thead>
            <tbody>
            <tr
                v-for="row in stats.details"
                :key="row.user_id"
            >
              <td>{{ row.user_name }}</td>
              <td>
                <!-- Переводим status в удобочитаемый вид -->
                <span v-if="row.status === 'active'">Працює</span>
                <span v-else-if="row.status === 'on_break'">На перерві</span>
                <span v-else>Офлайн</span>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Диаграмма "Работа/Перерывы" + таблица детализации -->
      <div class="chart-and-table-block">
        <h2>Робота / Перерви</h2>
        <Chart :data="chartData" />

        <div class="details-section">
          <h2>Деталізація</h2>
          <table>
            <thead>
            <tr>
              <th>Користувач</th>
              <th>Робота (хв)</th>
              <th>Перерва (хв)</th>
            </tr>
            </thead>
            <tbody>
            <tr
                v-for="row in stats.details"
                :key="row.user_id"
            >
              <td>{{ row.user_name }}</td>
              <td>{{ row.work_minutes }}</td>
              <td>{{ row.break_minutes }}</td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import Chart from '@/components/Chart.vue';
import api from '@/api';

// Дата по умолчанию — сегодня
const todayStr = new Date().toISOString().slice(0, 10);
const startDate = ref(todayStr);
const endDate = ref(todayStr);

// Инициализация статистики
const stats = reactive({
  totalUsers: 0,
  activeUsers: 0,
  onBreak: 0,
  offlineUsers: 0,
  total_work_minutes: 0,
  total_break_minutes: 0,
  details: [] // [{ user_id, user_name, work_minutes, break_minutes, status }, ...]
});

// Исходные данные для диаграммы "Работа/Перерывы"
const chartData = ref({
  labels: ['Робота (хв)', 'Перерва (хв)'],
  datasets: [
    {
      data: [0, 0],
      backgroundColor: ['#4caf50', '#ffa000'],
    },
  ],
});

// Диаграмма для отображения статусов (Актив/Перерыв/Офлайн)
const onlineStatusChartData = ref({
  labels: ['Працюють', 'На перерві', 'Офлайн'],
  datasets: [
    {
      data: [0, 0, 0],
      backgroundColor: ['#4caf50', '#ffa000', '#f44336'], // цвета можно менять
    },
  ],
});

// Функция получения данных для Dashboard
const fetchDashboardData = async () => {
  try {
    // Формирование параметров запроса
    const params = {};
    if (startDate.value) params.start_date = startDate.value;
    if (endDate.value) params.end_date = endDate.value;

    // 1) Получаем логи работы пользователей
    const respLogs = await api.get('/api/admin/userTimeLogs', { params });
    const allLogs = respLogs.data.data || [];

    // 2) Получаем список пользователей
    const respUsers = await api.get('/api/admin/users');
    const allUsers = respUsers.data.data || respUsers.data;

    // Группируем логи по user_id
    const logsByUser = {};
    allLogs.forEach(log => {
      const uid = log.user_id;
      if (!logsByUser[uid]) {
        logsByUser[uid] = [];
      }
      logsByUser[uid].push(log);
    });

    let totalWorkSeconds = 0;
    let totalBreakSeconds = 0;
    let activeCount = 0;
    let onBreakCount = 0;
    let offlineCount = 0;

    // detailMap для хранения информации по каждому пользователю
    const detailMap = {};

    // Вспомогательная функция для подсчёта времени и статуса
    const parseUserLogs = (userLogs) => {
      userLogs.sort((a, b) => new Date(a.acted_at) - new Date(b.acted_at));

      let workSec = 0;
      let breakSec = 0;
      let state = 'none';
      let lastTime = null;

      userLogs.forEach(l => {
        const eid = l.event_id;
        const t = new Date(l.acted_at).getTime() / 1000;

        if (eid === 1) {
          // start
          if (state === 'work' && lastTime != null) {
            workSec += t - lastTime;
          }
          if (state === 'break' && lastTime != null) {
            breakSec += t - lastTime;
          }
          state = 'work';
          lastTime = t;
        } else if (eid === 2) {
          // start_break
          if (state === 'work' && lastTime != null) {
            workSec += t - lastTime;
          }
          if (state === 'break' && lastTime != null) {
            breakSec += t - lastTime;
          }
          state = 'break';
          lastTime = t;
        } else if (eid === 3) {
          // stop_break => back to work
          if (state === 'break' && lastTime != null) {
            breakSec += t - lastTime;
          }
          state = 'work';
          lastTime = t;
        } else if (eid === 4) {
          // stop
          if (state === 'work' && lastTime != null) {
            workSec += t - lastTime;
          }
          if (state === 'break' && lastTime != null) {
            breakSec += t - lastTime;
          }
          state = 'none';
          lastTime = t;
        }
      });

      // Если состояние не завершено, считаем время до "сейчас"
      const nowSec = Date.now() / 1000;
      if (state === 'work' && lastTime != null) {
        workSec += nowSec - lastTime;
      } else if (state === 'break' && lastTime != null) {
        breakSec += nowSec - lastTime;
      }

      // Определяем финальный статус
      let finalStatus = 'offline';
      if (state === 'work') finalStatus = 'active';
      else if (state === 'break') finalStatus = 'on_break';

      return { workSec, breakSec, finalStatus };
    };

    // Обработка данных для каждого пользователя
    allUsers.forEach(u => {
      const uid = u.id;
      const userLogs = logsByUser[uid] || [];
      const { workSec, breakSec, finalStatus } = parseUserLogs(userLogs);

      totalWorkSeconds += workSec;
      totalBreakSeconds += breakSec;

      if (finalStatus === 'active') activeCount++;
      else if (finalStatus === 'on_break') onBreakCount++;
      else offlineCount++;

      detailMap[uid] = {
        user_id: uid,
        user_name: u.name || `Користувач #${uid}`,
        work_minutes: Math.floor(workSec / 60),
        break_minutes: Math.floor(breakSec / 60),
        status: finalStatus
      };
    });

    // Преобразуем detailMap в массив
    const detailsArray = Object.values(detailMap);

    // Обновляем stats
    stats.totalUsers = allUsers.length;
    stats.activeUsers = activeCount;
    stats.onBreak = onBreakCount;
    stats.offlineUsers = offlineCount;
    stats.total_work_minutes = Math.floor(totalWorkSeconds / 60);
    stats.total_break_minutes = Math.floor(totalBreakSeconds / 60);
    stats.details = detailsArray;

    // Обновляем данные для диаграммы "Работа/Перерывы"
    chartData.value = {
      labels: ['Робота (хв)', 'Перерва (хв)'],
      datasets: [
        {
          data: [stats.total_work_minutes, stats.total_break_minutes],
          backgroundColor: ['#4caf50', '#ffa000'],
        },
      ],
    };

    // Обновляем данные для диаграммы "Статус пользователей"
    onlineStatusChartData.value = {
      labels: ['Працюють', 'На перерві', 'Офлайн'],
      datasets: [
        {
          data: [activeCount, onBreakCount, offlineCount],
          backgroundColor: ['#4caf50', '#ffa000', '#f44336'],
        },
      ],
    };
  } catch (err) {
    console.error('Ошибка загрузки:', err);
  }
};

// Загружаем данные при монтировании компонента
onMounted(() => {
  fetchDashboardData();
});
</script>

<style scoped>
.dashboard-wrapper {
  padding: 2rem;
  background: #1f1f1f;
  color: #fff;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
}

/* Главный заголовок */
.main-title {
  text-align: center;
  color: #ff4545;
  font-size: 2.2rem;
  margin-bottom: 2rem;
}

/* Верхняя секция: фильтры + карточки */
.top-section {
  display: flex;
  flex-wrap: wrap;
  margin-bottom: 1.5rem;
}

/* Блок фильтров */
.filters {
  flex: 1;
  min-width: 220px;
  max-width: 300px;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-right: 1rem;
}

.filters label {
  display: flex;
  align-items: center;
  color: #fff;
}

.date-input {
  background: #2d2d2d;
  color: #fff;
  border: 1px solid #444;
  padding: 4px 8px;
  border-radius: 4px;
  margin-left: 0.5rem;
}

.apply-btn {
  background: #ff4545;
  color: #fff;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
  margin-top: 0.5rem;
}

.apply-btn:hover {
  background: #ff6565;
}

/* Карточки статистики */
.stats-cards {
  flex: 2;
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  justify-content: flex-start;
}

.card {
  flex: 1 1 160px;
  min-width: 140px;
  background: #2d2d2d;
  text-align: center;
  border-radius: 8px;
  padding: 1rem;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
  transition: transform 0.2s, box-shadow 0.3s;
}

.card:hover {
  transform: translateY(-4px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.5);
}

.card h3 {
  font-size: 1rem;
  color: #ff4545;
  margin-bottom: 0.5rem;
}

.card p {
  font-size: 1.6rem;
  font-weight: bold;
}

/* Средняя секция */
.middle-section {
  display: flex;
  flex-wrap: wrap;
  gap: 2rem;
  align-items: flex-start;
  justify-content: space-between; /* чтобы три блока распределялись */
}

/* Секция суммарных показателей */
.summary-block {
  flex: 1;
  background: #2d2d2d;
  border-radius: 8px;
  padding: 1rem;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
}

.summary-block h2 {
  color: #ff4545;
  margin-bottom: 0.5rem;
}

/* Новая секция для диаграммы статусов */
.online-status-block {
  flex: 1;
  background: #2d2d2d;
  border-radius: 8px;
  padding: 1rem;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
}

.online-status-block h2 {
  color: #ff4545;
  margin-bottom: 0.5rem;
  text-align: center;
}

/* Секция "Работа/Перерывы" */
.chart-and-table-block {
  flex: 1;
  background: #2d2d2d;
  border-radius: 8px;
  padding: 1rem;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
}

.chart-and-table-block h2 {
  color: #ff4545;
  margin-bottom: 0.5rem;
  text-align: center;
}

/* Таблица детализации */
.details-section {
  margin-top: 1rem;
  text-align: center;
}

.details-section table {
  margin: 0.5rem auto;
  border-collapse: collapse;
  width: 100%;
  max-width: 450px;
}

.details-section th,
.details-section td {
  padding: 0.75rem 1rem;
  border: 1px solid #444;
}

.details-section th {
  background: #2d2d2d;
  color: #ff4545;
}

.details-section td {
  text-align: center;
}
</style>

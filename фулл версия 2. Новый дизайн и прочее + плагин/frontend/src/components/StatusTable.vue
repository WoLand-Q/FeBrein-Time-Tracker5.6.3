<template>
  <div class="status-table-container">
    <table class="status-table">
      <thead>
      <tr>
        <th>Користувач</th>
        <th>Статус</th>
        <th>Відпрацьовано</th>
        <th>Перерва</th>
        <th class="col-work">Початок роботи</th>
        <th class="col-work">Завершення роботи</th>
        <th class="col-break">Початок перерви</th>
        <th class="col-break">Завершення перерви</th>
      </tr>
      </thead>
      <tbody>
      <!-- Используем отсортированный список -->
      <tr v-for="user in sortedUsers" :key="user.id">
        <td>{{ user.name }}</td>
        <td>
            <span :class="statusClass(user.status)">
              {{ translateStatus(user.status) }}
            </span>
        </td>

        <!-- Показываем отработанные/перерыв как "X г Y хв" -->
        <td>{{ formatHoursAndMinutes(user.workedMins) }}</td>
        <td>{{ formatHoursAndMinutes(user.breakMins) }}</td>

        <!-- Показываем только время "HH:MM" для начала/конца работы/перерыва -->
        <td class="col-work">{{ formatTimeOnly(user.lastStartWork) }}</td>
        <td class="col-work">{{ formatTimeOnly(user.lastStopWork) }}</td>
        <td class="col-break">{{ formatTimeOnly(user.lastStartBreak) }}</td>
        <td class="col-break">{{ formatTimeOnly(user.lastStopBreak) }}</td>
      </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: "StatusTable",
  props: {
    users: {
      type: Array,
      default: () => []
    }
  },
  computed: {
    /**
     * Сортируем пользователей по статусу:
     *  1) 'працює'   (working)   -> вверх
     *  2) 'перерва'  (break)     -> середина
     *  3) 'завершено'(done)      -> низ
     */
    sortedUsers() {
      const statusOrder = {
        'працює': 1,
        'перерва': 2,
        'завершено': 3
      };
      // Делаем копию массива и сортируем
      return this.users.slice().sort((a, b) => {
        // Если вдруг статус неизвестен, ставим его в конец (999)
        const aVal = statusOrder[a.status] || 999;
        const bVal = statusOrder[b.status] || 999;
        return aVal - bVal;
      });
    },
  },
  methods: {
    /**
     * Переводим статус на украинский.
     */
    translateStatus(st) {
      switch (st) {
        case "працює":  return "Працює";
        case "перерва": return "Перерва";
        default:        return "Завершено";
      }
    },

    /**
     * Превращаем минуты в формат "X г Y хв".
     * Если X=0, то только "Y хв".
     */
    formatHoursAndMinutes(totalMins) {
      if (!totalMins || totalMins <= 0) return "0 хв";
      const h = Math.floor(totalMins / 60);
      const m = totalMins % 60;
      if (h === 0) {
        return m + " хв";
      }
      return h + " г " + m + " хв";
    },

    /**
     * Показываем только время "HH:MM" из строки вида "YYYY-MM-DD HH:MM:SS".
     */
    formatTimeOnly(dateStr) {
      if (!dateStr) return "";
      // Заменяем пробел на "T" для парсинга
      const isoLike = dateStr.replace(" ", "T");
      const d = new Date(isoLike);
      if (isNaN(d.getTime())) return ""; // если не парсится

      // Возвращаем только "HH:MM"
      return d.toLocaleTimeString("uk-UA", {
        hour: "2-digit",
        minute: "2-digit",
      });
    },

    /**
     * Красим статус в зависимости от значения
     */
    statusClass(st) {
      switch (st) {
        case 'працює':  return 'status-work';
        case 'перерва': return 'status-break';
        default:        return 'status-stopped';
      }
    }
  }
};
</script>

<style scoped>
/* Контейнер для горизонтального скролла, если таблица широкая */
.status-table-container {
  overflow-x: auto;
  margin-top: 1rem;
}

/* Сама таблица */
.status-table {
  width: 100%;
  border-collapse: collapse;
  color: #fff;
  background-color: #2a2a2a; /* тёмный фон */
  border: 1px solid #444;
  border-radius: 6px;
  overflow: hidden; /* скрываем углы */
  font-size: 0.95rem;
}

/* Шапка */
.status-table thead {
  background-color: #333;
}
.status-table th {
  padding: 0.6rem 0.8rem;
  text-align: left;
  font-weight: 600;
  border-bottom: 1px solid #444;
}

/* Тело */
.status-table td {
  padding: 0.5rem 0.8rem;
  border-bottom: 1px solid #444;
}
.status-table tbody tr:last-child td {
  border-bottom: none;
}

/* Подсветка колонок «работы» */
.col-work {
  background-color: rgba(46, 125, 50, 0.07); /* чуть более заметный зелёный фон */
}
/* Подсветка колонок «перерыва» */
.col-break {
  background-color: rgba(255, 193, 7, 0.07); /* чуть более заметный жёлтый фон */
}

/* Цвета статусов (текст) */
.status-work {
  color: #4caf50; /* зелёный */
}
.status-break {
  color: #ffc107; /* жёлтый */
}
.status-stopped {
  color: #ff4545; /* красный */
}
</style>

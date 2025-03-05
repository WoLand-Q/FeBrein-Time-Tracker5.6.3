<template>
  <div class="status-table-container">
    <table class="status-table">
      <thead>
      <tr>
        <th>Користувач</th>
        <th>Група</th>
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
      <tr v-for="user in sortedUsers" :key="user.id">
        <!-- (1) Имя пользователя -->
        <td>{{ user.name }}</td>

        <!-- (2) Название группы -->
        <td>{{ user.groupName }}</td>

        <!-- (3) Статус -->
        <td>
            <span :class="statusClass(user.status)">
              {{ translateStatus(user.status) }}
            </span>
        </td>

        <!-- (4) Отработанное время (минуты -> X г Y хв) -->
        <td>{{ formatHoursAndMinutes(user.workedMins) }}</td>

        <!-- (5) Время перерыва -->
        <td>{{ formatHoursAndMinutes(user.breakMins) }}</td>

        <!-- (6) Начало работы -->
        <td class="col-work">{{ formatTimeOnly(user.lastStartWork) }}</td>

        <!-- (7) Завершение работы -->
        <td class="col-work">{{ formatTimeOnly(user.lastStopWork) }}</td>

        <!-- (8) Начало перерыва -->
        <td class="col-break">{{ formatTimeOnly(user.lastStartBreak) }}</td>

        <!-- (9) Завершение перерыва -->
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
    // Массив пользователей, каждый содержит поля:
    // name, groupName, status, workedMins, breakMins,
    // lastStartWork, lastStopWork, lastStartBreak, lastStopBreak
    users: {
      type: Array,
      default: () => []
    }
  },
  computed: {
    /**
     * Сортируем пользователей по статусу:
     *  1) 'працює'   -> вверх
     *  2) 'перерва'  -> середина
     *  3) 'завершено'-> низ
     */
    sortedUsers() {
      const statusOrder = {
        'працює': 1,
        'перерва': 2,
        'завершено': 3
      };
      return this.users.slice().sort((a, b) => {
        const aVal = statusOrder[a.status] || 999;
        const bVal = statusOrder[b.status] || 999;
        return aVal - bVal;
      });
    },
  },
  methods: {

    translateStatus(st) {
      switch (st) {
        case "працює":  return "Працює";
        case "перерва": return "Перерва";
        default:        return "Завершено";
      }
    },

    /**
     * Превращаем minutes -> "X г Y хв".
     * Если X=0, то просто "Y хв".
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
     * Из строки даты берем только "HH:MM".
     * При необходимости подгоняем под ваш часовой пояс.
     */
    formatTimeOnly(dateStr) {
      if (!dateStr) return "";
      // Преобразуем "YYYY-MM-DD HH:MM:SS" -> Date
      const isoLike = dateStr.replace(" ", "T");
      const d = new Date(isoLike);
      if (isNaN(d.getTime())) return "";

      return d.toLocaleTimeString("uk-UA", {
        hour: "2-digit",
        minute: "2-digit",
      });
    },

    /**
     * Подбираем класс для статуса, чтобы подсвечивать цветом.
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
  overflow: hidden;
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
  background-color: rgba(46, 125, 50, 0.07); /* слегка зелёный фон */
}
/* Подсветка колонок «перерыва» */
.col-break {
  background-color: rgba(255, 193, 7, 0.07); /* слегка жёлтый фон */
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

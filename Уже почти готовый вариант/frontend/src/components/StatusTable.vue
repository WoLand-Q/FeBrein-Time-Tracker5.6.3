<template>
  <table class="status-table">
    <thead>
    <tr>
      <th>Користувач</th>
      <th>Статус</th>
      <th>Відпрацьовано</th>
      <th>Перерва</th>
    </tr>
    </thead>
    <tbody>
    <tr v-for="user in users" :key="user.id">
      <td>{{ user.name }}</td>
      <td>
        <!-- В зависимости от user.currentStatus -->
        <span v-if="user.currentStatus === 'працює'" class="text-green-400">Працює</span>
        <span v-else-if="user.currentStatus === 'перерва'" class="text-yellow-400">Перерва</span>
        <span v-else class="text-red-400">Завершено</span>
      </td>
      <td>
        <!-- Вызываем метод formatHM для отображения в формате "X год Y хв" -->
        {{ formatHM(user.workedMins) }}
      </td>
      <td>
        {{ formatHM(user.breakMins) }}
      </td>
    </tr>
    </tbody>
  </table>
</template>

<script>
export default {
  name: "StatusTable",
  props: {
    users: {
      type: Array,
      default: () => [],
    },
  },
  methods: {
    /**
     * Преобразуем число минут в формат "X год Y хв".
     * Пример: 125 мин => "2 год 5 хв".
     */
    formatHM(totalMinutes) {
      const h = Math.floor(totalMinutes / 60);
      const m = totalMinutes % 60;
      // Если часов 0, можно показать "0 год 15 хв"
      return `${h} год ${m} хв`;
    },
  },
};
</script>

<style scoped>
.status-table {
  width: 100%;
  border-collapse: collapse;
  background: #2d2d2d;
  border-radius: 6px;
  overflow: hidden; /* если нужны закруглённые края */
  box-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.status-table th,
.status-table td {
  padding: 0.75rem 1rem;
  border: 1px solid #444;
  text-align: center;
}

.status-table thead {
  background: #1f1f1f;
}

.status-table th {
  color: #ff4545; /* красный акцент */
  font-weight: 600;
}
</style>

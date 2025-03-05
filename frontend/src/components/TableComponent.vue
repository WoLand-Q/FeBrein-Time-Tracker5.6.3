<template>
  <div class="table-container">
    <!-- Поле поиска -->
    <div class="table-controls">
      <input
          v-model="searchQuery"
          type="text"
          class="search-input"
          placeholder="Пошук..."
      />
    </div>

    <!-- Скролл-обёртка -->
    <div class="scroll-wrapper">
      <table class="custom-table">
        <thead>
        <tr>
          <!-- Перебираем все столбцы -->
          <th
              v-for="(col, i) in columns"
              :key="i"
              class="table-header"
              :class="{ 'actions-header': col === 'Дії' }"
          >
            {{ col }}
          </th>
        </tr>
        </thead>

        <tbody>
        <!-- Если нет данных -->
        <tr v-if="filteredItems.length === 0">
          <td :colspan="columns.length" class="empty-row">
            Немає даних
          </td>
        </tr>

        <!-- Отображаем строки -->
        <tr
            v-for="(item, rowIndex) in paginatedItems"
            :key="item.id || rowIndex"
            :class="{ 'hovered-row': hoveredRow === rowIndex }"
            @mouseover="hoveredRow = rowIndex"
            @mouseleave="hoveredRow = null"
        >
          <!-- В каждой строке снова перебираем columns -->
          <td
              v-for="(col, colIndex) in columns"
              :key="colIndex"
              :class="col === 'Дії' ? 'td-actions' : ''"
          >
            <!-- Условно выводим данные в зависимости от названия колонки -->
            <template v-if="col === 'Дії'">
              <!-- Кнопки действий -->
              <button class="action-btn edit" @click="$emit('edit', item)">Редагувати</button>
              <button class="action-btn delete" @click="$emit('delete', item.id)">Видалити</button>

            </template>

            <!-- Пример для групп -->
            <template v-else-if="col === 'Назва групи'">
              {{ item.group_name }}
            </template>

            <!-- Пример для пользователей -->
            <template v-else-if="col === 'Ім’я'">
              {{ item.name }}
            </template>
            <template v-else-if="col === 'Логін'">
              {{ item.login }}
            </template>
            <template v-else-if="col === 'Роль'">
              {{ item.role_id === 1 ? 'Admin' : 'User' }}
            </template>
            <template v-else-if="col === 'Група'">
              {{ findGroupName(item.group_id) }}
            </template>

            <!-- Пример для таймлогов -->
            <template v-else-if="col === 'Користувач'">
              {{ findUserName(item.user_id) }}
            </template>
            <template v-else-if="col === 'Подія'">
              {{ mapEventToLabel(item.event_id) }}
            </template>
            <template v-else-if="col === 'Дата'">
              {{ formatDate(item.acted_at) }}
            </template>

            <!-- Fallback: если название колонки не совпало ни с одним из условий -->
            <template v-else>
              {{ item[col.toLowerCase()] }}
            </template>
          </td>
        </tr>
        </tbody>
      </table>
    </div>

    <!-- Пагинация -->
    <div v-if="showPagination" class="pagination-container">
      <button
          class="pagination-btn"
          :disabled="currentPage <= 1"
          @click="goToPage(currentPage - 1)"
      >
        «
      </button>
      <span>Сторінка {{ currentPage }} / {{ totalPages }}</span>
      <button
          class="pagination-btn"
          :disabled="currentPage >= totalPages"
          @click="goToPage(currentPage + 1)"
      >
        »
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: "TableComponent",
  props: {
    items: { type: Array, default: () => [] },
    columns: { type: Array, default: () => [] },
    tableType: { type: String, default: "" },
    users: { type: Array, default: () => [] },
    groups: { type: Array, default: () => [] },
    showPagination: { type: Boolean, default: false },
    pageSize: { type: Number, default: 10 },
  },
  data() {
    return {
      searchQuery: "",
      hoveredRow: null,
      currentPage: 1,
    };
  },
  computed: {
    filteredItems() {
      if (!this.searchQuery) return this.items;
      const q = this.searchQuery.toLowerCase();

      // Пример фильтра, если нужно опираться на tableType
      if (this.tableType === "users") {
        return this.items.filter((user) =>
            [user.name?.toLowerCase(), user.login?.toLowerCase()]
                .some(field => field?.includes(q))
        );
      } else if (this.tableType === "groups") {
        return this.items.filter((g) =>
            g.group_name?.toLowerCase().includes(q)
        );
      } else if (this.tableType === "timelogs") {
        return this.items.filter((t) => {
          const userName = this.findUserName(t.user_id).toLowerCase();
          const eventName = this.mapEventToLabel(t.event_id).toLowerCase();
          const dateStr = this.formatDate(t.acted_at).toLowerCase();
          return userName.includes(q) || eventName.includes(q) || dateStr.includes(q);
        });
      }

      // fallback универсальный
      return this.items.filter((item) =>
          Object.values(item).some((val) => String(val).toLowerCase().includes(q))
      );
    },
    totalPages() {
      if (!this.showPagination) return 1;
      return Math.ceil(this.filteredItems.length / this.pageSize);
    },
    paginatedItems() {
      if (!this.showPagination) return this.filteredItems;
      const startIndex = (this.currentPage - 1) * this.pageSize;
      return this.filteredItems.slice(startIndex, startIndex + this.pageSize);
    },
  },
  methods: {
    findUserName(userId) {
      const user = this.users.find(u => u.id === userId);
      return user ? user.name : "Невідомо";
    },
    findGroupName(groupId) {
      const group = this.groups.find(g => g.id === groupId);
      return group ? group.group_name : "Невідомо";
    },
    mapEventToLabel(eventId) {
      switch (eventId) {
        case 1: return "start";
        case 2: return "start_break";
        case 3: return "stop_break";
        case 4: return "stop";
        default: return "невідомо";
      }
    },
    formatDate(str) {
      if (!str) return "";
      const date = new Date(str); // Передаём строку напрямую
      if (isNaN(date.getTime())) {
        return "";
      }
      return date.toLocaleString("uk-UA", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
      });
    },

    goToPage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
      }
    },
  },
};
</script>

<style scoped>
.table-container {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

/* Поиск */
.table-controls {
  display: flex;
  justify-content: flex-end;
}
.search-input {
  background-color: #1f1f1f;
  color: #fff;
  border: 1px solid #333;
  padding: 0.4rem 0.75rem;
  border-radius: 4px;
  outline: none;
  width: 180px;
}
.search-input::placeholder {
  color: #888;
}

/* Скролл-обёртка */
.scroll-wrapper {
  max-height: 450px; /* можно менять при желании */
  overflow-y: auto;
  border: 1px solid #333;
  border-radius: 0.5rem;
}

/* Таблица */
.custom-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  min-width: 600px;
}

/* Липкая шапка */
.custom-table thead {
  position: sticky;
  top: 0;
  background-color: #2a2a2a;
  z-index: 1;
}
.table-header {
  text-align: left;
  padding: 0.5rem;
  font-weight: 600;
  color: #ff4545;
  border-bottom: 2px solid #444;
}
/* Выравниваем «Дії» вправо */
.actions-header {
  text-align: right;
}

/* Тело */
.custom-table tbody tr {
  transition: background-color 0.2s;
}
.custom-table tbody td {
  padding: 0.5rem;
  border-bottom: 1px solid #333;
  color: #fff;
}
/* Полосатость */
.custom-table tbody tr:nth-child(even) {
  background-color: #1f1f1f;
}
.custom-table tbody tr:nth-child(odd) {
  background-color: #242424;
}
/* Hover */
.hovered-row {
  background-color: #343434 !important;
}

/* Колонка с кнопками (Дії) */
.td-actions {
  white-space: nowrap;
  text-align: right; /* выравниваем кнопки по правому краю */
}
.action-btn {
  margin-left: 0.5rem;
  padding: 0.4rem 0.7rem;
  border: none;
  border-radius: 4px;
  font-size: 0.85rem;
  cursor: pointer;
  color: #fff;
  transition: background-color 0.2s;
}
.action-btn.edit {
  background-color: #6060f0;
}
.action-btn.edit:hover {
  background-color: #4c4cc7;
}
.action-btn.delete {
  background-color: #cc2f2f;
}
.action-btn.delete:hover {
  background-color: #a62626;
}

/* Если нет данных */
.empty-row {
  text-align: center;
  padding: 1rem;
  color: #999;
}

/* Пагинация */
.pagination-container {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
}
.pagination-btn {
  background-color: #2d2d2d;
  color: #fff;
  border: 1px solid #444;
  padding: 0.3rem 0.6rem;
  border-radius: 4px;
  cursor: pointer;
}
.pagination-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
</style>

<template>
  <div class="p-4 bg-gray-800 rounded">
    <p class="mb-4">
      <strong>Ваш статус:</strong>
      <span v-if="status === 'працює'" class="text-green-400">Працює</span>
      <span v-else-if="status === 'перерва'" class="text-yellow-400">Перерва</span>
      <span v-else-if="status === 'завершено'" class="text-red-400">Завершено</span>
      <span v-else>{{ status }}</span>
    </p>

    <div class="flex space-x-2">
      <!-- Почати роботу (event_id=1) -->
      <button
          class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white rounded"
          @click="showConfirmDialog('startWork', 'Ви впевнені, що хочете почати роботу?')"
          :disabled="!canStartWork"
      >
        Почати роботу
      </button>

      <!-- Почати перерву (event_id=2) -->
      <button
          class="px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-gray-800 rounded"
          @click="showConfirmDialog('startBreak', 'Ви впевнені, що хочете почати перерву?')"
          :disabled="!canStartBreak"
      >
        Почати перерву
      </button>

      <!-- Закінчити перерву (event_id=3) -->
      <button
          class="px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-gray-800 rounded"
          @click="showConfirmDialog('stopBreak', 'Ви впевнені, що хочете закінчити перерву?')"
          :disabled="!canStopBreak"
      >
        Закінчити перерву
      </button>

      <!-- Закінчити роботу (event_id=4) -->
      <button
          class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded"
          @click="showConfirmDialog('stopWork', 'Ви впевнені, що хочете закінчити роботу?')"
          :disabled="!canStopWork"
      >
        Закінчити роботу
      </button>
    </div>

    <!-- Модалка подтверждения -->
    <transition name="fade">
      <div
          v-if="confirmDialog.visible"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
      >
        <div class="bg-gray-800 text-white p-6 rounded shadow-lg w-full max-w-md">
          <h2 class="text-xl font-semibold mb-4">Підтвердження дії</h2>
          <p class="mb-6">{{ confirmDialog.message }}</p>
          <div class="flex justify-end space-x-2">
            <button
                class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded"
                @click="onCancel"
            >
              Відміна
            </button>
            <button
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded"
                @click="onConfirm"
            >
              Підтвердити
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
export default {
  name: "Timer",
  props: {
    currentStatus: {
      type: String,
      default: "завершено", // "працює", "перерва", "завершено"
    },
  },
  data() {
    return {
      // Настройки для локальной модалки подтверждения
      confirmDialog: {
        visible: false,
        message: "",
        action: null, // 'startWork' | 'startBreak' | 'stopBreak' | 'stopWork'
      },
    };
  },
  computed: {
    status() {
      return this.currentStatus;
    },
    // --- Логика блокировок ---
    canStartWork() {
      return this.status === "завершено";
    },
    canStartBreak() {
      return this.status === "працює";
    },
    canStopBreak() {
      return this.status === "перерва";
    },
    canStopWork() {
      // Если "завершено", то уже закончено
      return this.status !== "завершено";
    },
  },
  methods: {
    // Получаем локальное время в формате "YYYY-MM-DD HH:mm:ss"
    getLocalDateTimeString() {
      const now = new Date();
      const yyyy = now.getFullYear();
      const mm = String(now.getMonth() + 1).padStart(2, "0");
      const dd = String(now.getDate()).padStart(2, "0");
      const hh = String(now.getHours()).padStart(2, "0");
      const min = String(now.getMinutes()).padStart(2, "0");
      const sec = String(now.getSeconds()).padStart(2, "0");
      return `${yyyy}-${mm}-${dd} ${hh}:${min}:${sec}`;
    },

    // Показываем диалог
    showConfirmDialog(actionName, msg) {
      this.confirmDialog.action = actionName;
      this.confirmDialog.message = msg;
      this.confirmDialog.visible = true;
    },

    // Нажали "Подтвердить"
    onConfirm() {
      const dateTime = this.getLocalDateTimeString();
      const action = this.confirmDialog.action;

      this.confirmDialog.visible = false;
      this.confirmDialog.message = "";
      this.confirmDialog.action = null;

      // Вызываем нужный метод
      if (action === "startWork") {
        this.$emit("startWork", dateTime);
      } else if (action === "startBreak") {
        this.$emit("startBreak", dateTime);
      } else if (action === "stopBreak") {
        this.$emit("stopBreak", dateTime);
      } else if (action === "stopWork") {
        this.$emit("stopWork", dateTime);
      }
    },

    // Нажали "Отмена"
    onCancel() {
      this.confirmDialog.visible = false;
      this.confirmDialog.message = "";
      this.confirmDialog.action = null;
    },
  },
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter, .fade-leave-to {
  opacity: 0;
}
</style>

<template>
  <div class="timer-card">
    <!-- Верхняя часть: иконка + статус -->
    <div class="timer-header">
      <div :class="['timer-icon', iconColorClass]">
        <i class="fas fa-clock"></i>
      </div>

      <div class="timer-info">
        <h3 class="timer-status">
          Ваш статус:
          <span :class="statusColorClass">
            {{ statusLabel }}
          </span>
        </h3>

        <!-- Если есть startTime, показываем -->
        <p v-if="startTime" class="start-time">
          Активно с: {{ startTime }}
        </p>
      </div>
    </div>

    <!-- Кнопки -->
    <div class="timer-actions">
      <!-- Кнопка "Почати роботу" -->
      <button
          v-if="canStartWork"
          class="btn btn-green"
          @click="onStartWorkClick"
      >
        Почати роботу
      </button>

      <!-- Кнопка "Почати перерву" -->
      <button
          v-if="canStartBreak"
          class="btn btn-yellow"
          @click="showConfirmDialog('startBreak', 'Почати перерву зараз?')"
      >
        Почати перерву
      </button>

      <!-- Кнопка "Закінчити перерву" -->
      <button
          v-if="canStopBreak"
          class="btn btn-yellow"
          @click="showConfirmDialog('stopBreak', 'Закінчити перерву?')"
      >
        Закінчити перерву
      </button>

      <!-- Кнопка "Закінчити роботу" -->
      <button
          v-if="canStopWork"
          class="btn btn-red"
          @click="showConfirmDialog('stopWork', 'Закінчити роботу?')"
      >
        Закінчити роботу
      </button>
    </div>

    <!-- Модальное окно подтверждения -->
    <transition name="fade">
      <div v-if="confirmDialog.visible" class="confirm-overlay">
        <div class="confirm-dialog">
          <h2 class="dialog-title">Підтвердження</h2>
          <p class="dialog-message">{{ confirmDialog.message }}</p>
          <div class="dialog-buttons">
            <button class="btn btn-secondary" @click="onCancel">
              Відміна
            </button>
            <button class="btn btn-primary" @click="onConfirm">
              Підтвердити
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Диалог "уже закончил день" -->
    <transition name="fade">
      <div v-if="cannotStartDialog" class="confirm-overlay">
        <div class="confirm-dialog">
          <h2 class="dialog-title">Увага</h2>
          <p class="dialog-message">
            Ви вже закінчили роботу сьогодні — почати її знову неможливо.
          </p>
          <div class="dialog-buttons">
            <button class="btn btn-primary" @click="cannotStartDialog = false">
              Закрити
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
      default: "завершено", // "працює" | "перерва" | "завершено"
    },
    startTime: {
      type: String,
      default: null,
    },
    // Признак, что пользователь "закрыл день" (последний event_id=4)
    // Нельзя снова начать работу
    dayClosed: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      confirmDialog: {
        visible: false,
        message: "",
        action: null,
      },
      cannotStartDialog: false,
    };
  },
  computed: {
    // Текстовая метка статуса
    statusLabel() {
      switch (this.currentStatus) {
        case "працює":  return "Працює";
        case "перерва": return "Перерва";
        default:        return "Завершено";
      }
    },

    // Цвет иконки
    iconColorClass() {
      switch (this.currentStatus) {
        case "працює":  return "icon-work";
        case "перерва": return "icon-break";
        default:        return "icon-stopped";
      }
    },

    // Цвет текста статуса
    statusColorClass() {
      switch (this.currentStatus) {
        case "працює":  return "status-work";
        case "перерва": return "status-break";
        default:        return "status-stopped";
      }
    },

    // Логика кнопок
    // Если dayClosed=true, всё отключаем
    canStartWork() {
      if (this.dayClosed) return false;
      return this.currentStatus === "завершено";
    },
    canStartBreak() {
      if (this.dayClosed) return false;
      return this.currentStatus === "працює";
    },
    canStopBreak() {
      if (this.dayClosed) return false;
      return this.currentStatus === "перерва";
    },
    canStopWork() {
      if (this.dayClosed) return false;
      return this.currentStatus === "працює" || this.currentStatus === "перерва";
    },
  },
  methods: {
    // Нажали "Почати роботу"
    onStartWorkClick() {
      if (this.dayClosed) {
        // Показываем диалог, что нельзя
        this.cannotStartDialog = true;
      } else {
        // Обычный сценарий — подтверждение
        this.showConfirmDialog("startWork", "Почати роботу зараз?");
      }
    },

    showConfirmDialog(action, msg) {
      this.confirmDialog.action = action;
      this.confirmDialog.message = msg;
      this.confirmDialog.visible = true;
    },
    onConfirm() {
      const action = this.confirmDialog.action;
      this.confirmDialog.visible = false;
      // Просто отправляем event
      this.$emit(action);
    },
    onCancel() {
      this.confirmDialog.visible = false;
    },
  },
};
</script>

<style scoped>
.timer-card {
  background: #2d2d2d;
  border-radius: 8px;
  padding: 1.5rem;
  color: #fff;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
  position: relative;
}

/* Шапка */
.timer-header {
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
}

/* Иконка */
.timer-icon {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 1rem;
  font-size: 1.5rem;
  background: #444;
  color: #fff;
}

.icon-work {
  background: #2e7d32; /* тёмно-зелёный */
}

.icon-break {
  background: #ffb300; /* тёмно-жёлтый */
  color: #333;
}

.icon-stopped {
  background: #b71c1c; /* тёмно-красный */
}

/* Инфа о таймере */
.timer-info {
  flex: 1;
}

.timer-status {
  margin: 0;
  font-size: 1.25rem;
  color: #ffcf33;
}

.status-work {
  color: #4caf50; /* зелёный */
}

.status-break {
  color: #ffc107; /* жёлтый */
}

.status-stopped {
  color: #ff4545; /* красный */
}

.start-time {
  margin: 0.25rem 0 0;
  color: #ccc;
  font-size: 0.9rem;
}

/* Кнопки */
.timer-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: 1rem;
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
  border: none;
  outline: none;
}

.btn-green {
  background: #28a745;
  color: #fff;
}

.btn-green:hover {
  background: #218838;
}

.btn-yellow {
  background: #ffc107;
  color: #212529;
}

.btn-yellow:hover {
  background: #e0a800;
}

.btn-red {
  background: #dc3545;
  color: #fff;
}

.btn-red:hover {
  background: #c82333;
}

/* Модальные окна */
.confirm-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 999;
  display: flex;
  align-items: center;
  justify-content: center;
}

.confirm-dialog {
  background: #2d2d2d;
  padding: 1.5rem;
  border-radius: 8px;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
}

.dialog-title {
  font-size: 1.2rem;
  margin-bottom: 0.75rem;
  color: #ff4545;
}

.dialog-message {
  margin-bottom: 1rem;
}

.dialog-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
}

.btn-secondary {
  background: #6c757d;
  color: #fff;
}

.btn-secondary:hover {
  background: #5a6268;
}

.btn-primary {
  background: #007bff;
  color: #fff;
}

.btn-primary:hover {
  background: #0069d9;
}

/* Анимация */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter, .fade-leave-to {
  opacity: 0;
}
</style>

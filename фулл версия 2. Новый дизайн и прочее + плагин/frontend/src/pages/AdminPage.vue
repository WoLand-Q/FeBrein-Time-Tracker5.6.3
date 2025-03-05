<template>
  <div class="space-y-6 fade-in">
    <!-- Alert Notification -->
    <Alert
        :type="alert.type"
        :title="alert.title"
        :message="alert.message"
        :show="alert.show"
    />

    <!-- Користувачі -->
    <section class="panel-container">
      <div class="panel-header">
        <h2 class="panel-title">Керування користувачами</h2>
        <!-- Кнопка «Додати користувача» -->
        <BaseButton variant="primary" @click="openUserModal">
          <i class="fas fa-plus mr-2"></i>
          Додати користувача
        </BaseButton>
      </div>

      <!-- Таблица пользователей -->
      <TableComponent
          :items="users"
          :columns="['Ім’я', 'Логін', 'Роль', 'Група', 'Дії']"
          tableType="users"
          :users="users"
          :groups="groups"
          @edit="editUser"
          @delete="deleteUserConfirm"
          :showPagination="true"
          :pageSize="8"
      />
    </section>

    <!-- Групи -->
    <section class="panel-container">
      <div class="panel-header">
        <h2 class="panel-title">Керування групами</h2>
        <BaseButton variant="secondary" @click="openGroupModal">
          <i class="fas fa-plus mr-2"></i>
          Додати групу
        </BaseButton>
      </div>

      <!-- Таблица групп -->
      <TableComponent
          :items="groups"
          :columns="['Назва групи', 'Дії']"
          tableType="groups"
          @edit="editGroup"
          @delete="deleteGroupConfirm"
          :showPagination="true"
          :pageSize="5"
      />
    </section>

    <!-- Таймлоги -->
    <section class="panel-container">
      <div class="panel-header">
        <h2 class="panel-title">Керування таймлогами</h2>
        <BaseButton variant="warning" @click="openTimeLogModal">
          <i class="fas fa-plus mr-2"></i>
          Додати таймлог
        </BaseButton>
      </div>

      <!-- Таблица таймлогов -->
      <TableComponent
          :items="timeLogs"
          :columns="['Користувач', 'Подія', 'Дата', 'Дії']"
          tableType="timelogs"
          :users="users"
          @edit="editTimeLog"
          @delete="deleteTimeLogConfirm"
          :showPagination="true"
          :pageSize="10"
      />
    </section>

    <!-- Users Modal -->
    <Modal v-if="showUserModal" :isVisible="showUserModal" @close="closeUserModal">
      <template #title>
        <span class="text-xl font-semibold">
          {{ isEditingUser ? "Редагувати користувача" : "Додати користувача" }}
        </span>
      </template>
      <template #body>
        <UserForm
            :formData="userForm"
            :groups="groups"
            @submit="saveUser"
            @reset="resetUserForm"
        />
      </template>
      <template #actions>
        <BaseButton type="button" variant="danger" @click="closeUserModal">
          Скасувати
        </BaseButton>
      </template>
    </Modal>

    <!-- Groups Modal -->
    <Modal v-if="showGroupModal" :isVisible="showGroupModal" @close="closeGroupModal">
      <template #title>
        <span class="text-xl font-semibold">
          {{ isEditingGroup ? "Редагувати групу" : "Додати групу" }}
        </span>
      </template>
      <template #body>
        <GroupForm
            :formData="groupForm"
            @submit="saveGroup"
            @reset="resetGroupForm"
        />
      </template>
      <template #actions>
        <BaseButton type="button" variant="danger" @click="closeGroupModal">
          Скасувати
        </BaseButton>
      </template>
    </Modal>

    <!-- Time Logs Modal -->
    <Modal v-if="showTimeLogModal" :isVisible="showTimeLogModal" @close="closeTimeLogModal">
      <template #title>
        <span class="text-xl font-semibold">
          {{ currentTimeLog ? "Редагувати таймлог" : "Додати таймлог" }}
        </span>
      </template>
      <template #body>
        <TimeLogForm
            :formData="timeLogForm"
            :users="users"
            @submit="saveTimeLog"
            @reset="resetTimeLogForm"
        />
      </template>
      <template #actions>
        <BaseButton type="button" variant="danger" @click="closeTimeLogModal">
          Скасувати
        </BaseButton>
      </template>
    </Modal>

    <!-- Confirm Dialog -->
    <ConfirmDialog
        :visible="confirmDialog.visible"
        :title="confirmDialog.title"
        :message="confirmDialog.message"
        @confirm="confirmDialog.onConfirm"
        @cancel="confirmDialog.onCancel"
    />
  </div>
</template>

<script>
import api from "@/api";
import Modal from "@/components/Modal.vue";
import TableComponent from "@/components/TableComponent.vue";
import UserForm from "@/components/UserForm.vue";
import GroupForm from "@/components/GroupForm.vue";
import TimeLogForm from "@/components/TimeLogForm.vue";
import BaseButton from "@/components/BaseButton.vue";
import Alert from "@/components/Alert.vue";
import ConfirmDialog from "@/components/ConfirmDialog.vue";

export default {
  name: "AdminPanel",
  components: {
    Modal,
    TableComponent,
    UserForm,
    GroupForm,
    TimeLogForm,
    BaseButton,
    Alert,
    ConfirmDialog,
  },
  data() {
    return {
      // Основные данные
      users: [],
      groups: [],
      timeLogs: [],

      // Видимость модалок
      showUserModal: false,
      showGroupModal: false,
      showTimeLogModal: false,

      // Флаги "редактирования"
      isEditingUser: false,
      isEditingGroup: false,

      // Текущие редактируемые объекты
      currentUser: null,
      currentGroup: null,
      currentTimeLog: null,

      // Формы
      userForm: {
        id: null,
        name: "",
        login: "",
        password: "",
        role: "user",
        group_id: null,
      },
      groupForm: {
        id: null,
        group_name: "",
      },
      timeLogForm: {
        id: null,
        user_id: "",
        event_id: "",
        acted_at: "",
      },

      // Флаги, предотвращающие повторные запросы
      isUserSaving: false,
      isGroupSaving: false,
      isTimeLogSaving: false,

      // Alert
      alert: {
        show: false,
        type: "success",
        title: "",
        message: "",
      },

      // Confirm dialog
      confirmDialog: {
        visible: false,
        title: "",
        message: "",
        onConfirm: null,
        onCancel: null,
      },
    };
  },
  methods: {
    async fetchData() {
      try {
        const [usersRes, groupsRes, timeLogsRes] = await Promise.all([
          api.get("/api/admin/users"),
          api.get("/api/admin/groups"),
          api.get("/api/admin/userTimeLogs"),
        ]);
        this.users = usersRes.data.data || usersRes.data;
        this.groups = groupsRes.data.data || groupsRes.data;
        this.timeLogs = timeLogsRes.data.data || timeLogsRes.data;
      } catch (error) {
        console.error("Помилка завантаження даних:", error);
        this.triggerAlert("error", "Помилка", "Не вдалося завантажити дані.");
      }
    },

    // ===== Users =====
    openUserModal(user = null) {
      if (this.showUserModal) return;
      this.isEditingUser = !!user;
      this.currentUser = user;
      if (user) {
        // Editing
        this.userForm = {
          id: user.id,
          name: user.name || "",
          login: user.login || "",
          password: "",
          role: user.role_id === 1 ? "admin" : "user",
          group_id: user.group_id || null,
        };
      } else {
        // New
        this.userForm = {
          id: null,
          name: "",
          login: "",
          password: "",
          role: "user",
          group_id: null,
        };
      }
      this.showUserModal = true;
    },
    closeUserModal() {
      this.showUserModal = false;
      this.resetUserForm();
    },
    resetUserForm() {
      this.isEditingUser = false;
      this.currentUser = null;
      this.userForm = {
        id: null,
        name: "",
        login: "",
        password: "",
        role: "user",
        group_id: null,
      };
    },
    async saveUser(payload) {
      if (this.isUserSaving) return;
      this.isUserSaving = true;
      try {
        const { onDone, ...data } = payload;
        // Преобразуем role -> role_id
        const roleMap = { admin: 1, user: 2 };
        data.role_id = roleMap[data.role] || 2;
        delete data.role;

        // Пароль пустой — не отправляем
        if (!data.password) delete data.password;

        if (this.isEditingUser && data.id) {
          await api.put(`/api/admin/users/${data.id}`, data);
          this.triggerAlert("success", "Успіх", "Користувача оновлено.");
        } else {
          await api.post("/api/admin/users", data);
          this.triggerAlert("success", "Успіх", "Користувача додано.");
        }
        await this.fetchData();
        this.closeUserModal();
      } catch (err) {
        console.error("Помилка збереження користувача:", err);
        this.triggerAlert("error", "Помилка", "Не вдалося зберегти користувача.");
      } finally {
        this.isUserSaving = false;
        if (payload.onDone) payload.onDone();
      }
    },
    editUser(user) {
      this.openUserModal(user);
    },
    deleteUserConfirm(userId) {
      this.openConfirmDialog(
          "Видалити користувача?",
          "Ви впевнені, що хочете видалити цього користувача?",
          async () => {
            try {
              await api.delete(`/api/admin/users/${userId}`);
              this.triggerAlert("success", "Успіх", "Користувача видалено.");
              this.fetchData();
            } catch (err) {
              console.error("Помилка видалення користувача:", err);
              this.triggerAlert("error", "Помилка", "Не вдалося видалити користувача.");
            }
          }
      );
    },

    // ===== Groups =====
    openGroupModal(group = null) {
      if (this.showGroupModal) return;
      this.isEditingGroup = !!group;
      this.currentGroup = group;
      if (group) {
        this.groupForm = { id: group.id, group_name: group.group_name || "" };
      } else {
        this.groupForm = { id: null, group_name: "" };
      }
      this.showGroupModal = true;
    },
    closeGroupModal() {
      this.showGroupModal = false;
      this.resetGroupForm();
    },
    resetGroupForm() {
      this.isEditingGroup = false;
      this.currentGroup = null;
      this.groupForm = { id: null, group_name: "" };
    },
    async saveGroup(formData) {
      if (this.isGroupSaving) return;
      this.isGroupSaving = true;
      try {
        if (this.isEditingGroup && formData.id) {
          await api.put(`/api/admin/groups/${formData.id}`, formData);
          this.triggerAlert("success", "Успіх", "Групу оновлено.");
        } else {
          await api.post("/api/admin/groups", formData);
          this.triggerAlert("success", "Успіх", "Групу додано.");
        }
        await this.fetchData();
        this.closeGroupModal();
      } catch (err) {
        console.error("Помилка збереження групи:", err);
        this.triggerAlert("error", "Помилка", "Не вдалося зберегти групу.");
      } finally {
        this.isGroupSaving = false;
      }
    },
    editGroup(group) {
      this.openGroupModal(group);
    },
    deleteGroupConfirm(groupId) {
      this.openConfirmDialog(
          "Видалити групу?",
          "Ви впевнені, що хочете видалити цю групу?",
          async () => {
            try {
              await api.delete(`/api/admin/groups/${groupId}`);
              this.triggerAlert("success", "Успіх", "Групу видалено.");
              this.fetchData();
            } catch (err) {
              console.error("Помилка видалення групи:", err);
              this.triggerAlert("error", "Помилка", "Не вдалося видалити групу.");
            }
          }
      );
    },

    // ===== Time Logs =====
    openTimeLogModal(timeLog = null) {
      if (this.showTimeLogModal) return;
      this.currentTimeLog = timeLog;
      if (timeLog) {
        // Редактирование
        this.timeLogForm = { ...timeLog };
      } else {
        // Создание
        this.timeLogForm = { id: null, user_id: "", event_id: "", acted_at: "" };
      }
      this.showTimeLogModal = true;
    },
    closeTimeLogModal() {
      this.showTimeLogModal = false;
      this.resetTimeLogForm();
    },
    resetTimeLogForm() {
      this.currentTimeLog = null;
      this.timeLogForm = { id: null, user_id: "", event_id: "", acted_at: "" };
    },
    async saveTimeLog(formData) {
      if (this.isTimeLogSaving) return;
      this.isTimeLogSaving = true;
      try {
        if (formData.id) {
          await api.put(`/api/admin/userTimeLogs/${formData.id}`, formData);
          this.triggerAlert("success", "Успіх", "Таймлог оновлено.");
        } else {
          await api.post("/api/admin/userTimeLogs", formData);
          this.triggerAlert("success", "Успіх", "Таймлог додано.");
        }
        await this.fetchData();
        this.closeTimeLogModal();
      } catch (error) {
        console.error("Помилка збереження таймлога:", error);
        this.triggerAlert("error", "Помилка", "Не вдалося зберегти таймлог.");
        if (error.response && error.response.data.error === "Already Exist") {
          this.triggerAlert("error", "Помилка", "Такий таймлог уже існує.");
        }
      } finally {
        this.isTimeLogSaving = false;
      }
    },
    editTimeLog(timeLog) {
      this.openTimeLogModal(timeLog);
    },
    deleteTimeLogConfirm(timeLogId) {
      this.openConfirmDialog(
          "Видалити таймлог?",
          "Ви впевнені, що хочете видалити цей таймлог?",
          async () => {
            try {
              await api.delete(`/api/admin/userTimeLogs/${timeLogId}`);
              this.triggerAlert("success", "Успіх", "Таймлог видалено.");
              this.fetchData();
            } catch (err) {
              console.error("Помилка видалення таймлога:", err);
              this.triggerAlert("error", "Помилка", "Не вдалося видалити таймлог.");
            }
          }
      );
    },

    // ===== ALERT =====
    triggerAlert(type, title, message) {
      this.alert = { show: true, type, title, message };
      setTimeout(() => {
        this.alert.show = false;
      }, 3000);
    },

    // ===== Confirm Dialog =====
    openConfirmDialog(title, message, onConfirm) {
      this.confirmDialog = {
        visible: true,
        title,
        message,
        onConfirm: async () => {
          this.confirmDialog.visible = false;
          if (onConfirm) await onConfirm();
        },
        onCancel: () => {
          this.confirmDialog.visible = false;
        },
      };
    },
  },
  mounted() {
    this.fetchData();
  },
};
</script>

<style scoped>
.fade-in {
  animation: fadeInUp 0.6s ease forwards;
}
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Убираем blur и hover-трансформации */
.panel-container {
  background-color: rgba(255, 255, 255, 0.05);
  border-radius: 1rem;
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  /* Если не нужна тень, можно выпилить:
     box-shadow: none;
  */
}
.panel-container:hover {
  /* Ничего не делаем при hover */
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.panel-title {
  font-size: 1.75rem;
  font-weight: 600;
  color: #fff;
}
</style>

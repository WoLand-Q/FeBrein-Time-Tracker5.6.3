<template>
  <div class="space-y-6 fade-in">
    <!-- Alert Notification -->
    <Alert
        :type="alert.type"
        :title="alert.title"
        :message="alert.message"
        :show="alert.show"
    />

    <!-- Users Management -->
    <section class="panel-container hover-card">
      <div class="panel-header">
        <h2 class="panel-title">Manage Users</h2>
        <BaseButton variant="primary" @click="openUserModal">
          <i class="fas fa-plus mr-2"></i> Add User
        </BaseButton>
      </div>
      <TableComponent
          :items="users"
          :columns="['Name', 'Login', 'Role', 'Group', 'Actions']"
          tableType="users"
          :users="users"
          :groups="groups"
          @edit="editUser"
          @delete="deleteUserConfirm"
      />
    </section>

    <!-- Groups Management -->
    <section class="panel-container hover-card">
      <div class="panel-header">
        <h2 class="panel-title">Manage Groups</h2>
        <BaseButton variant="secondary" @click="openGroupModal">
          <i class="fas fa-plus mr-2"></i> Add Group
        </BaseButton>
      </div>
      <TableComponent
          :items="groups"
          :columns="['Name', 'Actions']"
          tableType="groups"
          @edit="editGroup"
          @delete="deleteGroupConfirm"
      />
    </section>

    <!-- Time Logs Management -->
    <section class="panel-container hover-card">
      <div class="panel-header">
        <h2 class="panel-title">Manage Time Logs</h2>
        <BaseButton variant="warning" @click="openTimeLogModal">
          <i class="fas fa-plus mr-2"></i> Add Time Log
        </BaseButton>
      </div>
      <TableComponent
          :items="timeLogs"
          :columns="['User', 'Event', 'Date', 'Actions']"
          tableType="timelogs"
          :users="users"
          @edit="editTimeLog"
          @delete="deleteTimeLogConfirm"
      />
    </section>

    <!-- Users Modal -->
    <Modal v-if="showUserModal" :isVisible="showUserModal" @close="closeUserModal">
      <template #title>
        <span class="text-xl font-semibold">
          {{ isEditingUser ? 'Edit User' : 'Add User' }}
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
        <BaseButton type="button" variant="danger" @click="closeUserModal">Cancel</BaseButton>
      </template>
    </Modal>

    <!-- Groups Modal -->
    <Modal v-if="showGroupModal" :isVisible="showGroupModal" @close="closeGroupModal">
      <template #title>
        <span class="text-xl font-semibold">
          {{ isEditingGroup ? 'Edit Group' : 'Add Group' }}
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
        <BaseButton type="button" variant="danger" @click="closeGroupModal">Cancel</BaseButton>
      </template>
    </Modal>

    <!-- Time Logs Modal -->
    <Modal v-if="showTimeLogModal" :isVisible="showTimeLogModal" @close="closeTimeLogModal">
      <template #title>
        <span class="text-xl font-semibold">
          {{ currentTimeLog ? 'Edit Time Log' : 'Add Time Log' }}
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
        <BaseButton type="button" variant="danger" @click="closeTimeLogModal">Cancel</BaseButton>
      </template>
    </Modal>

    <!-- Custom Confirm Dialog -->
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

      // Окно подтверждения
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
    // Загрузка данных
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
        console.error("Ошибка загрузки данных:", error);
        this.triggerAlert("error", "Ошибка", "Не удалось загрузить данные.");
      }
    },

    // ===== USERS =====
    openUserModal(user = null) {
      if (this.showUserModal) return; // предотвращаем повторное открытие
      this.isEditingUser = !!user;
      this.currentUser = user;
      if (user) {
        // Редактирование
        this.userForm = {
          id: user.id,
          name: user.name || "",
          login: user.login || "",
          password: "",
          role: user.role_id === 1 ? "admin" : "user",
          group_id: user.group_id || null,
        };
      } else {
        // Создание
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
      if (this.isUserSaving) {
        console.log("saveUser prevented double call");
        if (payload.onDone) payload.onDone();
        return;
      }
      this.isUserSaving = true;
      try {
        const { onDone, ...data } = payload;
        // role -> role_id
        const roleMap = { admin: 1, user: 2 };
        data.role_id = roleMap[data.role] || 2;
        delete data.role;

        if (!data.password) {
          delete data.password;
        }

        if (this.isEditingUser && data.id) {
          // PUT
          await api.put(`/api/admin/users/${data.id}`, data);
          this.triggerAlert("success", "Успешно", "Пользователь обновлен.");
        } else {
          // POST
          await api.post("/api/admin/users", data);
          this.triggerAlert("success", "Успешно", "Пользователь добавлен.");
        }
        // Обновляем
        await this.fetchData();
        this.closeUserModal();
      } catch (err) {
        console.error("Ошибка сохранения пользователя:", err);
        this.triggerAlert("error", "Ошибка", "Не удалось сохранить пользователя.");
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
          "Delete User?",
          "Вы уверены, что хотите удалить пользователя?",
          async () => {
            try {
              await api.delete(`/api/admin/users/${userId}`);
              this.triggerAlert("success", "Успешно", "Пользователь удалён.");
              this.fetchData();
            } catch (err) {
              console.error("Ошибка удаления пользователя:", err);
              this.triggerAlert("error", "Ошибка", "Не удалось удалить пользователя.");
            }
          }
      );
    },

    // ===== GROUPS =====
    openGroupModal(group = null) {
      if (this.showGroupModal) return; // предотвращаем повторное открытие
      this.isEditingGroup = !!group;
      this.currentGroup = group;
      if (group) {
        this.groupForm = {
          id: group.id,
          group_name: group.group_name || "",
        };
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
      if (this.isGroupSaving) {
        console.log("saveGroup prevented double call");
        return;
      }
      this.isGroupSaving = true;
      try {
        if (this.isEditingGroup && formData.id) {
          await api.put(`/api/admin/groups/${formData.id}`, formData);
          this.triggerAlert("success", "Успешно", "Группа обновлена.");
        } else {
          await api.post("/api/admin/groups", formData);
          this.triggerAlert("success", "Успешно", "Группа добавлена.");
        }
        await this.fetchData();
        this.closeGroupModal();
      } catch (error) {
        console.error("Ошибка сохранения группы:", error);
        this.triggerAlert("error", "Ошибка", "Не удалось сохранить группу.");
      } finally {
        this.isGroupSaving = false;
      }
    },
    editGroup(group) {
      this.openGroupModal(group);
    },
    deleteGroupConfirm(groupId) {
      this.openConfirmDialog(
          "Delete Group?",
          "Вы уверены, что хотите удалить группу?",
          async () => {
            try {
              await api.delete(`/api/admin/groups/${groupId}`);
              this.triggerAlert("success", "Успешно", "Группа удалена.");
              this.fetchData();
            } catch (err) {
              console.error("Ошибка удаления группы:", err);
              this.triggerAlert("error", "Ошибка", "Не удалось удалить группу.");
            }
          }
      );
    },

    // ===== TIME LOGS =====
    openTimeLogModal(timeLog = null) {
      if (this.showTimeLogModal) return; // предотвращаем повторное открытие
      this.currentTimeLog = timeLog;
      if (timeLog) {
        this.timeLogForm = {...timeLog};
      } else {
        this.timeLogForm = {id: null, user_id: "", event_id: "", acted_at: ""};
      }
      this.showTimeLogModal = true;
    },
    closeTimeLogModal() {
      this.showTimeLogModal = false;
      this.resetTimeLogForm();
    },
    resetTimeLogForm() {
      this.currentTimeLog = null;
      this.timeLogForm = {id: null, user_id: "", event_id: "", acted_at: ""};
    },
    async saveTimeLog(formData) {
      if (this.isTimeLogSaving) {
        console.log("saveTimeLog prevented double call");
        return;
      }
      this.isTimeLogSaving = true;
      try {
        if (formData.id) {
          await api.put(`/api/admin/userTimeLogs/${formData.id}`, formData);
          this.triggerAlert("success", "Успешно", "Таймлог обновлен.");
        } else {
          await api.post("/api/admin/userTimeLogs", formData);
          this.triggerAlert("success", "Таймлог добавлен.", "");
        }
        await this.fetchData();
        this.closeTimeLogModal();
      } catch (error) {
        console.error("Ошибка сохранения таймлога:", error);
        this.triggerAlert("error", "Ошибка", "Не удалось сохранить таймлог.");
      } finally {
        this.isTimeLogSaving = false;
      }
    },
    editTimeLog(timeLog) {
      this.openTimeLogModal(timeLog);
    },
    deleteTimeLogConfirm(timeLogId) {
      this.openConfirmDialog(
          "Delete TimeLog?",
          "Вы уверены, что хотите удалить этот таймлог?",
          async () => {
            try {
              await api.delete(`/api/admin/userTimeLogs/${timeLogId}`);
              this.triggerAlert("success", "Успешно", "Таймлог удалён.");
              this.fetchData();
            } catch (err) {
              console.error("Ошибка удаления таймлога:", err);
              this.triggerAlert("error", "Ошибка", "Не удалось удалить таймлог.");
            }
          }
      );
    },

    // ===== ALERT =====
    triggerAlert(type, title, message) {
      this.alert = {show: true, type, title, message};
      setTimeout(() => {
        this.alert.show = false;
      }, 3000);
    },

    // ===== CONFIRM DIALOG =====
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

.panel-container {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(8px);
  border-radius: 1rem;
  padding: 1.5rem;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  margin-bottom: 2rem;
}

.hover-card:hover {
  transform: translateY(-3px) scale(1.01);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
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

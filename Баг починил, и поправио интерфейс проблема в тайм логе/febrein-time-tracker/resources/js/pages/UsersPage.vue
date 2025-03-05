<template>
    <div class="users-page fade-in">
        <h2 class="page-title">Users Management</h2>

        <!-- Карточка для добавления пользователя -->
        <div class="card">
            <div class="card-header">Create User</div>
            <div class="card-body form-row">
                <input
                    type="text"
                    v-model="newUser.name"
                    placeholder="Name"
                    class="input-text"
                />
                <input
                    type="text"
                    v-model="newUser.login"
                    placeholder="Login"
                    class="input-text"
                />
                <input
                    type="password"
                    v-model="newUser.password"
                    placeholder="Password"
                    class="input-text"
                />
                <select v-model="newUser.role_id" class="input-select">
                    <option value="1">Admin</option>
                    <option value="2">User</option>
                </select>
                <input
                    type="number"
                    v-model="newUser.group_id"
                    placeholder="Group ID"
                    class="input-text"
                />
                <button class="btn btn-success" @click="createUser">Create User</button>
            </div>
        </div>

        <!-- Список пользователей -->
        <div class="card users-list-card">
            <div class="card-header">Existing Users</div>
            <div class="card-body">
                <ul class="user-list">
                    <li
                        v-for="user in users"
                        :key="user.id"
                        class="user-item fade-in-up"
                    >
            <span class="user-label">
              <strong>{{ user.name }}</strong>
              (role: {{ user.role_name }}, group: {{ user.group_id }})
            </span>
                        <div class="item-actions">
                            <button class="btn btn-edit" @click="editUser(user)">Edit</button>
                            <button class="btn btn-delete" @click="deleteUser(user.id)">
                                Delete
                            </button>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Модальное окно редактирования -->
        <div v-if="editModalVisible" class="modal-backdrop">
            <div class="modal-window bounce-in">
                <h3>Edit User</h3>
                <input
                    type="text"
                    v-model="editUserData.name"
                    placeholder="Name"
                    class="input-text"
                />
                <input
                    type="text"
                    v-model="editUserData.login"
                    placeholder="Login"
                    class="input-text"
                />
                <input
                    type="password"
                    v-model="editUserData.password"
                    placeholder="Password"
                    class="input-text"
                />
                <select v-model="editUserData.role_id" class="input-select">
                    <option value="1">Admin</option>
                    <option value="2">User</option>
                </select>
                <input
                    type="number"
                    v-model="editUserData.group_id"
                    placeholder="Group ID"
                    class="input-text"
                />

                <div class="modal-actions">
                    <button class="btn btn-success" @click="updateUser">Save</button>
                    <button class="btn btn-gray" @click="closeEditModal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    name: "UsersPage",
    data() {
        return {
            users: [],
            newUser: {
                name: "",
                login: "",
                password: "",
                role_id: 2,
                group_id: 1,
            },
            editModalVisible: false,
            editUserId: null,
            editUserData: {
                name: "",
                login: "",
                password: "",
                role_id: 2,
                group_id: 1,
            },
        };
    },
    mounted() {
        this.loadUsers();
    },
    methods: {
        async loadUsers() {
            try {
                const response = await axios.get("/api/admin/users");
                this.users = response.data.data;
            } catch (error) {
                console.error(error);
            }
        },
        async createUser() {
            try {
                const response = await axios.post("/api/register", this.newUser);
                this.users.push(response.data.data);
                this.newUser = {
                    name: "",
                    login: "",
                    password: "",
                    role_id: 2,
                    group_id: 1,
                };
            } catch (error) {
                console.error(error);
            }
        },
        editUser(user) {
            this.editUserId = user.id;
            this.editUserData = {
                name: user.name,
                login: user.login,
                password: "",
                role_id: user.role_id,
                group_id: user.group_id,
            };
            this.editModalVisible = true;
        },
        async updateUser() {
            try {
                const response = await axios.put(
                    `/api/admin/users/${this.editUserId}`,
                    this.editUserData
                );
                const updatedUser = response.data.data;
                const index = this.users.findIndex((u) => u.id === this.editUserId);
                if (index !== -1) {
                    this.users.splice(index, 1, updatedUser);
                }
                this.closeEditModal();
            } catch (error) {
                console.error(error);
            }
        },
        async deleteUser(userId) {
            try {
                await axios.delete(`/api/admin/users/${userId}`);
                this.users = this.users.filter((u) => u.id !== userId);
            } catch (error) {
                console.error(error);
            }
        },
        closeEditModal() {
            this.editModalVisible = false;
            this.editUserId = null;
            this.editUserData = {
                name: "",
                login: "",
                password: "",
                role_id: 2,
                group_id: 1,
            };
        },
    },
};
</script>

<style scoped>
/* Контейнер страницы */
.users-page {
    margin-top: 1rem;
    color: #333;
    animation: fadeIn 0.4s ease;
}

/* Заголовок */
.page-title {
    font-size: 1.4rem;
    margin-bottom: 1rem;
    color: #2e7d32; /* Зеленоватый */
    font-weight: 600;
}

/* Карточки */
.card {
    background: #fff;
    border-radius: 6px;
    margin-bottom: 1rem;
    box-shadow: 0 0 4px rgba(0, 0, 0, 0.1);
}

.card-header {
    background: #f1f8f5; /* чуть зеленоватый */
    padding: 0.6rem 1rem;
    font-weight: 600;
    border-bottom: 1px solid #ddd;
}

.card-body {
    padding: 1rem;
}

.form-row {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

/* Список пользователей */
.user-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.user-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fafafa;
    margin-bottom: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    transition: background 0.2s;
}

.user-item:hover {
    background: #eee;
}

.user-label {
    font-weight: 500;
}

/* Кнопки */
.btn {
    cursor: pointer;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    border: none;
    font-weight: 500;
    transition: transform 0.2s, background-color 0.3s;
    margin-left: 0.5rem;
}

.btn:hover {
    transform: scale(1.03);
}

.btn-success {
    background-color: #4caf50;
    color: #fff;
}

.btn-edit {
    background-color: #2196f3;
    color: #fff;
}

.btn-delete {
    background-color: #f44336;
    color: #fff;
}

.btn-gray {
    background-color: #999;
    color: #fff;
}

/* Инпуты */
.input-text,
.input-select {
    background: #fff;
    border: 1px solid #ccc;
    color: #333;
    padding: 0.4rem 0.7rem;
    border-radius: 4px;
    min-width: 120px;
}

/* Модальное окно */
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}
.modal-window {
    background: #fff;
    color: #333;
    padding: 1rem;
    border-radius: 8px;
    min-width: 300px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    animation: bounceIn 0.4s ease;
}
.modal-actions {
    margin-top: 1rem;
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}
.item-actions {
    display: flex;
}

/* Анимации */
.fade-in {
    animation: fadeIn 0.4s ease;
}
.fade-in-up {
    animation: fadeInUp 0.4s ease;
}
@keyframes fadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes bounceIn {
    0%   { transform: scale(0.9); opacity: 0.5; }
    50%  { transform: scale(1.02); opacity: 1; }
    100% { transform: scale(1); }
}
</style>

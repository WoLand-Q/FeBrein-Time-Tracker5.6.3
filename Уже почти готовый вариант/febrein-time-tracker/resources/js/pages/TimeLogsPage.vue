<template>
    <div class="time-logs-page fade-in">
        <h2 class="page-title">User Time Logs</h2>

        <!-- Карточка для добавления -->
        <div class="card">
            <div class="card-header">Create Time Log</div>
            <div class="card-body form-row">
                <input
                    type="datetime-local"
                    v-model="newLog.acted_at"
                    class="input-text"
                />
                <select v-model="newLog.event_id" class="input-select">
                    <option value="1">start</option>
                    <option value="2">start_break</option>
                    <option value="3">stop_break</option>
                    <option value="4">stop</option>
                </select>
                <button class="btn btn-success" @click="createTimeLog">
                    Create
                </button>
            </div>
        </div>

        <!-- Список логов -->
        <div class="card logs-list-card">
            <div class="card-header">Existing Time Logs</div>
            <div class="card-body">
                <ul class="log-list">
                    <li
                        v-for="log in timeLogs"
                        :key="log.id"
                        class="log-item fade-in-up"
                    >
            <span class="log-label">
              <strong>Log #{{ log.id }}</strong> —
              user_id={{ log.user_id }},
              acted_at={{ log.acted_at }},
              event_id={{ log.event_id }}
              ({{ log.even_name }})
            </span>
                        <div class="item-actions">
                            <button class="btn btn-info" @click="editLog(log)">Edit</button>
                            <button class="btn btn-danger" @click="deleteLog(log.id)">
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
                <h3>Edit Time Log</h3>
                <input
                    type="datetime-local"
                    v-model="editLogData.acted_at"
                    class="input-text"
                />
                <select v-model="editLogData.event_id" class="input-select">
                    <option value="1">start</option>
                    <option value="2">start_break</option>
                    <option value="3">stop_break</option>
                    <option value="4">stop</option>
                </select>
                <div class="modal-actions">
                    <button class="btn btn-success" @click="updateLog">Save</button>
                    <button class="btn btn-gray" @click="closeEditModal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    name: "TimeLogsPage",
    data() {
        return {
            timeLogs: [],
            newLog: {
                acted_at: "",
                event_id: 1,
            },
            editModalVisible: false,
            editLogId: null,
            editLogData: {
                acted_at: "",
                event_id: 1,
            },
        };
    },
    mounted() {
        this.loadTimeLogs();
    },
    methods: {
        async loadTimeLogs() {
            try {
                const response = await axios.get("/api/admin/userTimeLogs");
                this.timeLogs = response.data.data;
            } catch (error) {
                console.error(error);
            }
        },
        async createTimeLog() {
            try {
                const response = await axios.post(
                    "/api/admin/userTimeLogs",
                    this.newLog
                );
                this.timeLogs.push(response.data.data);
                this.newLog = { acted_at: "", event_id: 1 };
            } catch (error) {
                console.error(error);
            }
        },
        editLog(log) {
            this.editLogId = log.id;
            this.editLogData = {
                acted_at: log.acted_at,
                event_id: log.event_id,
            };
            this.editModalVisible = true;
        },
        async updateLog() {
            try {
                const response = await axios.put(
                    `/api/admin/userTimeLogs/${this.editLogId}`,
                    this.editLogData
                );
                const updatedLog = response.data.data;
                const index = this.timeLogs.findIndex((l) => l.id === this.editLogId);
                if (index !== -1) {
                    this.timeLogs.splice(index, 1, updatedLog);
                }
                this.closeEditModal();
            } catch (error) {
                console.error(error);
            }
        },
        async deleteLog(id) {
            try {
                await axios.delete(`/api/admin/userTimeLogs/${id}`);
                this.timeLogs = this.timeLogs.filter((l) => l.id !== id);
            } catch (error) {
                console.error(error);
            }
        },
        closeEditModal() {
            this.editModalVisible = false;
            this.editLogId = null;
            this.editLogData = {
                acted_at: "",
                event_id: 1,
            };
        },
    },
};
</script>

<style scoped>
.time-logs-page {
    margin-top: 1rem;
    color: #333;
    animation: fadeIn 0.4s ease;
}

.page-title {
    font-size: 1.4rem;
    margin-bottom: 1rem;
    color: #2e7d32;
    font-weight: 600;
}

.card {
    background: #fff;
    border-radius: 6px;
    margin-bottom: 1rem;
    box-shadow: 0 0 4px rgba(0,0,0,0.1);
}
.card-header {
    background: #f1f8f5;
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

.log-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.log-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fafafa;
    margin-bottom: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    transition: background 0.2s;
}
.log-item:hover {
    background: #eee;
}
.log-label {
    font-weight: 500;
}

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
.btn-info {
    background-color: #2196f3;
    color: #fff;
}
.btn-danger {
    background-color: #f44336;
    color: #fff;
}
.btn-gray {
    background-color: #999;
    color: #fff;
}

.input-text,
.input-select {
    background: #fff;
    border: 1px solid #ccc;
    color: #333;
    padding: 0.4rem 0.7rem;
    border-radius: 4px;
    min-width: 120px;
}

.modal-backdrop {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
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

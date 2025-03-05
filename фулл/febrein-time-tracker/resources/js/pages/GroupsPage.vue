<template>
    <div class="groups-page animate__animated animate__fadeIn">
        <h2 class="page-title">Groups Management</h2>

        <div class="card">
            <div class="card-header">Create new group</div>
            <div class="card-body form-row">
                <input
                    type="text"
                    v-model="newGroupName"
                    placeholder="Enter group name"
                    class="input-text"
                />
                <button class="btn btn-primary" @click="createGroup">Create Group</button>
            </div>
        </div>

        <div class="card groups-list-card">
            <div class="card-header">Existing Groups</div>
            <div class="card-body">
                <ul class="group-list">
                    <li
                        v-for="group in groups"
                        :key="group.id"
                        class="group-item animate__animated animate__fadeInUp"
                    >
                        <span>{{ group.group_name }}</span>
                        <div class="item-actions">
                            <button class="btn btn-edit" @click="editGroup(group)">Edit</button>
                            <button class="btn btn-delete" @click="deleteGroup(group.id)">Delete</button>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Модальное окно для редактирования -->
        <div v-if="editModalVisible" class="modal">
            <div class="modal-content animate__animated animate__zoomIn">
                <h3>Edit Group</h3>
                <input
                    type="text"
                    v-model="editGroupName"
                    class="input-text"
                />
                <div class="modal-actions">
                    <button class="btn btn-primary" @click="updateGroup">Save</button>
                    <button class="btn btn-cancel" @click="closeEditModal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'GroupsPage',
    data() {
        return {
            groups: [],
            newGroupName: '',
            editModalVisible: false,
            editGroupId: null,
            editGroupName: '',
        };
    },
    mounted() {
        this.loadGroups();
    },
    methods: {
        async loadGroups() {
            try {
                const response = await axios.get('/api/admin/groups');
                this.groups = response.data.data;
            } catch (error) {
                console.error(error);
            }
        },
        async createGroup() {
            if (!this.newGroupName) return;
            try {
                const response = await axios.post('/api/admin/groups', {
                    group_name: this.newGroupName,
                });
                this.groups.push(response.data.data);
                this.newGroupName = '';
            } catch (error) {
                console.error(error);
            }
        },
        editGroup(group) {
            this.editGroupId = group.id;
            this.editGroupName = group.group_name;
            this.editModalVisible = true;
        },
        async updateGroup() {
            try {
                const response = await axios.put(`/api/admin/groups/${this.editGroupId}`, {
                    group_name: this.editGroupName,
                });
                const updatedGroup = response.data.data;
                const index = this.groups.findIndex((g) => g.id === this.editGroupId);
                if (index !== -1) {
                    this.groups.splice(index, 1, updatedGroup);
                }
                this.closeEditModal();
            } catch (error) {
                console.error(error);
            }
        },
        async deleteGroup(id) {
            try {
                await axios.delete(`/api/admin/groups/${id}`);
                this.groups = this.groups.filter((group) => group.id !== id);
            } catch (error) {
                console.error(error);
            }
        },
        closeEditModal() {
            this.editModalVisible = false;
            this.editGroupId = null;
            this.editGroupName = '';
        },
    },
};
</script>

<style scoped>
/* Страница */
.groups-page {
    margin-top: 1rem;
    color: #fff;
}

/* Заголовок */
.page-title {
    font-size: 1.4rem;
    margin-bottom: 1rem;
    color: #fff;
    text-shadow: 1px 1px 3px #000;
}

/* Карточки */
.card {
    background: rgba(255,255,255,0.05);
    border-radius: 6px;
    margin-bottom: 1rem;
    box-shadow: 0 0 4px rgba(0,0,0,0.2);
}
.card-header {
    background: rgba(255,255,255,0.1);
    padding: 0.5rem 1rem;
    font-weight: 600;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}
.card-body {
    padding: 1rem;
}
.form-row {
    display: flex;
    gap: 1rem;
    align-items: center;
}
.groups-list-card {
    /* Можно доп. стили */
}

/* Список групп */
.group-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.group-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(255,255,255,0.05);
    margin-bottom: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    transition: background 0.2s;
}
.group-item:hover {
    background: rgba(255,255,255,0.1);
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
.btn-primary {
    background-color: #f44336; /* красный для "новогоднего" контраста */
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
.btn-cancel {
    background-color: #999;
    color: #fff;
}

/* Инпуты */
.input-text {
    background: #2e2e2e;
    border: 1px solid #666;
    color: #fff;
    padding: 0.4rem 0.7rem;
    border-radius: 4px;
}

/* Модальное окно */
.modal {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}
.modal-content {
    background: #fff;
    color: #000;
    padding: 1rem;
    border-radius: 8px;
    min-width: 300px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
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
</style>

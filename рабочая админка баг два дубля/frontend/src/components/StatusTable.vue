<template>
    <div class="status-table animate__animated animate__fadeInUp">
        <h2 class="status-title">Team Status</h2>
        <table>
            <thead>
            <tr>
                <th>Name</th>
                <th>Status</th>
                <th>Last Updated</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="user in users" :key="user.id">
                <td>{{ user.name }}</td>
                <td :class="getStatusClass(user.status)">
                    {{ user.status }}
                </td>
                <td>{{ user.lastUpdated }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
export default {
    name: "StatusTable",
    data() {
        return {
            users: [
                { id: 1, name: "Alice", status: "Working", lastUpdated: "10:15 AM" },
                { id: 2, name: "Bob", status: "On Break", lastUpdated: "10:30 AM" },
                { id: 3, name: "Charlie", status: "Offline", lastUpdated: "9:45 AM" },
            ],
        };
    },
    methods: {
        getStatusClass(status) {
            switch (status) {
                case "Working":
                    return "status-working";
                case "On Break":
                    return "status-break";
                case "Offline":
                    return "status-offline";
                default:
                    return "";
            }
        },
    },
};
</script>

<style scoped>
.status-table {
    width: 100%;
    background: #1f1f1f;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    color: #fff;
}

.status-title {
    font-size: 1.8rem;
    margin-bottom: 1rem;
    color: #ff4545;
    text-align: center;
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background: #2d2d2d;
}

thead th {
    padding: 1rem;
    text-align: left;
    color: #fff;
}

tbody tr {
    border-bottom: 1px solid #333;
}

tbody td {
    padding: 1rem;
    color: #fff;
}

tbody tr:hover {
    background: #3f3f3f;
}

.status-working {
    color: #4caf50;
}

.status-break {
    color: #ff9800;
}

.status-offline {
    color: #f44336;
}
</style>

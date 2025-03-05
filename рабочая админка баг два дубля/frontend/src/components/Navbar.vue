<template>
    <nav class="navbar">
        <div class="navbar-logo">
            <h1>TimeTracker</h1>
        </div>
        <div class="navbar-links">
            <button @click="navigateTo('/dashboard')">Dashboard</button>
            <button @click="navigateTo('/timer')">Timer</button>
            <button class="btn-danger" @click="logout">Logout</button>
        </div>
    </nav>
</template>

<script>
export default {
    name: "Navbar",
    methods: {
        navigateTo(route) {
            this.$router.push(route);
        },
        logout() {
            alert("Logged out successfully!");
        },
    },
};
</script>

<style scoped>
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #1f1f1f;
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
}
</style>

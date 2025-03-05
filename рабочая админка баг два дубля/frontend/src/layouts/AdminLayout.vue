<template>
    <div class="flex">
        <Sidebar />
        <div class="flex-1 ml-64 p-4">
            <Navbar />
            <slot />
        </div>
    </div>
    <div class="admin-layout">
        <sidebar /> <!-- Добавляем Sidebar -->
        <main class="main-content">
            <router-view />
        </main>
    </div>
</template>

<script>
import Sidebar from "../components/Sidebar.vue";
import Navbar from "../components/Navbar.vue";

export default {
    name: "AdminLayout",
    components: { Sidebar, Navbar },
};

</script>

<style scoped>
.admin-layout {
    display: flex;
    height: 100vh;
    background-color: #121212;
}

.sidebar {
    width: 250px;
    background-color: #1f1f1f;
    color: #fff;
}

.main-content {
    flex: 1;
    padding: 1rem;
}
</style>

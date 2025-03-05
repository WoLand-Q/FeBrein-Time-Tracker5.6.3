<template>
    <div class="base-layout">
        <sidebar /> <!-- Меню -->
        <div class="content">
            <header-bar /> <!-- Верхняя панель -->
            <main class="main-content">
                <router-view /> <!-- Основное содержимое -->
            </main>
        </div>
    </div>
</template>

<script>
import Sidebar from "@/components/Sidebar.vue";
import HeaderBar from "@/components/HeaderBar.vue";

export default {
    name: "BaseLayout",
    components: {
        Sidebar,
        HeaderBar,
    },
};
</script>

<style scoped>
.base-layout {
    display: flex;
    height: 100vh;
    background: #121212; /* Общий фон */
    color: #fff;
}

.content {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.main-content {
    flex: 1;
    padding: 1rem;
    overflow-y: auto;
    background: #1f1f1f; /* Зона контента */
    border-radius: 8px;
}
</style>

<template>
  <div class="app-layout">
    <!-- Горизонтальный header-бар -->
    <HeaderBar />
    <!-- Основное содержимое -->
    <main class="main-content">
      <router-view />
    </main>
  </div>
</template>

<script>
import HeaderBar from "@/components/AdminHeaderBar.vue";

export default {
  name: "AppLayout",
  components: {HeaderBar}
};
</script>

<style scoped>
.app-layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background-color: #121212; /* темный фон под остальное */
}

.main-content {
  flex: 1;
  padding: 1rem;
  /* Основной блок под ваш контент */
}
</style>

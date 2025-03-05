<template>
  <div class="admin-layout">
    <AdminSidebar />
    <div class="main-content">
      <!-- Здесь можно вставить какую-то админскую шапку, если надо -->
      <router-view />
    </div>
  </div>
</template>

<script>
import AdminSidebar from "@/components/AdminSidebar.vue";

export default {
  name: "AdminLayout",
  components: { AdminSidebar },
};
</script>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
  height: 100%;
  background-color: #121212;
}


.main-content {
  flex: 1;
  padding: 1rem;
  display: flex;
  flex-direction: column;
}
</style>

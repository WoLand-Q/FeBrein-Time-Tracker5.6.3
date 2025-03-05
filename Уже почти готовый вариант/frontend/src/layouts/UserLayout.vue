<template>
  <div class="user-layout">
    <UserSidebar />
    <div class="main-content">
      <!-- Тут можно user-шапку, если нужна -->
      <router-view />
    </div>
  </div>
</template>

<script>
import UserSidebar from "@/components/UserSidebar.vue";

export default {
  name: "UserLayout",
  components: { UserSidebar },
};
</script>

<style scoped>
.user-layout {
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

<template>
  <header class="header-bar">
    <!-- Левая часть: иконка 'выключения' для выхода из системы -->
    <div class="left-section">
      <!-- Можно любую иконку, в данном случае fa-power-off -->
      <button class="power-btn" @click="logout">
        <i class="fa fa-power-off"></i>
      </button>
      <!-- Название или логотип системы -->
      <span class="brand-name">FeBrein Time Tracker</span>
    </div>

    <!-- Правая часть: горизонтальное меню -->
    <nav class="menu">
      <router-link
          to="/admin/dashboard"
          class="nav-link"
          active-class="active-link"
      >
        Адмін Статистика
      </router-link>

      <router-link
          to="/admin/timer"
          class="nav-link"
          active-class="active-link"
      >
        Таймер
      </router-link>

      <router-link
          to="/admin/admin-page"
          class="nav-link"
      >
        Адмін панель

      </router-link>

      <!--  <router-link
          to="/admin/plugins"
          class="nav-link"
      >
        Плагіни

      </router-link> -->

      <router-link
          to="/admin/changelog"
          class="nav-link"
          active-class="active-link"
      >
        <i class="fa fa-file-alt" style="margin-right: 0.4rem;"></i>
        ChangeLog
      </router-link>
    </nav>
  </header>
</template>

<script>
import api from "@/api";
import { mapState } from "vuex";

export default {
  name: "HeaderBar",
  computed: {
    ...mapState({
      role: state => state.user.role
    })
  },
  methods: {
    async logout() {
      try {
        await api.delete("/api/logout");
        this.$store.commit("logout");
        this.$router.push("/login");
      } catch (error) {
        console.error("Logout error:", error);
      }
    }
  }
};
</script>

<style scoped>
/* Сам контейнер шапки */
.header-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1rem;
  background-color: #1f1f1f;  /* темный фон */
  border-bottom: 1px solid #333;
  height: 60px;
}

/* Левая часть (иконка + бренд) */
.left-section {
  display: flex;
  align-items: center;
}

.brand-name {
  margin-left: 1rem;
  font-size: 1.2rem;
  color: #ff4545; /* фирменный цвет */
}

/* Кнопка с иконкой power-off */
.power-btn {
  background: none;
  border: none;
  color: #fff;
  cursor: pointer;
  font-size: 1.2rem; /* для иконки */
  transition: transform 0.2s, color 0.2s;
}

.power-btn:hover {
  color: #ff4545;
  transform: scale(1.1);
}

/* Правая часть: горизонтальное меню */
.menu {
  display: flex;
  align-items: center;
}

.nav-link {
  color: #fff;
  text-decoration: none;
  margin-left: 1rem;
  padding: 0.5rem;
  transition: color 0.2s, background-color 0.2s;
}

.nav-link:hover {
  background-color: #2d2d2d;
  color: #ff4545;
  border-radius: 4px;
}

.active-link {
  background-color: #2d2d2d;
  color: #ff4545;
  border-radius: 4px;
}
</style>

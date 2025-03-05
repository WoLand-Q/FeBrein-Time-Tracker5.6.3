<template>
  <aside class="sidebar">
    <div class="sidebar-header">

      <h2>Меню</h2>
    </div>

    <ul class="menu-list">

      <li>
        <router-link to="/user/dashboard" class="menu-link">Статистика</router-link>
      </li>
      <li>
        <router-link to="/user/timer" class="menu-link">Таймер</router-link>
      </li>
      <li>

        <button class="menu-link logout-btn" @click="logout">Вийти</button>
      </li>
    </ul>
  </aside>
</template>

<script>
import { mapState } from "vuex";
import api from "@/api";

export default {
  name: "UserSidebar",
  computed: {
    ...mapState({
      role: state => state.user.role,
    }),
  },
  methods: {
    async logout() {
      try {
        await api.delete("/api/logout");
        this.$store.commit("logout");
        this.$router.push("/login");
      } catch (err) {
        console.error(err);
      }
    },
  },
};
</script>

<style scoped>
.sidebar {
  width: 250px;
  background-color: #1f1f1f;
  color: #fff;
  display: flex;
  flex-direction: column;
  height: 100%;
  min-height: 100%;
  box-shadow: 0 0 8px rgba(0, 0, 0, 0.3);
}


/* Шапка сайдбара (логотип, название и т.д.) */
.sidebar-header {
  padding: 1rem;
  border-bottom: 1px solid #333;
  text-align: center;
}

.sidebar-header h2 {
  margin: 0;
  font-size: 1.2rem;
  color: #ff4545;
}

.menu-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  flex-grow: 2; /* Меню занимает все доступное пространство */

}

.menu-list li {
  margin: 0;
}

.menu-link {
  display: block;
  width: 100%;
  padding: 0.75rem 1rem;
  text-decoration: none;
  color: #fff;
  background: transparent;
  border: none;
  text-align: left;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s, transform 0.2s;
}

.menu-link:hover {
  background-color: #2d2d2d;
  transform: translateX(4px);
  color: #ff4545;
}

.router-link-active {
  background-color: #2d2d2d;
  color: #ff4545;
}


.logout-btn {
  border-top: 1px solid #333;
  margin-top: auto;
}
</style>

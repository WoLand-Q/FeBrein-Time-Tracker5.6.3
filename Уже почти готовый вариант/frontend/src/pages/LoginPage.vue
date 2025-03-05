<template>

  <div class="login-page">

    <div class="login-container">
      <h2>Вхід</h2>
      <form @submit.prevent="performLogin" class="login-form">
        <div class="form-group">
          <label for="login">Логін</label>
          <input
              id="login"
              v-model="login"
              type="text"
              placeholder="Введіть логін"
              required
          />
        </div>

        <div class="form-group">
          <label for="password">Пароль</label>
          <input
              id="password"
              v-model="password"
              type="password"
              placeholder="Введіть пароль"
              required
          />
        </div>

        <button type="submit" class="login-button">Увійти</button>
      </form>

      <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
    </div>
  </div>
</template>

<script>
import api from "@/api";
import store from "@/store/store.js";

export default {
  name: "LoginPage",
  data() {
    return {
      login: "",
      password: "",
      errorMessage: "",
    };
  },
  methods: {
    async performLogin() {
      try {

        await api.get("/sanctum/csrf-cookie");


        await api.post("/api/login", {
          login: this.login,
          password: this.password,
        });


        store.commit("setUser", { role: null, isAuthenticated: true });


        const userResponse = await api.get("/api/admin/user");
        const userData = userResponse.data;


        let userRole = "user";
        if (userData.role_id === 1) {
          userRole = "admin";
        }


        store.commit("setUser", { role: userRole, isAuthenticated: true });


        if (userRole === "admin") {
          this.$router.push("/admin/timer");
        } else {
          this.$router.push("/user/timer");
        }
      } catch (error) {
        this.errorMessage =
            "Помилка входу: " + (error.response?.data?.message || "Спробуйте ще раз");
        console.error("Login error:", error);
      }
    },
  },
};
</script>

<style scoped>

.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: radial-gradient(circle at 30% 30%, #121212 0%, #000000 100%);
  font-family: 'Figtree', sans-serif;
  color: #fff;
  margin: 0;
}

/* Картка форми */
.login-container {
  width: 100%;
  max-width: 400px;
  padding: 2rem;
  background: #1f1f1f;
  border-radius: 8px;
  box-shadow: 0 6px 14px rgba(0, 0, 0, 0.4);
  animation: fadeInUp 0.6s ease forwards;
}

/* Анімація появи */
@keyframes fadeInUp {
  0% {
    opacity: 0;
    transform: translateY(10px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

.login-container h2 {
  text-align: center;
  margin-bottom: 1.5rem;
  font-size: 28px;
  color: #ff4545;
}

/* Форма */
.login-form {
  display: flex;
  flex-direction: column;
}

.form-group {
  margin-bottom: 1rem;
  display: flex;
  flex-direction: column;
}

/* Підписи до полів */
.form-group label {
  margin-bottom: 0.5rem;
  font-size: 14px;
  color: #b3b3b3;
}

/* Поля введення */
.form-group input {
  padding: 0.75rem;
  border: 1px solid transparent;
  border-radius: 4px;
  background: #2d2d2d;
  color: #fff;
  font-size: 16px;
  transition: border 0.3s;
}

.form-group input:focus {
  border: 1px solid #ff4545;
  outline: none;
}

.form-group input::placeholder {
  color: #b3b3b3;
}

.login-button {
  padding: 0.75rem;
  background-color: #ff4545;
  border: none;
  border-radius: 4px;
  font-size: 16px;
  color: #fff;
  cursor: pointer;
  transition: background 0.3s, transform 0.2s;
}


.login-button:hover {
  background-color: #e03e3e;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
}


.error {
  margin-top: 1rem;
  text-align: center;
  color: #ff4545;
  font-weight: 500;
}
</style>

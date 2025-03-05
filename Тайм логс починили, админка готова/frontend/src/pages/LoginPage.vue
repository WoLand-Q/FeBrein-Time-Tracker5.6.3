<template>
  <div class="login-container">
    <h2>Вход</h2>
    <form @submit.prevent="performLogin" class="login-form">
      <div class="form-group">
        <label for="login">Логин</label>
        <input
          id="login"
          v-model="login"
          type="text"
          placeholder="Введите логин"
          required
        />
      </div>
      <div class="form-group">
        <label for="password">Пароль</label>
        <input
          id="password"
          v-model="password"
          type="password"
          placeholder="Введите пароль"
          required
        />
      </div>
      <button type="submit" class="login-button">Войти</button>
    </form>
    <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
  </div>
</template>

<script>
import api from "@/api"; // ваш axios-инстанс

export default {
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
        // Перед логином  можно сделать await initializeCsrf() — запрос sanctum/csrf-cookie
        await api.get("/sanctum/csrf-cookie");
        // А потом обычный POST на /api/login
        await api.post("/api/login", {
          login: this.login,
          password: this.password,
        });
        // Всё, мы залогинились, куки на клиенте. Дальше переходим в админку.
        this.$router.push("/dashboard");
      } catch (error) {
        this.errorMessage = "Ошибка входа: " + (error.response?.data?.message || "Попробуйте снова");
        console.error("Login error:", error);
      }
    }
  }
};
</script>


<style scoped>
.login-container {
  max-width: 400px;
  margin: 80px auto;
  padding: 2rem;
  background: #1f1f1f;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  color: #fff;
  font-family: 'Figtree', sans-serif;
}

.login-container h2 {
  text-align: center;
  margin-bottom: 1.5rem;
  font-size: 28px;
}

.login-form {
  display: flex;
  flex-direction: column;
}

.form-group {
  margin-bottom: 1rem;
  display: flex;
  flex-direction: column;
}

.form-group label {
  margin-bottom: 0.5rem;
  font-size: 14px;
  color: #b3b3b3;
}

.form-group input {
  padding: 0.75rem;
  border: none;
  border-radius: 4px;
  background: #2d2d2d;
  color: #fff;
  font-size: 16px;
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
  transition: background 0.3s;
}

.login-button:hover {
  background-color: #e03e3e;
}

.error {
  margin-top: 1rem;
  text-align: center;
  color: #ff4545;
}
</style>

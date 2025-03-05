import { createApp } from 'vue';
import App from './App.vue';
import router from './router/index.js';
import './assets/styles.css'; // Tailwind и кастомные стили
import 'animate.css'; // Подключение animate.css

const app = createApp(App);
app.use(router);
app.mount('#app');

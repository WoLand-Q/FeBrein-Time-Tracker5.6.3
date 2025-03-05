import { createApp } from 'vue';
import App from './App.vue';
import router from './router/index.js';
import './assets/styles.css'; // Tailwind и кастомные стили
import 'animate.css'; // Подключение animate.css
import store from './store/store.js';
const app = createApp(App);
app.use(store);
app.use(router);
app.mount('#app');

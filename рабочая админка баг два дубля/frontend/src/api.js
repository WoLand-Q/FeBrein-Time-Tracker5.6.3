// api.js
import axios from "axios";

const token = localStorage.getItem("token");
const headers = {};

if (token) {
    headers["Authorization"] = `Bearer ${token}`;
}


const api = axios.create({
    baseURL: "http://127.0.0.1:8000",  // Указываем API-путь
    withCredentials: true, // Включаем поддержку кук
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    }
});




async function initializeCsrf() {
    await axios.get("http://127.0.0.1:8000/sanctum/csrf-cookie", { withCredentials: true });
}

initializeCsrf();

// Перехватчик запросов, чтобы добавлять токен
api.interceptors.request.use(config => {
    const token = localStorage.getItem("token");
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
}, error => Promise.reject(error));
export default api;

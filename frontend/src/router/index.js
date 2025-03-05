import { createRouter, createWebHistory } from "vue-router";
import store from "@/store/store.js";
import api from "@/api";

// Импорт статических компонентов и маршрутов
import AdminLayout from "@/layouts/AdminLayout.vue";
import UserLayout from "@/layouts/UserLayout.vue";
import Log from "@/components/ChangeLogPage.vue";
import AdminDashboard from "@/pages/AdminDashboard.vue";
import AdminPage from "@/pages/AdminPage.vue";
import PluginsManager from "@/pages/PluginsManager.vue";
import UserDashboard from "@/pages/UserDashboard.vue";
import TimerPage from "@/pages/TimerPage.vue";
import LoginPage from "@/pages/LoginPage.vue";

// Статические маршруты (не зависят от плагинов)
const routes = [
    // --- Админский блок ---
    {
        path: "/admin",
        component: AdminLayout,
        meta: { requiresAuth: true, role: "admin" },
        children: [
            {
                path: "dashboard",
                name: "admin-dashboard",
                component: AdminDashboard,
            },
            {
                path: "admin-page",
                name: "admin-page",
                component: AdminPage,
            },
            {
                path: "changelog",
                name: "changelog",
                component: Log,
            },
            {
                path: "timer",
                name: "admin-timer",
                component: TimerPage,
            },
            {
                path: "plugins",
                name: "admin-pluginsmanager",
                component: PluginsManager,
            },
        ],
    },

    // --- Юзерский блок ---
    {
        path: "/user",
        component: UserLayout,
        meta: { requiresAuth: true, role: "user" },
        children: [
            {
                path: "dashboard",
                name: "user-dashboard",
                component: UserDashboard,
            },
            {
                path: "timer",
                name: "user-timer",
                component: TimerPage,
            },
            {
                path: "changelog",
                name: "user-changelog",
                component: Log,
            },
        ],
    },

    // --- Страница логина ---
    {
        path: "/login",
        name: "login",
        component: LoginPage,
    },

    // --- Корень ---
    {
        path: "/",
        redirect: "/login",
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Глобальная защита маршрутов с асинхронной проверкой авторизации
router.beforeEach(async (to, from, next) => {
    // Если маршрут не требует авторизации — пускаем
    if (!to.meta.requiresAuth) {
        return next();
    }

    // Если пользователь ещё не авторизован в Vuex, пробуем получить данные с сервера
    if (!store.state.user.isAuthenticated) {
        try {
            const response = await api.get("/api/user");
            const userData = response.data;

            // Определяем роль. Предполагается, что сервер возвращает role_id
            let userRole = "user";
            if (userData.role_id === 1) {
                userRole = "admin";
            }

            store.commit("setUser", { role: userRole, isAuthenticated: true });
        } catch (error) {
            store.commit("logout");
            return next("/login");
        }
    }

    // После получения данных проверяем, соответствует ли роль требуемой для маршрута
    const userRole = store.state.user.role;
    if (to.meta.role && to.meta.role !== userRole) {
        return next("/login");
    }

    next();
});


export default router;

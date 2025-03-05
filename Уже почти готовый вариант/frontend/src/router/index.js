// router/index.js

import { createRouter, createWebHistory } from "vue-router";
import store from "@/store/store.js";

// Импортируем всё из папки pages
import AdminLayout from "@/layouts/AdminLayout.vue";
import UserLayout from "@/layouts/UserLayout.vue";

import AdminDashboard from "@/pages/AdminDashboard.vue";
import AdminPage from "@/pages/AdminPage.vue";

import UserDashboard from "@/pages/UserDashboard.vue";
import TimerPage from "@/pages/TimerPage.vue";

import LoginPage from "@/pages/LoginPage.vue";

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
            // Если нужен /admin/timer
            {
                path: "timer",
                name: "admin-timer",
                component: TimerPage,
            },
        ],
    },

    // --- Юзерский блок ---
    {
        path: "/user",
        component: UserLayout, // вёрстка с UserSidebar, например
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

// Глобальная «защита» маршрутов
router.beforeEach((to, from, next) => {
    const userRole = store.state.user.role;
    const isAuthenticated = store.state.user.isAuthenticated;

    // 1) Если маршрут требует авторизации, а пользователь не залогинен:
    if (to.meta.requiresAuth && !isAuthenticated) {
        return next("/login");
    }

    // 2) Если у маршрута указана конкретная роль (role), а у пользователя другая:
    if (to.meta.role && to.meta.role !== userRole) {
        return next("/login");
    }

    // Иначе пропускаем
    next();
});

export default router;

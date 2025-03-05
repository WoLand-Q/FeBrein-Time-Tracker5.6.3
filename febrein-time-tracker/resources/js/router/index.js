import { createRouter, createWebHistory } from 'vue-router';
import AdminLayout from '../layouts/AdminLayout.vue';

// Ваши внутренние страницы:
import DashboardPage from '../pages/DashboardPage.vue';
import GroupsPage from '../pages/GroupsPage.vue';
import UsersPage from '../pages/UsersPage.vue';
import TimeLogsPage from '../pages/TimeLogsPage.vue';

const routes = [
    {
        path: '/admin',
        component: AdminLayout,       // <-- Указываем общий Layout
        children: [
            { path: 'dashboard', component: DashboardPage, name: 'admin.dashboard' },
            { path: 'groups',    component: GroupsPage,    name: 'admin.groups' },
            { path: 'users',     component: UsersPage,     name: 'admin.users' },
            { path: 'time-logs', component: TimeLogsPage,  name: 'admin.timeLogs' },

            // Если нужно, можно добавить редирект с /admin -> /admin/dashboard
            { path: '', redirect: { name: 'admin.dashboard' } },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;

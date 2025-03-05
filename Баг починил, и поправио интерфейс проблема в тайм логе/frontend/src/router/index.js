import { createRouter, createWebHistory } from 'vue-router';
import BaseLayout from '@layouts/BaseLayout.vue';
import Dashboard from '@pages/Dashboard.vue';
import AdminPage from '@pages/AdminPage.vue';
import LoginPage from '@pages/LoginPage.vue';
import GroupsPage from "@pages/GroupsPage.vue";

const routes = [
    {
        path: '/',
        component: BaseLayout,
        redirect: '/dashboard',
        children: [
            { path: 'dashboard', component: Dashboard, name: 'dashboard' },
            { path: 'admin', component: AdminPage, name: 'admin' },
        ],
    },
    {
        path: '/login',
        name: 'login',
        component: LoginPage,
    },   {
        path: '/groups',
        name: 'groups',
        component: GroupsPage,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;

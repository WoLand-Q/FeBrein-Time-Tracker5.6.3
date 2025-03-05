// plugin.js

// Импортируем Vue-компонент
import ProfilePage from './ProfilePage.vue';

export default {
    name: "ProfilePlugin",

    // Массив маршрутов, которые нужно добавить
    routes: [
        {
            path: '/profile',
            name: 'profile',
            component: ProfilePage
        }
    ],

    // Опционально: если в вашей системе есть логика автодобавления в меню
    // (вы можете это поле игнорировать, если не нужно)
    menu: [
        {
            label: 'Профиль',
            icon: 'user',
            route: '/profile'
        }
    ]
};

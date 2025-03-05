import ProfilePage from './ProfilePage.vue'

export default {
    slug: 'profile', // Должно совпадать с plugin.json (например, "profile")
    name: 'Profile Plugin',
    // Для фронтенда: массив маршрутов, которые добавляет плагин
    routes: [
        {
            path: '/profile',
            name: 'ProfilePage',
            component: ProfilePage
        }
    ],
    // Мета-информация (опционально)
    description: 'Плагин для отображения профиля пользователя',
    version: '1.0.0',
    author: 'Ваше имя'
}

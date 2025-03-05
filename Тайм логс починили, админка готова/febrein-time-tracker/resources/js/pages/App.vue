<template>
    <div class="app-container">
        <main class="app-main fade-in-up">
            <router-view />
        </main>
    </div>
</template>

<script>
export default {
    name: 'AdminLayout',
};
</script>

<style scoped>
/* 1) Сброс отступов и растяжение html и body на весь экран */
html, body {
    margin: 0;
    padding: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

/* 2) Корневой контейнер приложения */
.app-container {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    background: #eef5f3; /* Установите желаемый цвет фона */
}

/* 3) Основная область для контента */
.app-main {
    flex: 1;
    width: 100%;
    height: 100%;
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    background: transparent; /* Сделайте фон прозрачным или установите нужный цвет */
    color: #333;
    overflow-y: auto; /* Разрешаем вертикальную прокрутку при необходимости */
}

/* 4) Анимация плавного появления */
.fade-in-up {
    animation: fadeInUp 0.4s ease forwards;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

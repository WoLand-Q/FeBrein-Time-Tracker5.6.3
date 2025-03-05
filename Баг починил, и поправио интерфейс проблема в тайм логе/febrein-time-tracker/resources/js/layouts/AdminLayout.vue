<template>
    <div class="admin-layout">
        <!-- Верхняя панель (header) -->
        <header class="header">
            <div class="header-left">
                <img
                    class="logo"
                    src="https://cdn-icons-png.flaticon.com/512/2936/2936631.png"
                    alt="CRM Logo"
                />
                <h1 class="title">SnowCRM</h1>
            </div>
            <div class="header-right">
                <span class="user-info">Hello, Admin!</span>
                <button class="logout-btn">Logout</button>
            </div>
        </header>

        <!-- Боковое меню -->
        <aside class="sidebar">
            <nav class="menu">
                <router-link to="/admin/dashboard" class="menu-item" active-class="active">
                    <i class="fa fa-home"></i> Dashboard
                </router-link>
                <router-link to="/admin/users" class="menu-item" active-class="active">
                    <i class="fa fa-user"></i> Users
                </router-link>
                <router-link to="/admin/groups" class="menu-item" active-class="active">
                    <i class="fa fa-users"></i> Groups
                </router-link>
                <router-link to="/admin/time-logs" class="menu-item" active-class="active">
                    <i class="fa fa-clock-o"></i> Time Logs
                </router-link>
            </nav>
        </aside>

        <!-- Основной контент -->
        <main class="main-content">
            <router-view />
        </main>

        <!-- Нижняя панель (footer) -->
        <footer class="footer">
            <p class="footer-text">© 2024 SnowCRM | Powered by Vue & Laravel</p>
        </footer>
    </div>
</template>

<script>
export default {
    name: "AdminLayout",
};
</script>

<style scoped>
/* Контейнер */
.admin-layout {
    display: grid;
    grid-template-columns: 220px 1fr;
    grid-template-rows: 60px auto 40px;
    grid-template-areas:
    "header header"
    "sidebar main"
    "footer footer";
    min-height: 100vh;
    background-color: #f9f9f9; /* Светлый фон */
    color: #333;
    font-family: "Poppins", sans-serif;
    overflow: hidden; /* Убираем прокрутку */
}

/* Верхняя панель */
.header {
    grid-area: header;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #3aa546; /* Зелёная полоса */
    padding: 0 1rem;
}
.header-left {
    display: flex;
    align-items: center;
}
.logo {
    width: 40px;
    height: 40px;
    margin-right: 0.5rem;
}
.title {
    font-size: 1.3rem;
    color: #fff;
    margin: 0;
}
.header-right {
    display: flex;
    align-items: center;
}
.user-info {
    color: #fff;
    margin-right: 1rem;
}
.logout-btn {
    background: #fff;
    color: #3aa546;
    border: none;
    border-radius: 4px;
    padding: 0.4rem 0.8rem;
    cursor: pointer;
    font-weight: 500;
}
.logout-btn:hover {
    background: #f1f1f1;
}

/* Боковое меню */
.sidebar {
    grid-area: sidebar;
    background-color: #ffffff;
    border-right: 1px solid #ddd;
    padding-top: 0.5rem;
    overflow: hidden; /* Убираем прокрутку бокового меню */
}
.menu {
    display: flex;
    flex-direction: column;
}
.menu-item {
    display: flex;
    align-items: center;
    padding: 0.7rem 1rem;
    text-decoration: none;
    color: #333;
    border-bottom: 1px solid #eee;
    transition: background 0.2s;
}
.menu-item i {
    margin-right: 0.5rem;
    color: #3aa546;
}
.menu-item:hover {
    background-color: #3aa546;
    color: #fff;
}
.active {
    background-color: #3aa546;
    color: #fff !important;
}

/* Основной контент */
.main-content {
    grid-area: main;
    padding: 1rem;
    background-color: #f1f8f5; /* Едва зеленоватый фон для контраста */
    overflow: hidden; /* Убираем прокрутку */
}

/* Нижняя панель (footer) */
.footer {
    grid-area: footer;
    background: #3aa546;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
}
.footer-text {
    margin: 0;
    font-size: 0.85rem;
}

/* Анимации */
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

<template>
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Menu</h2>
        </div>
        <nav class="sidebar-menu">
            <button
                v-for="item in menuItems"
                :key="item.name"
                :class="{'active': isActive(item.route)}"
                @click="navigateTo(item.route)"
            >
                <span class="icon" :class="item.icon"></span>
                <span class="label">{{ item.name }}</span>
            </button>
        </nav>
    </aside>
</template>

<script>
export default {
    name: "Sidebar",
    data() {
        return {
            menuItems: [
                { name: "Dashboard", route: "/dashboard", icon: "fas fa-tachometer-alt" },
                { name: "Timer", route: "/timer", icon: "fas fa-clock" },
                { name: "Admin Panel", route: "/admin", icon: "fas fa-cogs" },
            ],
        };
    },
    methods: {
        navigateTo(route) {
            this.$router.push(route);
        },
        isActive(route) {
            return this.$route.path === route;
        },
    },
};
</script>

<style scoped>
.sidebar {
    width: 250px;
    background: linear-gradient(145deg, #1e1e1e, #292929);
    padding: 1rem;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    transition: transform 0.3s ease-in-out;
}

.sidebar-header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.sidebar-header h2 {
    font-size: 1.8rem;
    color: #ff4545;
    font-weight: bold;
    text-transform: uppercase;
}

.sidebar-menu {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.sidebar-menu button {
    display: flex;
    align-items: center;
    background: #2d2d2d;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    color: #fff;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
    border: none;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.sidebar-menu button:hover {
    background: #ff4545;
    transform: translateX(10px);
    box-shadow: 0 4px 8px rgba(255, 69, 69, 0.5);
}

.sidebar-menu button.active {
    background: #ff4545;
    color: #fff;
    font-weight: bold;
    box-shadow: 0 4px 8px rgba(255, 69, 69, 0.5);
}

.sidebar-menu .icon {
    margin-right: 0.75rem;
    font-size: 1.2rem;
    color: inherit;
}

.sidebar-menu .label {
    font-size: 1.1rem;
    font-weight: 500;
    color: inherit;
}
</style>

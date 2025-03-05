<template>
    <div class="profile-page">
        <h1>Мой Профиль</h1>
        <div class="profile-card">
            <img
                class="avatar"
                src="https://i.pravatar.cc/150?u=profile"
                alt="Avatar"
            />
            <h2>Тестовый пользователь</h2>
            <p>Роль: User</p>
            <p>Логин: test_user</p>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ProfilePage'
}
</script>

<style scoped>
.profile-page {
    padding: 1rem;
    background: #1f1f1f;
    color: #fff;
    text-align: center;
}

.profile-card {
    background: #2d2d2d;
    padding: 1.5rem;
    border-radius: 8px;
    max-width: 300px;
    margin: 1rem auto;
}

.avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 1rem;
}

.profile-card h2 {
    color: #ffcf33;
    margin: 0.5rem 0;
}

.profile-card p {
    margin: 0.25rem 0;
}
</style>

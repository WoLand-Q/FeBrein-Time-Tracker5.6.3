<template>
    <div class="dashboard-container animate__animated animate__fadeIn">

        <h2 class="dashboard-title">Admin Dashboard</h2>
        <div class="dashboard-cards">
            <div class="card animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
                <h3>Total Users</h3>
                <p>{{ stats.totalUsers }}</p>
            </div>
            <div class="card animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                <h3>Active Users</h3>
                <p>{{ stats.activeUsers }}</p>
            </div>
            <div class="card animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
                <h3>On Break</h3>
                <p>{{ stats.onBreak }}</p>
            </div>
        </div>
        <div class="chart-section">
            <h3>User Activity</h3>
            <Chart :data="chartData" />
        </div>
    </div>
</template>

<script>
import Chart from "../components/Chart.vue";
export default {
    name: "Dashboard",
    components: {Chart},
    data() {
        return {
            stats: {
                totalUsers: 100,
                activeUsers: 75,
                onBreak: 10,
            },
            chartData: {
                labels: ["Working", "On Break", "Offline"],
                datasets: [
                    {
                        data: [75, 10, 15],
                        backgroundColor: ["#4caf50", "#ffa000", "#f44336"],
                    },
                ],
            },
        };
    },
};

</script>

<style scoped>
.dashboard-container {
    padding: 2rem;
    background: #1f1f1f;
    color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
}

.dashboard-title {
    font-size: 2rem;
    margin-bottom: 1rem;
    text-align: center;
    color: #ff4545;
}

.dashboard-cards {
    display: flex;
    justify-content: space-between;
    margin-bottom: 2rem;
}

.card {
    flex: 1;
    margin: 0 0.5rem;
    padding: 1.5rem;
    background: #2d2d2d;
    text-align: center;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    transition: transform 0.2s, box-shadow 0.3s;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.5);
}

.card h3 {
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
    color: #ff4545;
}

.card p {
    font-size: 2rem;
    font-weight: bold;
}

.chart-section {
    margin-top: 2rem;
    text-align: center;
}

.chart-section h3 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
    color: #ff4545;
}
</style>

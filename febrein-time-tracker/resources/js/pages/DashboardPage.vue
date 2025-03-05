<template>
    <div class="dashboard">
        <h1 class="dashboard-title">Рабочее время</h1>
        <p class="dashboard-subtitle">Сводка за {{ periodText }}</p>
        <div class="filter-row">
            <label>Старт:</label>
            <input type="date" v-model="selectedStartDate" />
            <label>Окончание:</label>
            <input type="date" v-model="selectedEndDate" />
            <button class="btn btn-apply" @click="applyFilter">Применить</button>
        </div>

        <div class="stats-cards" v-if="dashboardData">
            <div class="stat-card" style="animation-delay: 0.0s;">
                <h3>Всего рабочих часов</h3>
                <p>{{ totalWorkHours }} ч.</p>
            </div>
            <div class="stat-card" style="animation-delay: 0.1s;">
                <h3>Всего перерывов</h3>
                <p>{{ totalBreakHours }} ч.</p>
            </div>
            <div class="stat-card" style="animation-delay: 0.2s;">
                <h3>Период</h3>
                <p>{{ startDate }} - {{ endDate }}</p>
            </div>
            <div class="stat-card" style="animation-delay: 0.3s;">
                <h3>Средняя выработка</h3>
                <p>{{ averageWorkPerDay }} ч./день</p>
            </div>
        </div>

        <div class="flex-row" v-if="dashboardData">
            <div class="chart-card">
                <h3>Work vs Break (Общее соотношение)</h3>
                <img
                    :src="pieChartUrl"
                    alt="Pie Chart"
                    class="chart-img"
                    v-if="pieChartUrl"
                />
                <div v-else class="no-data">Нет данных для графика</div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'DashboardPage',
    data() {
        return {
            dashboardData: null,
            selectedStartDate: '',
            selectedEndDate: '',
        };
    },
    computed: {
        totalWorkHours() {
            if (!this.dashboardData) return '0.00';
            const m = this.dashboardData.total_work_minutes || 0;
            return (m / 60).toFixed(2);
        },
        totalBreakHours() {
            if (!this.dashboardData) return '0.00';
            const m = this.dashboardData.total_break_minutes || 0;
            return (m / 60).toFixed(2);
        },
        startDate() {
            return this.dashboardData?.start_date || '';
        },
        endDate() {
            return this.dashboardData?.end_date || '';
        },
        periodText() {
            if (!this.startDate && !this.endDate) return 'все время';
            return `${this.startDate} по ${this.endDate}`;
        },
        averageWorkPerDay() {
            if (!this.dashboardData) return '0.00';
            const total = this.dashboardData.total_work_minutes || 0;
            if (this.startDate && this.endDate) {
                const sd = new Date(this.startDate);
                const ed = new Date(this.endDate);
                let days = (ed - sd) / (1000 * 60 * 60 * 24);
                if (days < 1) days = 1;
                const hours = (total / 60) / days;
                return hours.toFixed(2);
            }
            return '0.00';
        },
        pieChartUrl() {
            if (!this.dashboardData) return '';
            const w = this.dashboardData.total_work_minutes || 0;
            const b = this.dashboardData.total_break_minutes || 0;
            if (w === 0 && b === 0) return '';
            const chartConfig = {
                type: 'pie',
                data: {
                    labels: ['Work', 'Break'],
                    datasets: [
                        { data: [w, b], backgroundColor: ['#4caf50', '#f44336'] }
                    ]
                },
                options: {
                    title: { display: true, text: 'Общее соотношение' }
                }
            };
            return `https://quickchart.io/chart?c=${encodeURIComponent(
                JSON.stringify(chartConfig)
            )}`;
        },
    },
    methods: {
        async applyFilter() {
            try {
                const params = {};
                if (this.selectedStartDate) params.start_date = this.selectedStartDate;
                if (this.selectedEndDate) params.end_date = this.selectedEndDate;

                const res = await axios.get('/api/admin/dashboard', { params });
                this.dashboardData = res.data;
            } catch (e) {
                console.error(e);
            }
        }
    },
    mounted() {
        this.applyFilter();
    }
};
</script>

<style scoped>
/* Растягиваем контейнер Dashboard на весь доступный экран внутри main-content */
.dashboard {
    width: 100%;
    height: 100%;
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    background: #eef5f3; /* Установите желаемый цвет фона */
    display: flex;
    flex-direction: column;
    overflow: hidden; /* Убираем прокрутку */
}

/* Заголовки */
.dashboard-title {
    font-size: 1.8rem;
    color: #333;
    margin: 1rem 0 0.5rem 0;
    font-weight: 600;
}

.dashboard-subtitle {
    font-size: 1rem;
    color: #666;
    margin-bottom: 1.5rem;
}

/* Фильтр */
.filter-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.filter-row input[type="date"] {
    background: #fff;
    border: 1px solid #aaa;
    color: #333;
    padding: 0.4rem 0.7rem;
    border-radius: 4px;
}

.btn-apply {
    background-color: #4caf50;
    color: #fff;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
}

/* Статистика */
.stats-cards {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 2rem;
    flex: 1; /* Позволяет блокам растягиваться */
}

.stat-card {
    flex: 1;
    min-width: 220px;
    background: #fff;
    color: #333;
    border-radius: 8px;
    padding: 1rem;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    animation: fadeInUp 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-3px);
}

.stat-card h3 {
    font-size: 1rem;
    margin-bottom: 0.5rem;
    color: #555;
}

.stat-card p {
    font-size: 1.4rem;
    font-weight: bold;
    margin: 0;
    color: #000;
}

/* Графики */
.flex-row {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 2rem;
}

.chart-card {
    flex: 1;
    min-width: 300px;
    background: #fff;
    color: #333;
    border-radius: 8px;
    padding: 1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    animation: fadeInUp 0.4s ease;
}

.chart-card h3 {
    margin-bottom: 1rem;
    color: #555;
}

.chart-img {
    width: 100%;
    border-radius: 4px;
    background: #fff;
    padding: 0.5rem;
    border: 1px solid #ccc;
}

.no-data {
    color: #999;
    font-size: 0.9rem;
    margin-top: 0.5rem;
    text-align: center;
}

/* Анимации */
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

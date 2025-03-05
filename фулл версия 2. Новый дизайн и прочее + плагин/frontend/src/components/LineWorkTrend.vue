<template>
  <div class="chart-wrapper">
    <LineChart :chart-data="chartData" :chart-options="chartOptions" />
  </div>
</template>

<script>
import { defineComponent, computed } from 'vue'
import { Chart as ChartJS, Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale } from 'chart.js'
import { Line as LineChart } from 'vue-chartjs'

// Регистрируем модули Chart.js
ChartJS.register(
    Title, Tooltip, Legend,
    LineElement, PointElement,
    CategoryScale, LinearScale
)

// Функция для формата минут -> "ЧЧ:ММ"
function formatHM(totalMinutes) {
  const h = Math.floor(totalMinutes / 60)
  const m = totalMinutes % 60
  const mm = m < 10 ? '0' + m : m
  return `${h}:${mm}`
}

export default defineComponent({
  name: 'LineWorkTrend',
  components: {
    LineChart
  },
  props: {

    trendData: {
      type: Array,
      default: () => []
    }
  },
  setup(props) {
    // Массив дат
    const labels = computed(() => props.trendData.map(d => d.date))

    // Минуты для оси Y
    const dataValues = computed(() => props.trendData.map(d => d.work_minutes))

    const chartData = computed(() => ({
      labels: labels.value,
      datasets: [
        {
          label: 'Робочий час (ЧЧ:ММ) за день',
          data: dataValues.value,
          borderColor: '#4caf50',
          backgroundColor: '#4caf50',
          fill: false,
          tension: 0.1
        }
      ]
    }))

    const chartOptions = {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: {
          ticks: {
            color: '#fff'
          },
          grid: {
            color: '#555',
            display: false
          }
        },
        y: {
          ticks: {
            color: '#fff',
            callback(value) {
              // value — число минут
              return formatHM(value)
            }
          },
          grid: {
            color: '#555'
          }
        }
      },
      plugins: {
        title: { display: false },
        legend: {
          labels: { color: '#fff' }
        },
        tooltip: {
          callbacks: {
            label(context) {
              const idx = context.dataIndex
              const minutes = context.parsed.y
              const userName = props.trendData[idx].user_name
              const userPart = userName ? `(${userName}) ` : ''
              return ` ${userPart}${formatHM(minutes)}`
            }
          }
        }
      }
    }

    return {chartData, chartOptions}
  }
})
</script>

<style scoped>
.chart-wrapper {
  width: 100%;
  height: 450px;
  position: relative;
}
</style>

<template>
  <div class="chart-wrapper">
    <PieChart :chart-data="chartData" :chart-options="chartOptions" />
  </div>
</template>

<script>
import { defineComponent, computed } from 'vue'
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'
import { Pie as PieChart } from 'vue-chartjs'

// Регистрируем то, что нужно для круговой диаграммы
ChartJS.register(ArcElement, Tooltip, Legend)

export default defineComponent({
  name: 'ChartStatus',
  components: {
    PieChart
  },
  props: {
    active: {
      type: Number,
      default: 0
    },
    onBreak: {
      type: Number,
      default: 0
    },
    offline: {
      type: Number,
      default: 0
    }
  },
  setup(props) {
    // Формируем данные для графика
    const chartData = computed(() => ({
      labels: ['Працюють', 'На перерві', 'Офлайн'],
      datasets: [
        {
          label: 'Статус користувачів',
          data: [props.active, props.onBreak, props.offline],
          backgroundColor: [
            '#4caf50',  // зелёный
            '#ffc107',  // жёлтый
            '#f44336'   // красный
          ],
          hoverOffset: 8
        }
      ]
    }))

    // Опции для круглого графика
    const chartOptions = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          labels: {
            color: '#fff' // текст легенды белым
          }
        }
      }
    }

    return { chartData, chartOptions }
  }
})
</script>

<style scoped>
.chart-wrapper {
  width: 100%;
  height: 300px;
  position: relative;
}
</style>

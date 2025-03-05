<template>
  <div class="chart-wrapper">
    <PieChart :chart-data="chartData" :chart-options="chartOptions" />
  </div>
</template>

<script>
import { defineComponent, computed } from 'vue'
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'
import { Pie as PieChart } from 'vue-chartjs'

// Функция форматирования: минуты -> "ЧЧ:ММ"
function formatHM(totalMinutes) {
  const h = Math.floor(totalMinutes / 60)
  const m = totalMinutes % 60
  // Добавим ведущий ноль для минут
  const mm = m < 10 ? '0' + m : m
  return `${h}:${mm}`
}

ChartJS.register(ArcElement, Tooltip, Legend)

export default defineComponent({
  name: 'PieWorkBreak',
  components: {
    PieChart
  },
  props: {
    // Кол-во минут работы
    work: {
      type: Number,
      default: 0
    },
    // Кол-во минут перерыва
    break: {
      type: Number,
      default: 0
    }
  },
  setup(props) {
    // Формируем данные для пирога
    const chartData = computed(() => ({
      labels: ['Робота', 'Перерва'],
      datasets: [
        {
          label: 'Робота / Перерви',
          // Внутри храним минуты
          data: [props.work, props.break],
          backgroundColor: ['#4caf50', '#ffa000'],
          hoverOffset: 8
        }
      ]
    }))

    // Подключаем тултип, чтобы отображать часы:минуты
    const chartOptions = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          labels: {
            color: '#fff'
          }
        },
        tooltip: {
          callbacks: {
            label(context) {
              // context.parsed — число минут (для соответствующего сектора)
              // Если parsed не работает — используйте context.raw
              let minutes = context.parsed
              if (typeof minutes !== 'number') {
                minutes = context.raw
              }
              // Превращаем в "ЧЧ:ММ"
              return ` ${formatHM(minutes)}`
            }
          }
        }
      }
    }

    return {
      chartData,
      chartOptions
    }
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

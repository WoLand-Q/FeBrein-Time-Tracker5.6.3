<template>
  <div class="chart-container">
    <PieChart :chart-data="chartData" :chart-options="chartOptions" />
  </div>
</template>

<script>
import {defineComponent, computed} from "vue";
import {Chart as ChartJS, ArcElement, Tooltip, Legend} from "chart.js";
import {Pie as PieChart} from "vue-chartjs";

// Регистрируем компоненты Chart.js
ChartJS.register(ArcElement, Tooltip, Legend);

export default defineComponent({
  name: "ChartComponent",
  components: {PieChart},
  props: {
    data: {
      type: Object,
      required: true,
    },
  },
  setup(props) {
    // Формируем данные для графика на основе входных данных
    const chartData = computed(() => ({
      labels: props.data.labels,
      datasets: props.data.datasets,
    }));

    // Опции графика
    const chartOptions = {
      responsive: true,
      maintainAspectRatio: false,
    };

    return {chartData, chartOptions};
  },
});
</script>

<style scoped>
.chart-container {
  width: 400px;
  height: 400px;
  margin: 0 auto;
}

</style>

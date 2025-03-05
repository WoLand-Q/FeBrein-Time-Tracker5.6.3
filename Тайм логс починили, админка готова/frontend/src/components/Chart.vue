<template>
    <div class="bg-white p-4 shadow-md rounded">
        <h2 class="text-xl font-bold mb-2">Analytics</h2>
        <div>
            <img
                :src="chartUrl"
                alt="Chart"
                class="w-full"
            />
        </div>
    </div>
</template>

<script>
export default {
    name: "Chart",
    computed: {
        chartUrl() {
            const data = {
                type: "bar",
                data: {
                    labels: ["Work", "Break"],
                    datasets: [{ data: [50, 20], backgroundColor: ["#4caf50", "#f44336"] }],
                },
            };
            return `https://quickchart.io/chart?c=${encodeURIComponent(JSON.stringify(data))}`;
        },
    },
};
</script>

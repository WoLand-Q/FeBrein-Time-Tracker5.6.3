<template>
    <AdminLayout>
        <div class="p-4">
            <h1 class="text-3xl font-bold">User Timer</h1>
            <Timer />
            <div class="mt-8">
                <h2 class="text-2xl">Colleague Status</h2>
                <StatusTable />
            </div>
        </div>
    </AdminLayout>
</template>

<script>
import AdminLayout from "../layouts/AdminLayout.vue";
import Timer from "../components/Timer.vue";
import StatusTable from "../components/StatusTable.vue";

export default {
    name: "TimerPage",
    components: {AdminLayout, Timer, StatusTable},
};
</script>

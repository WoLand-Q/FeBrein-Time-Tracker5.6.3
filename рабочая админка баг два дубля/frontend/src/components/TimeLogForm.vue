<template>
  <form @submit.prevent="submitForm" class="space-y-4">
    <!-- User -->
    <div>
      <label class="block text-sm font-medium mb-1 text-gray-300">User</label>
      <select
          v-model="formData.user_id"
          class="w-full p-2 rounded bg-gray-700 border border-gray-600 text-white"
      >
        <option value="" disabled>-- Select user --</option>
        <option v-for="u in users" :key="u.id" :value="u.id">
          {{ u.name }} ({{ u.login }})
        </option>
      </select>
    </div>

    <!-- Event -->
    <div>
      <label class="block text-sm font-medium mb-1 text-gray-300">Event</label>
      <select
          v-model="formData.event_id"
          class="w-full p-2 rounded bg-gray-700 border border-gray-600 text-white"
      >
        <option value="" disabled>-- Select event --</option>
        <option v-for="ev in eventOptions" :key="ev.value" :value="ev.value">
          {{ ev.label }}
        </option>
      </select>
    </div>

    <!-- Acted At -->
    <div>
      <label class="block text-sm font-medium mb-1 text-gray-300">Acted At</label>
      <input
          type="datetime-local"
          class="w-full p-2 rounded bg-gray-700 border border-gray-600 text-white"
          v-model="localActedAt"
      />
    </div>

    <div class="flex justify-end space-x-2 mt-4">
      <BaseButton type="submit" variant="secondary">Save</BaseButton>
      <BaseButton type="button" variant="danger" @click="resetForm">Reset</BaseButton>
    </div>
  </form>
</template>

<script>
import BaseButton from "./BaseButton.vue";

export default {
  name: "TimeLogForm",
  components: { BaseButton },
  props: {
    formData: {
      type: Object,
      default: () => ({
        id: null,
        user_id: "",
        event_id: "",
        acted_at: "",
      }),
    },
    users: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      eventOptions: [
        { value: 1, label: "start" },
        { value: 2, label: "start_break" },
        { value: 3, label: "stop_break" },
        { value: 4, label: "stop" },
      ],
      localActedAt: "",
    };
  },
  watch: {
    formData: {
      handler(newVal) {
        this.updateLocalActedAt(newVal.acted_at);
      },
      immediate: true,
      deep: true,
    },
  },
  methods: {
    updateLocalActedAt(val) {
      if (!val) {
        this.localActedAt = "";
        return;
      }
      const date = new Date(val);
      if (!isNaN(date.getTime())) {
        const yyyy = date.getFullYear();
        const mm = String(date.getMonth() + 1).padStart(2, "0");
        const dd = String(date.getDate()).padStart(2, "0");
        const hh = String(date.getHours()).padStart(2, "0");
        const min = String(date.getMinutes()).padStart(2, "0");
        this.localActedAt = `${yyyy}-${mm}-${dd}T${hh}:${min}`;
      } else {
        this.localActedAt = val;
      }
    },
    submitForm() {
      // Преобразуем localActedAt в formData.acted_at
      if (this.localActedAt) {
        const d = new Date(this.localActedAt);
        if (!isNaN(d.getTime())) {
          const yyyy = d.getFullYear();
          const mm = String(d.getMonth() + 1).padStart(2, "0");
          const dd = String(d.getDate()).padStart(2, "0");
          const hh = String(d.getHours()).padStart(2, "0");
          const min = String(d.getMinutes()).padStart(2, "0");
          this.formData.acted_at = `${yyyy}-${mm}-${dd} ${hh}:${min}:00`;
        } else {
          this.formData.acted_at = this.localActedAt;
        }
      } else {
        this.formData.acted_at = "";
      }
      this.$emit("submit", this.formData);
    },
    resetForm() {
      this.$emit("reset");
    },
  },
};
</script>

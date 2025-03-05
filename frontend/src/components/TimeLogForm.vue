<template>
  <form @submit.prevent="submitForm" class="space-y-4">
    <!-- User -->
    <div>
      <label class="block text-sm font-medium mb-1 text-gray-300">User</label>
      <select
          v-model="formData.user_id"
          class="w-full p-2 rounded bg-gray-700 border border-gray-600 text-white"
          required
      >
        <option value="" disabled>-- Select user --</option>
        <option v-for="u in users" :key="u.id" :value="u.id">
          {{ u.name }} ({{ u.login }})
        </option>
      </select>
      <p v-if="errors.user_id" class="mt-1 text-sm text-red-600 animate__animated animate__fadeIn">
        {{ errors.user_id }}
      </p>
    </div>

    <!-- Event -->
    <div>
      <label class="block text-sm font-medium mb-1 text-gray-300">Event</label>
      <select
          v-model="formData.event_id"
          class="w-full p-2 rounded bg-gray-700 border border-gray-600 text-white"
          required
      >
        <option value="" disabled>-- Select event --</option>
        <option v-for="ev in eventOptions" :key="ev.value" :value="ev.value">
          {{ ev.label }}
        </option>
      </select>
      <p v-if="errors.event_id" class="mt-1 text-sm text-red-600 animate__animated animate__fadeIn">
        {{ errors.event_id }}
      </p>
    </div>

    <!-- Acted At -->
    <div>
      <label class="block text-sm font-medium mb-1 text-gray-300">Acted At</label>
      <input
          type="datetime-local"
          class="w-full p-2 rounded bg-gray-700 border border-gray-600 text-white"
          v-model="localActedAt"
          required
      />
      <p v-if="errors.acted_at" class="mt-1 text-sm text-red-600 animate__animated animate__fadeIn">
        {{ errors.acted_at }}
      </p>
    </div>

    <div class="flex justify-end space-x-2 mt-4">
      <BaseButton :disabled="isSubmitting" type="submit" variant="secondary">
        Save
      </BaseButton>
      <BaseButton type="button" variant="danger" @click="resetForm">
        Reset
      </BaseButton>
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
      errors: {},
      isSubmitting: false,
    };
  },
  watch: {
    // Когда formData.acted_at изменяется (например, при редактировании), переводим в localActedAt
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
      // val - это Unix timestamp (секунды)
      const date = new Date(val * 1000);
      if (!isNaN(date.getTime())) {
        const yyyy = date.getFullYear();
        const mm = String(date.getMonth() + 1).padStart(2, "0");
        const dd = String(date.getDate()).padStart(2, "0");
        const hh = String(date.getHours()).padStart(2, "0");
        const min = String(date.getMinutes()).padStart(2, "0");
        this.localActedAt = `${yyyy}-${mm}-${dd}T${hh}:${min}`;
      } else {
        this.localActedAt = "";
      }
    },
    submitForm() {
      if (this.isSubmitting) {
        console.log("Prevent double submit in TimeLogForm");
        return;
      }
      this.isSubmitting = true;
      this.errors = {};

      // Простая валидация
      if (!this.formData.user_id) {
        this.errors.user_id = "User is required.";
      }
      if (!this.formData.event_id) {
        this.errors.event_id = "Event is required.";
      }
      if (!this.localActedAt) {
        this.errors.acted_at = "Acted At is required.";
      } else {
        const d = new Date(this.localActedAt);
        if (!isNaN(d.getTime())) {
          // Конвертируем в Unix-секунды
          this.formData.acted_at = Math.floor(d.getTime() / 1000);
        } else {
          this.errors.acted_at = "Invalid date format.";
        }
      }

      if (Object.keys(this.errors).length === 0) {
        this.$emit("submit", { ...this.formData });
      }
      this.isSubmitting = false;
    },
    resetForm() {
      this.$emit("reset");
      this.errors = {};
      this.localActedAt = "";
      // Сбрасываем локально
      this.formData = {
        id: null,
        user_id: "",
        event_id: "",
        acted_at: "",
      };
    },
  },
};
</script>

<style scoped>
.animate__animated {
  animation-duration: 0.5s;
  animation-fill-mode: both;
}

.animate__fadeIn {
  animation-name: fadeIn;
}
@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
</style>

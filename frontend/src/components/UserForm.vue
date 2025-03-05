<template>
  <form @submit.prevent="onSubmit" class="space-y-6">
    <!-- Name -->
    <div>
      <label class="block text-sm font-medium mb-1 text-gray-300">Name</label>
      <input
          v-model="formData.name"
          type="text"
          :disabled="isSubmitting"
          class="w-full p-2 rounded bg-gray-700 border border-gray-600 text-white"
          placeholder="Enter user name"
      />
      <p v-if="errors.name" class="mt-1 text-sm text-red-600 animate__fadeIn">
        {{ errors.name }}
      </p>
    </div>

    <!-- Login -->
    <div>
      <label class="block text-sm font-medium mb-1 text-gray-300">Login</label>
      <input
          v-model="formData.login"
          type="text"
          :disabled="isSubmitting"
          class="w-full p-2 rounded bg-gray-700 border border-gray-600 text-white"
          placeholder="Enter login"
      />
      <p v-if="errors.login" class="mt-1 text-sm text-red-600 animate__fadeIn">
        {{ errors.login }}
      </p>
    </div>

    <!-- Password -->
    <div>
      <label class="block text-sm font-medium mb-1 text-gray-300">Password</label>
      <input
          v-model="formData.password"
          type="password"
          :disabled="isSubmitting"
          class="w-full p-2 rounded bg-gray-700 border border-gray-600 text-white"
          placeholder="Leave empty if not changing"
      />
      <p v-if="errors.password" class="mt-1 text-sm text-red-600 animate__fadeIn">
        {{ errors.password }}
      </p>
    </div>

    <!-- Role -->
    <div>
      <label class="block text-sm font-medium mb-1 text-gray-300">Role</label>
      <select
          v-model="formData.role"
          :disabled="isSubmitting"
          class="w-full p-2 rounded bg-gray-700 border border-gray-600 text-white"
      >
        <option value="admin">Admin</option>
        <option value="user">User</option>
      </select>
      <p v-if="errors.role" class="mt-1 text-sm text-red-600 animate__fadeIn">
        {{ errors.role }}
      </p>
    </div>

    <!-- Group -->
    <div>
      <label class="block text-sm font-medium mb-1 text-gray-300">Group</label>
      <select
          v-model="formData.group_id"
          :disabled="isSubmitting"
          class="w-full p-2 rounded bg-gray-700 border border-gray-600 text-white"
      >
        <option value="" disabled>Select group</option>
        <option
            v-for="g in groups"
            :key="g.id"
            :value="g.id"
        >
          {{ g.group_name }}
        </option>
      </select>
      <p v-if="errors.group_id" class="mt-1 text-sm text-red-600 animate__fadeIn">
        {{ errors.group_id }}
      </p>
    </div>

    <!-- Buttons -->
    <div class="flex justify-end space-x-2 mt-4">
      <BaseButton
          :disabled="isSubmitting"
          type="submit"
          variant="secondary"
      >
        Save
      </BaseButton>
      <BaseButton
          type="button"
          variant="danger"
          :disabled="isSubmitting"
          @click="resetForm"
      >
        Reset
      </BaseButton>
    </div>
  </form>
</template>

<script>
import BaseButton from "./BaseButton.vue";

export default {
  name: "UserForm",
  components: { BaseButton },
  props: {
    formData: {
      type: Object,
      default: () => ({
        name: "",
        login: "",
        password: "",
        role: "user",
        group_id: null,
      }),
    },
    groups: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      errors: {},
      isSubmitting: false, // флаг для предотвращения двойного сабмита
    };
  },
  methods: {
    onSubmit() {
      if (this.isSubmitting) {
        console.log("Prevent double submit in UserForm");
        return;
      }
      this.isSubmitting = true;

      // Минимальная валидация
      this.errors = {};
      if (!this.formData.name) {
        this.errors.name = "Name is required.";
      }
      if (!this.formData.login) {
        this.errors.login = "Login is required.";
      }

      if (Object.keys(this.errors).length === 0) {
        // Передаём onDone, чтобы родитель мог разблокировать
        this.$emit("submit", {
          ...this.formData,
          onDone: () => {
            this.isSubmitting = false;
          },
        });
      } else {
        this.isSubmitting = false;
      }
    },
    resetForm() {
      this.errors = {};
      this.isSubmitting = false;
      this.$emit("reset");
    },
  },
};
</script>

<style scoped>
.animate__fadeIn {
  animation: fadeIn 0.5s both;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to   { opacity: 1; }
}
</style>

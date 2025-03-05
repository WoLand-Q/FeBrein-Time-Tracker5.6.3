<template>
  <form @submit.prevent="submitForm" class="space-y-4">
    <!-- Name -->
    <div>
      <label class="block text-sm font-medium mb-1 text-gray-300">Name</label>
      <input
          v-model="formData.name"
          type="text"
          class="w-full p-2 rounded bg-gray-700 border border-gray-600 text-white"
          placeholder="Enter user name"
      />
      <p v-if="errors.name" class="mt-1 text-sm text-red-600 animate__animated animate__fadeIn">
        {{ errors.name }}
      </p>
    </div>

    <!-- Login -->
    <div>
      <label class="block text-sm font-medium mb-1 text-gray-300">Login</label>
      <input
          v-model="formData.login"
          type="text"
          class="w-full p-2 rounded bg-gray-700 border border-gray-600 text-white"
          placeholder="Enter login"
      />
      <p v-if="errors.login" class="mt-1 text-sm text-red-600 animate__animated animate__fadeIn">
        {{ errors.login }}
      </p>
    </div>

    <!-- Password -->
    <div>
      <label class="block text-sm font-medium mb-1 text-gray-300">Password</label>
      <input
          v-model="formData.password"
          type="password"
          class="w-full p-2 rounded bg-gray-700 border border-gray-600 text-white"
          placeholder="Leave empty if not changing"
      />
      <p v-if="errors.password" class="mt-1 text-sm text-red-600 animate__animated animate__fadeIn">
        {{ errors.password }}
      </p>
    </div>

    <!-- Role -->
    <div>
      <label class="block text-sm font-medium mb-1 text-gray-300">Role</label>
      <select
          v-model="formData.role"
          class="w-full p-2 rounded bg-gray-700 border border-gray-600 text-white"
      >
        <option value="admin">Admin</option>
        <option value="user">User</option>
      </select>
      <p v-if="errors.role" class="mt-1 text-sm text-red-600 animate__animated animate__fadeIn">
        {{ errors.role }}
      </p>
    </div>

    <!-- Group -->
    <div>
      <label class="block text-sm font-medium mb-1 text-gray-300">Group</label>
      <select
          v-model="formData.group_id"
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
      <p v-if="errors.group_id" class="mt-1 text-sm text-red-600 animate__animated animate__fadeIn">
        {{ errors.group_id }}
      </p>
    </div>

    <!-- Buttons -->
    <div class="flex justify-end space-x-2 mt-4">
      <!-- type="submit" => только form @submit.prevent -->
      <BaseButton type="submit" variant="secondary">Save</BaseButton>
      <!-- type="button" => не триггерит submit -->
      <BaseButton type="button" variant="danger" @click="resetForm">Reset</BaseButton>
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
    };
  },
  methods: {
    submitForm() {
      this.errors = {};
      if (!this.formData.name) {
        this.errors.name = "Name is required.";
      }
      if (!this.formData.login) {
        this.errors.login = "Login is required.";
      }
      // ...доп. проверки, если нужно

      if (Object.keys(this.errors).length === 0) {
        this.$emit("submit", this.formData);
      }
    },
    resetForm() {
      this.$emit("reset");
    },
  },
};
</script>

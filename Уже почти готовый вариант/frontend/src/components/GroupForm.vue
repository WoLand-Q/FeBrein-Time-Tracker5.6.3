<template>
  <form @submit.prevent="submitForm" class="space-y-6">
    <div>
      <label for="group_name" class="block text-sm font-medium text-gray-300">Group Name</label>
      <input
          v-model="formData.group_name"
          id="group_name"
          type="text"
          class="mt-1 block w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md shadow-sm
                 focus:outline-none focus:ring-secondary focus:border-secondary text-white
                 placeholder-gray-400"
          placeholder="Enter group name"
          required
      />
      <p v-if="errors.group_name" class="mt-1 text-sm text-red-600 animate__fadeIn">
        {{ errors.group_name }}
      </p>
    </div>

    <div class="flex justify-end space-x-2">
      <BaseButton type="submit" variant="secondary">Save</BaseButton>
      <BaseButton type="button" variant="danger" @click="resetForm">Reset</BaseButton>
    </div>
  </form>
</template>

<script>
import BaseButton from "./BaseButton.vue";

export default {
  name: "GroupForm",
  components: { BaseButton },
  props: {
    formData: {
      type: Object,
      default: () => ({
        group_name: "",
      }),
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
      if (!this.formData.group_name) {
        this.errors.group_name = "Group name is required.";
      }
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

<style scoped>
.animate__fadeIn {
  animation: fadeIn 0.5s both;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to   { opacity: 1; }
}
</style>

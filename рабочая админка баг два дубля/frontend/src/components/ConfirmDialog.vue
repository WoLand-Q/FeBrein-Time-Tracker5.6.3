<!-- components/ConfirmDialog.vue -->
<template>
  <transition name="fade">
    <div
        v-if="visible"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50"
    >
      <div class="bg-gray-800 text-white p-6 rounded-lg shadow-xl w-full max-w-sm">
        <h3 class="text-xl font-bold mb-2">{{ title }}</h3>
        <p class="mb-4">{{ message }}</p>
        <div class="flex justify-end space-x-2">
          <BaseButton variant="danger" @click="handleCancel">Cancel</BaseButton>
          <BaseButton variant="secondary" @click="handleOk">OK</BaseButton>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
import BaseButton from "./BaseButton.vue";

export default {
  name: "ConfirmDialog",
  components: { BaseButton },
  props: {
    visible: { type: Boolean, default: false },
    title: { type: String, default: "Confirm" },
    message: { type: String, default: "Are you sure?" },
  },
  emits: ["confirm", "cancel"],
  methods: {
    handleOk() {
      this.$emit("confirm");
    },
    handleCancel() {
      this.$emit("cancel");
    },
  },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter,
.fade-leave-to {
  opacity: 0;
}
</style>

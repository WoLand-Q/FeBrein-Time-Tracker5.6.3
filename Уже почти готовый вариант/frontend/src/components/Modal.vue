<template>
  <transition name="modal-fade">
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        v-if="isVisible"
    >
      <div class="relative bg-gray-800 text-white p-6 rounded-lg shadow-xl w-full max-w-lg">
        <div class="mb-4">
          <slot name="title"></slot>
        </div>
        <div>
          <slot name="body"></slot>
        </div>
        <div class="mt-6 flex justify-end space-x-2">
          <slot name="actions"></slot>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
export default {
  name: "Modal",
  props: {
    isVisible: { type: Boolean, default: false },
  },
  mounted() {
    document.addEventListener("keydown", this.handleEscape);
  },
  beforeUnmount() {
    document.removeEventListener("keydown", this.handleEscape);
  },
  methods: {
    handleEscape(e) {
      if (e.key === "Escape" && this.isVisible) {
        this.$emit("close");
      }
    },
  },
};
</script>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s;
}

.modal-fade-enter,
.modal-fade-leave-to {
  opacity: 0;
}
</style>

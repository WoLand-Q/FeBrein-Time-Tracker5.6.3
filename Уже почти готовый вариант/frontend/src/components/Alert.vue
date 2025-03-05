<template>
  <transition name="fade">
    <div
        v-if="show"
        :class="alertClasses"
        class="relative p-4 mb-4 rounded-lg shadow-lg max-w-xl mx-auto"
        role="alert"
    >
      <strong class="font-bold block mb-1">{{ title }}</strong>
      <span>{{ message }}</span>
    </div>
  </transition>
</template>

<script>
export default {
  name: "Alert",
  props: {
    type: {type: String, default: "success"},
    title: {type: String, default: ""},
    message: {type: String, default: ""},
    show: {type: Boolean, default: false},
  },
  computed: {
    alertClasses() {
      const colorMap = {
        success: "bg-green-600 text-white",
        error: "bg-red-600 text-white",
        warning: "bg-yellow-500 text-gray-800",
        info: "bg-blue-600 text-white",
      };
      return colorMap[this.type] || colorMap.info;
    },
  },
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter, .fade-leave-to {
  opacity: 0;
}
</style>

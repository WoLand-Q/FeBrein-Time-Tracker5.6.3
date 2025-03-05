<template>
  <button
      :type="type"
      :class="buttonClasses"
      :disabled="isDisabled"
  >
    <slot />
  </button>
</template>

<script>
export default {
  name: "BaseButton",
  props: {
    type: {type: String, default: "button"},
    variant: {type: String, default: "primary"},
    size: {type: String, default: "md"},
    isDisabled: {type: Boolean, default: false},
  },
  computed: {
    buttonClasses() {
      const base = "inline-flex items-center justify-center font-semibold rounded px-4 py-2";
      const variants = {
        primary: "bg-blue-600 hover:bg-blue-700 text-white",
        secondary: "bg-gray-600 hover:bg-gray-700 text-white",
        danger: "bg-red-600 hover:bg-red-700 text-white",
        warning: "bg-yellow-500 hover:bg-yellow-600 text-gray-900",
      };
      return [
        base,
        variants[this.variant] || variants.primary,
        this.isDisabled ? "opacity-50 cursor-not-allowed" : "",
      ].join(" ");
    },
  },
};
</script>

<style scoped>
/* При желании стили */
</style>

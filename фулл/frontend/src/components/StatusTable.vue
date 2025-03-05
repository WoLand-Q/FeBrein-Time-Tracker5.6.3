<template>
  <div class="overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead>
      <tr class="bg-gray-700 text-white uppercase text-xs">
        <th class="px-4 py-2 text-left">Користувач</th>
        <th class="px-4 py-2 text-left">Статус</th>
        <th class="px-4 py-2 text-left">Відпрацьовано (хв)</th>
        <th class="px-4 py-2 text-left">Перерва (хв)</th>
      </tr>
      </thead>
      <tbody>
      <tr
          v-for="user in users"
          :key="user.id"
          class="border-b border-gray-700 hover:bg-gray-700"
      >
        <td class="px-4 py-2">
          {{ user.name }}
        </td>
        <td class="px-4 py-2">
            <span v-if="user.currentStatus === 'працює'" class="text-green-400">
              Працює
            </span>
          <span v-else-if="user.currentStatus === 'перерва'" class="text-yellow-400">
              Перерва
            </span>
          <span v-else-if="user.currentStatus === 'завершено'" class="text-red-400">
              Завершено
            </span>
          <span v-else>
              {{ user.currentStatus }}
            </span>
        </td>
        <td class="px-4 py-2">
          {{ user.workedMins }} хв
        </td>
        <td class="px-4 py-2">
          {{ user.breakMins }} хв
        </td>
      </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: "StatusTable",
  props: {
    users: {
      type: Array,
      default: () => [],
    },
  },
};
</script>

<style scoped>
/* При желании */
</style>

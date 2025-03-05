<template>
  <div class="overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead>
      <tr class="bg-gray-700 text-white uppercase text-xs">
        <th v-for="col in columns" :key="col"
            class="px-4 py-2 text-left"
            :class="col === 'Actions' ? 'w-32 text-right' : ''"
        >
          {{ col }}
        </th>
      </tr>
      </thead>

      <tbody>
      <tr
          v-for="item in items"
          :key="item.id"
          class="border-b border-gray-700 hover:bg-gray-700"
      >
        <td
            v-for="col in columns"
            :key="col"
            class="px-4 py-2 whitespace-nowrap"
        >
          <!-- USERS TABLE -->
          <template v-if="tableType === 'users'">
            <template v-if="col === 'Name'">
              {{ item.name || '-' }}
            </template>
            <template v-else-if="col === 'Login'">
              {{ item.login || '-' }}
            </template>
            <template v-else-if="col === 'Role'">
              {{ item.role_name || (item.role_id === 1 ? 'Admin' : 'User') }}
            </template>
            <template v-else-if="col === 'Group'">
              {{ item.group_name || getGroupName(item.group_id) }}
            </template>
            <template v-else-if="col === 'Actions'">
              <td class="px-4 py-2 whitespace-nowrap text-right">
                <div class="flex justify-end space-x-2 items-center">
                  <button
                      class="px-3 py-1 rounded bg-yellow-500 text-white hover:bg-yellow-600"
                      @click="$emit('edit', item)"
                  >
                    Edit
                  </button>
                  <button
                      class="px-3 py-1 rounded bg-red-600 text-white hover:bg-red-700"
                      @click="$emit('delete', item.id)"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </template>

            <template v-else>
              {{ item[col] || '-' }}
            </template>
          </template>

          <!-- GROUPS TABLE -->
          <template v-else-if="tableType === 'groups'">
            <template v-if="col === 'Name'">
              {{ item.group_name || '-' }}
            </template>
            <template v-else-if="col === 'Actions'">
              <td class="px-4 py-2 whitespace-nowrap text-right">
                <div class="flex justify-end space-x-2 items-center">
                  <button
                      class="px-3 py-1 rounded bg-yellow-500 text-white hover:bg-yellow-600"
                      @click="$emit('edit', item)"
                  >
                    Edit
                  </button>
                  <button
                      class="px-3 py-1 rounded bg-red-600 text-white hover:bg-red-700"
                      @click="$emit('delete', item.id)"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </template>

            <template v-else>
              {{ item[col] || '-' }}
            </template>
          </template>

          <!-- TIMELOGS TABLE -->
          <template v-else-if="tableType === 'timelogs'">
            <template v-if="col === 'User'">
              {{ getUserLogin(item.user_id) }}
            </template>
            <template v-else-if="col === 'Event'">
              {{ item.even_name ? item.even_name : 'Event #' + item.event_id }}
            </template>
            <template v-else-if="col === 'Date'">
              {{ item.acted_at || '-' }}
            </template>
            <template v-else-if="col === 'Actions'">
              <td class="px-4 py-2 whitespace-nowrap text-right">
                <div class="flex justify-end space-x-2 items-center">
                  <button
                      class="px-3 py-1 rounded bg-yellow-500 text-white hover:bg-yellow-600"
                      @click="$emit('edit', item)"
                  >
                    Edit
                  </button>
                  <button
                      class="px-3 py-1 rounded bg-red-600 text-white hover:bg-red-700"
                      @click="$emit('delete', item.id)"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </template>

            <template v-else>
              {{ item[col] || '-' }}
            </template>
          </template>

          <!-- Если не попали в users/groups/timelogs -->
          <template v-else>
            {{ item[col] || '-' }}
          </template>
        </td>
      </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: "TableComponent",
  props: {
    items: { type: Array, required: true },
    columns: { type: Array, required: true },
    tableType: { type: String, default: "" },
    users: { type: Array, default: () => [] },
    groups: { type: Array, default: () => [] },
  },
  methods: {
    getUserLogin(userId) {
      const user = this.users.find(u => u.id === userId);
      return user ? user.login : `User ID: ${userId}`;
    },
    getGroupName(groupId) {
      const grp = this.groups.find(g => g.id === groupId);
      return grp ? grp.group_name : `Group ID: ${groupId}`;
    },
  },
};
</script>

<style scoped>
/* При желании стили для таблицы */
</style>

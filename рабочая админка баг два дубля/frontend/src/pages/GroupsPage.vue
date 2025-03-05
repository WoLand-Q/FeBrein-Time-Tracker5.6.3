<template>
  <div>
    <h1>Групи</h1>

    <table>
      <tr>
        <th>id</th>
        <th>name</th>
      </tr>
      <tr v-for="group in groups" :key="group.id">
        <td>{{ group.id }}</td>
        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ group.group_name }}</td>
      </tr>
    </table>

  </div>
</template>

<script>
import api from "@/api";

export default {
  data() {
    return {
      groups: [] // Поле для збереження масиву груп
    };
  },
  mounted() {
    api.get('/api/groups')
        .then(response => {
          this.groups = response.data.data; // Зберігаємо отриманий масив груп у змінну
        })
        .catch(error => {
          console.error(error);
        });
  }
};
</script>

<style scoped>
/* Ваші стилі */
</style>

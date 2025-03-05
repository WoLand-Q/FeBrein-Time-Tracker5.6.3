<template>
  <div class="plugins-manager">
    <h1 class="title">Управление плагинами</h1>

    <div class="table-container">
      <table class="plugins-table">
        <thead>
        <tr>
          <th>ID</th>
          <th>Slug</th>
          <th>Name</th>
          <th>Enabled</th>
          <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="plg in plugins" :key="plg.id">
          <td>{{ plg.id }}</td>
          <td>{{ plg.slug }}</td>
          <td>{{ plg.name }}</td>
          <td>
              <span :class="['status-badge', plg.enabled ? 'enabled' : 'disabled']">
                {{ plg.enabled ? 'Да' : 'Нет' }}
              </span>
          </td>
          <td>
            <button class="btn-toggle" :class="{ off: !plg.enabled }" @click="togglePlugin(plg)">
              {{ plg.enabled ? 'Выключить' : 'Включить' }}
            </button>
            <button class="btn-delete" @click="deletePlugin(plg)">Удалить</button>
          </td>
        </tr>
        </tbody>
      </table>
    </div>

    <div class="upload-section">
      <h2>Загрузить плагин</h2>
      <form @submit.prevent="uploadPlugin">
        <input type="file" @change="onFileChange" accept=".zip" />
        <button type="submit">Загрузить</button>
      </form>
    </div>
  </div>
</template>

<script>
import api from '@/api'

export default {
  name: 'PluginsManager',
  data() {
    return {
      plugins: [],
      pluginFile: null
    }
  },
  async mounted() {
    await this.loadPlugins()
  },
  methods: {
    async loadPlugins() {
      try {
        const resp = await api.get('/api/admin/plugins')
        this.plugins = resp.data
      } catch (err) {
        console.error('Ошибка загрузки плагинов:', err)
      }
    },
    async togglePlugin(plg) {
      try {
        const newStatus = !plg.enabled
        await api.patch(`/api/admin/plugins/${plg.id}/${newStatus ? 'enable' : 'disable'}`)
        await this.loadPlugins()
      } catch (err) {
        console.error('Ошибка переключения плагина:', err)
      }
    },
    async deletePlugin(plg) {
      if (!confirm(`Удалить плагин "${plg.name}"?`)) return
      try {
        await api.delete(`/api/admin/plugins/${plg.id}`)
        await this.loadPlugins()
      } catch (err) {
        console.error('Ошибка удаления плагина:', err)
      }
    },
    onFileChange(e) {
      this.pluginFile = e.target.files[0]
    },
    async uploadPlugin() {
      if (!this.pluginFile) {
        alert('Выберите .zip файл!')
        return
      }
      try {
        const formData = new FormData()
        formData.append('plugin_file', this.pluginFile)

        await api.post('/api/admin/plugins/upload', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })

        alert('Плагин загружен!')
        await this.loadPlugins()
      } catch (err) {
        console.error('Ошибка загрузки плагина:', err)
        alert('Ошибка загрузки плагина')
      }
    }
  }
}
</script>


<style scoped>
/* Общий контейнер */
.plugins-manager {
  padding: 1rem;
  color: #fff;
}

/* Заголовок */
.title {
  margin-top: 0;
  margin-bottom: 1.5rem;
  font-size: 1.8rem;
  color: #ff4545;
  text-align: center;
}

/* Таблица */
.table-container {
  overflow-x: auto;
  margin-bottom: 2rem;
}

.plugins-table {
  width: 100%;
  border-collapse: collapse;
  background: #2d2d2d;
  border-radius: 8px;
  overflow: hidden;
}

.plugins-table th,
.plugins-table td {
  padding: 0.75rem 1rem;
  text-align: left;
  border-bottom: 1px solid #444;
}

.plugins-table thead tr {
  background: #1f1f1f;
}

.plugins-table th {
  color: #ff4545;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.85rem;
}

.plugins-table tbody tr:hover {
  background: #3b3b3b;
}

/* Статус-бейдж */
.status-badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-weight: 600;
  color: #fff;
  font-size: 0.8rem;
}

.enabled {
  background-color: #4caf50;
}

.disabled {
  background-color: #ff4545;
}

/* Кнопки */
.btn-toggle,
.btn-delete {
  padding: 0.5rem 0.75rem;
  margin-right: 0.5rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  color: #fff;
  font-weight: 600;
  transition: background-color 0.2s;
}

.btn-toggle {
  background-color: #4caf50;
}

.btn-toggle.off {
  background-color: #ff9800;
}

.btn-toggle:hover {
  background-color: #66bb6a;
}

.btn-toggle.off:hover {
  background-color: #ffa726;
}

.btn-delete {
  background-color: #ff4545;
}

.btn-delete:hover {
  background-color: #ff6f6f;
}

/* Секция загрузки плагина */
.upload-section {
  background: #1f1f1f;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 2rem;
  text-align: center;
}

.upload-section h2 {
  color: #ffcf33;
  margin-bottom: 1rem;
}

/* Форма добавления нового плагина */
.create-plugin-form {
  background: #1f1f1f;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 2rem;
}

.create-plugin-form h2 {
  margin-top: 0;
  color: #ffcf33;
  margin-bottom: 1rem;
  text-align: center;
}

.plugin-form {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
}

.form-row {
  display: flex;
  flex-direction: column;
  flex: 1 1 200px;
}

.form-row label {
  margin-bottom: 0.25rem;
  font-size: 0.9rem;
  color: #ff4545;
}

.form-row input {
  padding: 0.5rem;
  border: 1px solid #444;
  border-radius: 4px;
  background: #2d2d2d;
  color: #fff;
}

.btn-submit {
  background-color: #ff4545;
  color: #fff;
  border: none;
  padding: 0.6rem 1.2rem;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  align-self: flex-end;
  margin-top: 0.5rem;
}

.btn-submit:hover {
  background-color: #ff6f6f;
}
</style>

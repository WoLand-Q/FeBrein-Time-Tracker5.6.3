<template>
  <div class="upload-plugin">
    <h2>Загрузить плагин</h2>
    <form @submit.prevent="uploadPlugin">
      <input type="file" @change="onFileChange" accept=".zip" />
      <button type="submit">Загрузить</button>
    </form>
  </div>
</template>

<script>
import api from '@/api'

export default {
  data() {
    return {
      pluginFile: null
    }
  },
  methods: {
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
      } catch (err) {
        console.error(err)
        alert('Ошибка загрузки плагина')
      }
    }
  }
}
</script>

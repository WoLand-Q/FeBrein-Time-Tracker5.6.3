<template>
  <div class="changelog-page">
    <h1 class="page-title">ChangeLog</h1>

    <!-- Секция с текстом ChangeLog -->
    <div class="changelog-text" v-html="changelogContent"></div>

    <!-- Галерея изображений -->
    <div class="image-gallery">
      <h2>Галерея змін</h2>
      <div class="images-container">
        <div v-for="(img, index) in images" :key="index" class="image-item">
          <img
              :src="img"
              alt="ChangeLog Image"
              @click="openModal(img)"
          />
        </div>
      </div>
    </div>

    <!-- Модальное окно для просмотра фото на всю страницу -->
    <div
        class="modal-backdrop"
        v-if="selectedImage"
        @click.self="closeModal"
    >
      <div class="modal-content">
        <img :src="selectedImage" alt="Full View" />
        <button class="close-btn" @click="closeModal">Закрыть</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "ChangeLogPage",
  data() {
    return {
      changelogContent: `
        <h2>Версія 5.6.3</h2>
        <ul>
          <li>Поправлен и улучшен дашбоард для админа и юзера. Добавлены фильтры</li>
          <li>На странице Таймера была изменена таблица и улучшена логика</li>
          <li>Со стороны админ панели так же был изменен интерфейс, добавлены фильтры через поиск.</li>
          <li>Для бека была добавлена функциия для авто-фиксации на выход с работы</li>
          <li>Было изменено меню, теперь оно расположено по горизонтали, кнопка выхода заменена на иконку Выключения</li>
        </ul>
      `,
      images: [
        "/changelog/2.jpg",
        "/changelog/2.jpg",
        "/changelog/3.jpg"
      ],
      selectedImage: null
    };
  },
  methods: {
    openModal(img) {
      this.selectedImage = img;
    },
    closeModal() {
      this.selectedImage = null;
    }
  }
};
</script>

<style scoped>
.changelog-page {
  background: #1f1f1f;
  padding: 1.5rem;
  border-radius: 8px;
  color: #fff;
  box-shadow: 0 2px 6px rgba(0,0,0,0.4);
  max-width: 800px;
  margin: 1rem auto;
  position: relative;
}

.page-title {
  margin-top: 0;
  color: #ff4545;
  margin-bottom: 1rem;
  text-align: center;
}

/* Стили для текста ChangeLog */
.changelog-text {
  background: #2d2d2d;
  padding: 1rem;
  border-radius: 4px;
  margin-bottom: 1.5rem;
  line-height: 1.5;
}
.changelog-text h2 {
  color: #ffcf33;
  margin-bottom: 0.5rem;
}
.changelog-text ul {
  margin: 0 0 1rem 1.2rem;
  padding: 0;
  list-style: disc;
}

/* Галерея изображений */
.image-gallery {
  margin-top: 1rem;
}
.image-gallery h2 {
  color: #ffcf33;
  margin-bottom: 0.75rem;
  text-align: center;
}
.images-container {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  justify-content: center;
}
.image-item img {
  max-width: 200px;
  border-radius: 4px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.3);
  transition: transform 0.2s, box-shadow 0.2s;
  cursor: pointer;
}
.image-item img:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 8px rgba(0,0,0,0.5);
}

/* Модальное окно на всю страницу */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0,0,0,0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

/* Контент модального окна */
.modal-content {
  position: relative;
  max-width: 90%;
  max-height: 90%;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.modal-content img {
  max-width: 100%;
  max-height: 80vh;
  border-radius: 6px;
  object-fit: contain;
}

/* Кнопка закрытия */
.close-btn {
  margin-top: 1rem;
  background-color: #ff4545;
  color: #fff;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 500;
  transition: background-color 0.2s;
}
.close-btn:hover {
  background-color: #e83b3b;
}
</style>

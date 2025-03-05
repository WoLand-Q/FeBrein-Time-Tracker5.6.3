import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';

export default defineConfig({
    plugins: [vue()],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'src'),
            '@layouts': path.resolve(__dirname, 'src/layouts'),
            '@pages': path.resolve(__dirname, 'src/pages'),
            '@components': path.resolve(__dirname, 'src/components'),
            '@assets': path.resolve(__dirname, 'src/assets'),
        },
    },
    server: {
        host: '127.0.0.1', // Привязываем сервер к 127.0.0.1
        port: 3000, // Используем порт 3000
        open: true, // Автоматически открывать браузер
        strictPort: true, // Если порт 3000 занят, не использовать другой
        cors: true, // Включаем CORS для API-запросов
    },
});

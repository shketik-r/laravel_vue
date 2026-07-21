import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path'; // 1. Обязательно добавляем импорт модуля path

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/assets/styles/main.scss',
            ],
            refresh: true,
        }),
        vue(),
    ],

    server: {
        host: '0.0.0.0',
        hmr: {
            host: 'localhost',
        },
    },

    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
            // 2. Дописываем наши новые алиасы в список
            '@': path.resolve(__dirname, './resources/js'),
            '@styles': path.resolve(__dirname, './resources/assets/styles')
        },
    },
});

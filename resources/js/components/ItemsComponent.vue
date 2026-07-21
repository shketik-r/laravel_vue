<template>
    <div class="clients-list-container">
        <h3>Список последних клиентов</h3>

        <!-- 1. ПОКАЗЫВАЕМ НАШ МИГАЮЩИЙ СКЕЛЕТОН ВСЕ 10 СЕКУНД -->
        <div v-if="loading">
            <SkeletonRender />
        </div>

        <!-- 2. ПОКАЗЫВАЕМ ЗАГЛУШКУ, ЕСЛИ СПИСОК ПУСТ -->
        <div v-else-if="clients.length === 0" class="empty-text">
            Клиентов пока нет. Будьте первым!
        </div>

        <!-- 3. РЕНДЕРИМ НАСТОЯЩИЙ СПИСОК, КОГДА ДАННЫЕ ПРИШЛИ -->
        <ul v-else class="clients-list">
            <li v-for="client in clients" :key="client.id" class="client-item">
                <div class="client-info">
                    <span class="client-name">{{ client.name }}</span>
                    <span class="client-age">{{ client.age }} лет</span>
                </div>
                <span class="client-date">{{ formatDate(client.created_at) }}</span>
            </li>
        </ul>
    </div>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue';
// Импортируем функцию генерации скелетона
import { SkeletonComponent } from '../Skeleton';

// Создаем Vue-компонент скелетона прямо из нашей JS-функции
const SkeletonRender = SkeletonComponent('card-users', 10);

const clients = ref([]);
const loading = ref(true);
const showToast = inject('showToast');

const fetchClients = async () => {
    try {
        const response = await fetch('/api/clients');
        const result = await response.json();

        if (response.ok) {
            clients.value = result.data;
        } else {
            showToast('Не удалось загрузить список', 'error');
        }
    } catch (error) {
        console.error('Ошибка:', error);
    } finally {
        // Выключаем скелетон только после того, как fetch завершился
        loading.value = false;
    }
};

onMounted(() => {
    // Ваша задержка в 10 секунд (10000 мс)
    setTimeout(() => {
        fetchClients();
    }, 10000);

    window.addEventListener('refresh-clients-list', () => {
        loading.value = true; // Снова включаем скелетон при обновлении
        fetchClients();
    });
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('ru-RU', { day: '2-digit', month: 'short' });
};
</script>

<style scoped>
.clients-list-container { max-width: 500px; margin: 30px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
h3 { margin-top: 0; color: #1f2937; border-bottom: 2px solid #f3f4f6; padding-bottom: 10px; }
.loading-text, .empty-text { text-align: center; color: #9ca3af; padding: 20px 0; }
.clients-list { list-style: none; padding: 0; margin: 0; }
.client-item { display: flex; justify-content: space-between; align-items: center; padding: 12px; border-bottom: 1px solid #f3f4f6; transition: background 0.2s; }
.client-item:hover { background-color: #f9fafb; }
.client-name { font-weight: 600; color: #374151; display: block; }
.client-age { font-size: 14px; color: #6b7280; }
.client-date { font-size: 12px; color: #9ca3af; }
</style>

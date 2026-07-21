<template>
    <div class="clients-list-container">
        <h3>Список последних клиентов</h3>

        <!-- БЛОК СОРТИРОВКИ -->
        <div class="sort-controls">
            <span>Сортировка по возрасту:</span>
            <button @click="toggleAgeSort" class="btn-sort">
                {{ currentOrder === 'asc' ? '⬇️ От младших к старшим' : '⬆️ От старших к младшим' }}
            </button>
        </div>


        <div v-if="loading">
            <SkeletonRender/>
        </div>


        <div v-else-if="clients.length === 0" class="empty-text">
            Клиентов пока нет. Будьте первым!
        </div>


        <ul v-else class="clients-list">
            <li v-for="client in clients" :key="client.id" class="client-item">
                <div class="client-info">
                    <span class="client-name">{{ client.name }}</span>
                    <span class="client-age">{{ client.age }} лет</span>
                </div>
                <div class="client-actions">
                    <span class="client-date">{{ formatDate(client.created_at) }}</span>

                    <!-- КНОПКА РЕДАКТИРОВАНИЯ -->
                    <button class="btn-edit" @click="editClient(client)">✏️</button>

                    <button class="btn-delete" @click="deleteClient(client.id)">&times;</button>
                </div>
            </li>
        </ul>


        <base-pagination
            :current-page="currentPage"
            :last-page="lastPage"
            :total-pages="totalPages"
            @change-page="changePage"
        />

    </div>
</template>

<script setup>

import {inject, onMounted, ref} from 'vue';
// Импортируем функцию генерации скелетона
import {SkeletonComponent} from '../Skeleton';
import {api} from '@/api';
import BasePagination from "@/components/UI/Pagination.vue";

// Создаем Vue-компонент скелетона прямо из нашей JS-функции
const SkeletonRender = SkeletonComponent('card-users', 3);

const clients = ref([]);
const loading = ref(true);
const showToast = inject('showToast');

const currentPage = ref(1);
const lastPage = ref(1);
const totalPages = ref(1);

const currentSort = ref('created_at'); // по умолчанию сортируем по дате
const currentOrder = ref('desc');

onMounted(() => {
    // Ваша задержка в 10 секунд (10000 мс)
    fetchClients();

    window.addEventListener('refresh-clients-list', () => {
        // loading.value = true;
        fetchClients(currentPage.value); // Запрос уйдет на 1-ю страницу по умолчанию
    });
});
const changePage = (newPage) => {
    // loading.value = true; // Снова включаем скелетон при переходе на другую страницу
    fetchClients(newPage);
};

const editClient = (client) => {
    // Отправляем событие менеджеру окон, передавая имя формы и КЛОН данных клиента
    window.dispatchEvent(new CustomEvent('open-modal', {
        detail: {
            name: 'modal-form',
            data: {...client} // клонируем через деструктуризацию, чтобы не мутировать список напрямую
        }
    }));
};

const fetchClients = async (page = 1) => {
    try {
        // Передаем в метод getAll параметры сортировки
        const result = await api.clients.getAll(page, currentSort.value, currentOrder.value);

        console.log(result)

        clients.value = result.data;
        currentPage.value = result.current_page;
        lastPage.value = result.last_page;
        totalPages.value = result.last_page;
    } catch (error) {
        showToast(error.message, 'error');
    } finally {
        loading.value = false;
    }
};

// Функция переключения сортировки по клику на кнопку
const toggleAgeSort = () => {

    currentSort.value = 'age'; // фиксируем, что сортируем по возрасту

    // Меняем направление на противоположное
    currentOrder.value = currentOrder.value === 'asc' ? 'desc' : 'asc';

    // Сбрасываем пагинацию на 1 страницу, чтобы начать просмотр сначала
    fetchClients(1);
};

const deleteClient = async (id) => {
    try {
        // Axios + наш интерцептор вернут чистый JSON от Laravel
        const result = await api.clients.delete(id);
        showToast(result.message, 'success');
        await fetchClients(currentPage.value);

    } catch (error) {
        showToast(error.message, 'error'); // Поймает ошибку из интерцептора
    } finally {
        loading.value = false;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('ru-RU', {day: '2-digit', month: 'short'});
};
</script>

<style lang="scss">
@use "@styles/modules/skeleton";
</style>

<style lang="scss" scoped>
.clients-list-container {
    max-width: 500px;
    margin: 30px auto;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

h3 {
    margin-top: 0;
    color: #1f2937;
    border-bottom: 2px solid #f3f4f6;
    padding-bottom: 10px;
}

.loading-text, .empty-text {
    text-align: center;
    color: #9ca3af;
    padding: 20px 0;
}

.clients-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.client-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    border-bottom: 1px solid #f3f4f6;
    transition: background 0.2s;
}

.client-item:hover {
    background-color: #f9fafb;
}

.client-name {
    font-weight: 600;
    color: #374151;
    display: block;
}

.client-age {
    font-size: 14px;
    color: #6b7280;
}

.client-date {
    font-size: 12px;
    color: #9ca3af;
}

.client-actions {
    display: flex;
    align-items: center;
    gap: 15px;
}

.btn-edit {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 16px;
    padding: 4px;
    border-radius: 4px;
    transition: background 0.2s;
}

.btn-edit:hover {
    background-color: #f3f4f6;
}

.sort-controls {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
    font-size: 13px;
    color: #6b7280;
}
.btn-sort {
    background-color: #f3f4f6;
    border: 1px solid #e5e7eb;
    padding: 4px 10px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
    transition: background 0.2s;
}
.btn-sort:hover {
    background-color: #e5e7eb;
}



</style>

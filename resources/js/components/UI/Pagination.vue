<template>
    <div v-if="totalPages > 1" class="pagination-buttons">
        <button
            :disabled="currentPage === 1"
            @click="emitPage(currentPage - 1)"
        >
            Назад
        </button>

        <div class="pagination-pages">
            <!-- Перебираем сгенерированный массив страниц -->
            <template v-for="(page, idx) in visiblePages" :key="idx">
                <!-- Если элемент — многоточие, рендерим его как обычный текст без клика -->
                <span v-if="page === '...'" class="pagination-ellipsis">...</span>


                <span
                    v-else
                    class="pagination-page"
                    :class="{ 'active': currentPage === page }"
                    @click="emitPage(page)"
                >
                    {{ page }}
                </span>
            </template>
        </div>

        <button
            :disabled="currentPage === lastPage"
            @click="emitPage(currentPage + 1)"
        >
            Вперед
        </button>
    </div>
</template>

<script setup>
import { computed } from 'vue';

// 1. Принимаем мета-данные от родительского списка
const props = defineProps({
    currentPage: { type: Number, required: true },
    lastPage: { type: Number, required: true },
    totalPages: { type: Number, required: true }
});

// 2. Объявляем событие
const emit = defineEmits(['change-page']);

const emitPage = (page) => {
    emit('change-page', page);
};

// 3. Умное вычисление видимых страниц и многоточий
const visiblePages = computed(() => {
    const total = props.totalPages;
    const current = props.currentPage;
    const pages = [];

    // Если страниц всего 5 или меньше, просто выводим их все без многоточий
    if (total <= 5) {
        for (let i = 1; i <= total; i++) {
            pages.push(i);
        }
        return pages;
    }

    // ЛОГИКА ДЛЯ СХЕМЫ СКОЛЬЗЯЩЕГО ОКНА (6+ СТРАНИЦ):

    // Сценарий 1: Мы в самом начале (страницы 1, 2) -> Выводим: 1 2 3 ... 6
    if (current < 3) {
        pages.push(1, 2, 3, '...', total);
    }
    // Сценарий 3: Мы в самом конце (страницы 5, 6) -> Выводим: 1 ... 4 5 6
    else if (current > total - 2) {
        pages.push(1, '...', total - 2, total - 1, total);
    }
    // Сценарий 2: Мы посередине (страницы 3, 4) -> Выводим: 1 ... 3 4 ... 6
    else {
        pages.push(1, '...', current - 1, current, current + 1, '...', total);
    }

    return pages;
});

</script>

<style lang="scss" scoped>
.pagination-buttons {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #f3f4f6;
    font-size: 14px;
    color: #4b5563;
    font-family: sans-serif;

    button {
        padding: 6px 14px;
        background-color: #f3f4f6;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 500;
        transition: background 0.2s;

        &:hover:not(:disabled) {
            background-color: #e5e7eb;
        }

        &:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    }
}

.pagination-pages {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.pagination-page {
    width: 26px;
    height: 26px;
    border: 1px solid #000;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.2s;

    &:hover:not(.active) {
        background-color: #f3f4f6;
    }

    &.active {
        background-color: rgba(12, 84, 96, 0.38);
        border-color: rgba(12, 84, 96, 0.6);
        font-weight: bold;
    }
}

/* Стили для некликабельного многоточия */
.pagination-ellipsis {
    color: #9ca3af;
    font-weight: bold;
    cursor: default;
    user-select: none;
}
</style>

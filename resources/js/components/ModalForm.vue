<template>
    <div  class="modal-overlay" @click.self="closeModal">
        <div v-if="loading" class="loading">Загрузка полей...</div>
        <div v-else class="modal-content">
            <button class="btn-close" @click="closeModal">&times;</button>

            <h2>Создать клиента</h2>

            <form @submit.prevent="submitForm" novalidate>

                <div class="form-group">
                    <!-- Добавляем динамический класс ошибки и очистку при вводе input -->
                    <input
                        v-model="form.name"
                        :class="{ 'input-error': errors.name }"
                        type="text"
                        placeholder="Имя"
                        @input="errors.name = ''"
                        required
                    >
                    <!-- Блок для вывода текста ошибки под полем -->
                    <span v-if="errors.name" class="error-text">{{ errors.name }}</span>
                </div>

                <div class="form-group">
                    <!-- Добавляем модификатор .number для корректного типа данных -->
                    <input
                        v-model.number="form.age"
                        :class="{ 'input-error': errors.age }"
                        type="number"
                        placeholder="Возраст"
                        @input="errors.age = ''"
                        required
                    >
                    <span v-if="errors.age" class="error-text">{{ errors.age }}</span>
                </div>

                <button type="submit" class="btn-submit" :disabled="submitting">
                    {{ submitting ? 'Отправка...' : 'Отправить' }}
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { onMounted, onUnmounted, ref, inject } from 'vue';

const showToast = inject('showToast');

const getInitialForm = () => ({
    name: '',
    age: '',
    // тут может быть еще 20 полей
});

const form = ref(getInitialForm());

// Объект для хранения ошибок валидации
const errors = ref({
    name: '',
    age: ''
});

const isOpen = ref(true);
const loading = ref(false);
const submitting = ref(false);

const openModal = () => {
    isOpen.value = true;
};

// Функция закрытия окна с очисткой полей и ошибок
const emit = defineEmits(['close']);

// 2. Функция закрытия теперь очищает данные и сообщает менеджеру окон
const closeModal = () => {
    // Генерируем событие для удаления компонента из DOM менеджером окон
    emit('close');
};

// Клиентская валидация перед отправкой
const validateForm = () => {
    let isValid = true;
    errors.value.name = '';
    errors.value.age = '';

    if (!form.value.name.trim()) {
        errors.value.name = 'Поле Имя обязательно для заполнения';
        isValid = false;
    } else if (form.value.name.length > 255) {
        errors.value.name = 'Имя не должно превышать 255 символов';
        isValid = false;
    }

    if (!form.value.age) {
        errors.value.age = 'Поле Возраст обязательно для заполнения';
        isValid = false;
    } else if (!Number.isInteger(form.value.age) || form.value.age < 1) {
        errors.value.age = 'Возраст должен быть целым числом больше 0';
        isValid = false;
    }

    return isValid;
};

const submitForm = async () => {
    // 1. Проверяем валидность на клиенте. Если есть ошибки — прерываем отправку
    if (!validateForm()) return;

    submitting.value = true;

    try {
        const response = await fetch('/api/clients', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(form.value)
        });

        const result = await response.json();


        if (response.ok) {
            console.log(result.message);
            showToast(result.message, 'success');
            closeModal(); // Закрываем форму при успехе
            form.value = getInitialForm();

            window.dispatchEvent(new CustomEvent('refresh-clients-list'));
        } else {
            // 2. Если бэкенд Laravel вернул ошибку валидации (код 422), подставляем её под поля
            if (result.errors) {
                if (result.errors.name) errors.value.name = result.errors.name[0];
                if (result.errors.age) errors.value.age = result.errors.age[0];
            }
            showToast('Ошибка заполнения формы', 'error');
        }
    } catch (error) {
        showToast('Ошибка сервера', 'error');
    } finally {
        submitting.value = false;
    }
};

onMounted(() => {
    window.addEventListener('open-feedback-modal', openModal);
});
onUnmounted(() => {
    window.removeEventListener('open-feedback-modal', openModal);
});
</script>

<style scoped>
/* Ваши стили остаются без изменений */
.input-error {
    border-color: #ef4444 !important;
    background-color: #fef2f2;
}

.error-text {
    color: #ef4444;
    font-size: 13px;
    margin-top: 4px;
    display: block;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    padding: 30px;
    border-radius: 8px;
    width: 100%;
    max-width: 450px;
    position: relative;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.btn-close {
    position: absolute;
    top: 10px;
    right: 15px;
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #9ca3af;
}

.btn-close:hover {
    color: #374151;
}

.form-group {
    margin-bottom: 15px;
    text-align: left;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

.form-group input, .form-group textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    box-sizing: border-box;
    outline: none;
}

.btn-submit {
    width: 100%;
    padding: 10px;
    background-color: #10b981;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

.btn-submit:hover {
    background-color: #059669;
}

.btn-submit:disabled {
    background-color: #a7f3d0;
    cursor: not-allowed;
}

.loading {
    padding: 20px;
    text-align: center;
    color: #6b7280;
}
</style>

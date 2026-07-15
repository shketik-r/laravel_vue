<template>
  <div v-if="isOpen" class="modal-overlay" @click.self="isOpen = false">
    <div v-if="loading" class="loading">Загрузка полей...</div>
    <div v-else class="modal-content">
      <button class="btn-close" @click="isOpen = false">&times;</button>

      <h2>Обратная связь</h2>

      <!-- Добавляем атрибут novalidate, чтобы отключить стандартные подсказки браузера -->
      <form  @submit.prevent="handleSubmit" novalidate>

        <div v-for="field in fields" :key="field.name" class="form-group">
          <label>{{ field.label }}:</label>

          <textarea
              v-if="field.type === 'textarea'"
              v-model="formData[field.name]"
              :placeholder="field.placeholder"
              :class="{ 'input-error': errors[field.name] }"
          ></textarea>

          <input
              v-else
              :type="field.type"
              v-model="formData[field.name]"
              :placeholder="field.placeholder"
              :class="{ 'input-error': errors[field.name] }"
          />

          <!-- Вывод ошибки под конкретным полем -->
          <span v-if="errors[field.name]" class="error-text">
            {{ errors[field.name][0] }}
          </span>
        </div>

        <button type="submit" class="btn-submit" :disabled="submitting">
          {{ submitting ? 'Проверка...' : 'Отправить' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const isOpen = ref(false);
const loading = ref(false);
const submitting = ref(false);

const fields = ref([]);
const formData = reactive({});
const errors = ref({}); // Объект для хранения ошибок валидации с бэка

const fetchFields = async () => {
  if (fields.value.length > 0) return;
  loading.value = true;
  try {
    const response = await axios.get('/api/form-fields');
    fields.value = response.data;
    fields.value.forEach(field => {
      formData[field.name] = '';
    });
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const openModal = () => {
  isOpen.value = true;
  errors.value = {}; // Сбрасываем старые ошибки при открытии
  fetchFields();
};

const handleSubmit = async () => {
  submitting.value = true;
  errors.value = {}; // Очищаем предыдущие ошибки перед отправкой

  try {
    const response = await axios.post('/api/form-submit', formData);
    if (response.data.success) {
      isOpen.value = false;
      Object.keys(formData).forEach(key => formData[key] = '');

      const successEvent = new CustomEvent('open-success-modal', {
        detail: { message: response.data.message }
      });
      window.dispatchEvent(successEvent);
    }
  } catch (error) {
    // Если  вернул ошибку валидации (код 422)
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors; // Записываем ошибки в объект
    } else {
      alert('Произошла системная ошибка при отправке формы.');
      console.error(error);
    }
  } finally {
    submitting.value = false;
  }
};

onMounted(() => { window.addEventListener('open-feedback-modal', openModal); });
onUnmounted(() => { window.removeEventListener('open-feedback-modal', openModal); });
</script>

<style scoped>
/* Добавляем стили для отображения ошибок */
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


.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center; z-index: 1000; }
.modal-content { background: white; padding: 30px; border-radius: 8px; width: 100%; max-width: 450px; position: relative; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
.btn-close { position: absolute; top: 10px; right: 15px; background: none; border: none; font-size: 28px; cursor: pointer; color: #9ca3af; }
.btn-close:hover { color: #374151; }
.form-group { margin-bottom: 15px; text-align: left; }
.form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
.form-group input, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; outline: none; }
.btn-submit { width: 100%; padding: 10px; background-color: #10b981; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
.btn-submit:hover { background-color: #059669; }
.btn-submit:disabled { background-color: #a7f3d0; cursor: not-allowed; }
.loading { padding: 20px; text-align: center; color: #6b7280; }
</style>

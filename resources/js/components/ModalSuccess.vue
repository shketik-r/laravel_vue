<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const isOpen = ref(false);
const successMessage = ref('');

// Функция открытия окна
const openModal = (event) => {
  successMessage.value = event.detail?.message || 'Успешно!';
  isOpen.value = true;
};

// Слушаем событие
onMounted(() => {
  window.addEventListener('open-success-modal', openModal);
});

// Убираем слушатель при уничтожении
onUnmounted(() => {
  window.removeEventListener('open-success-modal', openModal);
});


</script>

<template>
  <div v-if="isOpen" class="modal-overlay" @click.self="isOpen = false">

    <div class="modal-content">
      <button class="btn-close" @click="isOpen = false">&times;</button>
      <h2>{{successMessage}}</h2>
    </div>
  </div>
</template>

<style scoped>

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

</style>
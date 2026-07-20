<template>
    <div class="modal-manager-wrapper">
        <!-- Перебираем все формы, которые были добавлены в историю рендеринга -->
        <component
            :is="modalRegistry[modalName].component"
            v-for="modalName in renderedModals"
            :key="modalName"
            v-show="activeModal === modalName"
            :modal-data="modalData"
            @close="closeModal(modalName)"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { modalRegistry } from '../../config/modalRegistry';

const activeModal = ref(null); // Имя формы, которая открыта прямо сейчас
const modalData = ref(null);
const renderedModals = ref(new Set()); // Список форм, физически находящихся в DOM

const openModal = (event) => {
    const name = event.detail.name;
    activeModal.value = name;
    modalData.value = event.detail.data || null;

    // Добавляем форму в список отрендеренных (Vue вставит её в DOM)
    renderedModals.value.add(name);
};

const closeModal = (name) => {
    // Закрываем активное окно
    activeModal.value = null;
    modalData.value = null;

    // Проверяем конфиг: если keepAlive равен false, полностью удаляем её из DOM
    if (!modalRegistry[name].keepAlive) {
        renderedModals.value.delete(name);
    }
};

onMounted(() => {
    window.addEventListener('open-modal', openModal);
    window.addEventListener('close-modal', () => activeModal.value = null);
});

onUnmounted(() => {
    window.removeEventListener('open-modal', openModal);
    window.removeEventListener('close-modal', () => activeModal.value = null);
});
</script>

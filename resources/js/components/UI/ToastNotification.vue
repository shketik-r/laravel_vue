<template>
    <div class="toast-container">
        <TransitionGroup name="toast">
            <div v-for="item in toasts" :key="item.id" :class="['toast-box', item.type]">
                <span class="toast-icon">{{ getIcon(item.type) }}</span>
                <p class="toast-message">{{ item.message }}</p>
            </div>
        </TransitionGroup>
    </div>
</template>

<script setup>
defineProps({
    toasts: Array
});

const getIcon = (type) => {
    const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
    return icons[type] || '🔔';
};
</script>

<style scoped>
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    display: flex;
    flex-direction: column;
    gap: 10px; /* Уведомления будут красиво вставать друг под другом */
    z-index: 9999;
}
.toast-box {
    position: fixed;
    top: 20px;
    right: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 24px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 9999;
    min-width: 250px;
    font-family: sans-serif;
}
.toast-box.success { background-color: #e6f4ea; border-left: 5px solid #34a853; color: #137333; }
.toast-box.error { background-color: #fce8e6; border-left: 5px solid #ea4335; color: #c5221f; }
.toast-message { margin: 0; font-weight: 500; }
.toast-enter-active, .toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from { transform: translateX(100px); opacity: 0; }
.toast-leave-to { opacity: 0; }
</style>

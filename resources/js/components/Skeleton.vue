<template>
    <div class="skeleton-pulse">
        <div class="skeleton-bar" v-for="i in 3" :key="i"></div>
    </div>
</template>

<style scoped>
.skeleton-pulse {
    background: #f0f0f0;
    border-radius: 4px;
    margin-bottom: 10px;
    animation: pulse 1.5s infinite ease-in-out;
}
@keyframes pulse {
    0% { opacity: 0.6; }
    50% { opacity: 1; }
    100% { opacity: 0.6; }
}
</style>

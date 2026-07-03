<template>
  <p>Vue-компонент:</p>
  <div class="card">
    <p>Счётчик: {{ count }}</p>
    <button @click="count++">Нажми меня</button>
  </div>
</template>

<script setup>
import { ref } from 'vue';
const count = ref(0);
</script>


<style scoped>
.card { padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
</style>

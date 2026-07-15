<template>
    <div class="features-block">
        <!-- Выводим заголовок "Наши преимущества" -->
        <h2>{{ featuresData.title }}</h2>

        <!-- Перебираем элементы списка -->
        <ul>
            <li v-for="(item, index) in featuresData.items" :key="index">
                {{ item }}
            </li>
        </ul>
    </div>
</template>

<script setup>
import { defineProps } from 'vue';

// Принимаем данные от Laravel. Vue сам переведет kebab-case из Blade в camelCase
defineProps({
    featuresData: {
        type: Object,
        required: true
    }
});
</script>

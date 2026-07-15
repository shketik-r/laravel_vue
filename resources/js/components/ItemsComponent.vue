<template>
    <div class="features-block" v-if="featuresData">
        <!-- Безопасно проверяем и выводим заголовок через точку -->
        <h2 >{{ featuresData.title || 'Наши преимущества 2' }}</h2>


        <ul v-if="featuresData.items?.length">
            <li v-for="(item, index) in featuresData.items" :key="index">
                {{ item }}
            </li>
        </ul>
    </div>

</template>

<script setup lang="ts">



withDefaults(
    defineProps<{
        featuresData?: {
            title?: string,
            items: {
                title: string,
                src: string
            }[]
        }
    }>(),
    {
        featuresData: () => ({
            title: '',
            items: []
        })
    }
);




</script>







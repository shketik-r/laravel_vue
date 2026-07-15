import {createApp, defineAsyncComponent} from 'vue';
import {createSkeleton} from './Skeleton';


const app = createApp({});

// Регистрируем компоненты АСИНХРОННО
// 2. Регистрируем компоненты асинхронно с привязкой скелетонов
app.component('items-component', defineAsyncComponent({
    loader: () => import('@/components/ItemsComponent.vue'),
    loadingComponent: createSkeleton('card'),
    delay: 0 // Скелетон появится, если загрузка займет больше 100мс
}));

app.component('example-component', defineAsyncComponent({
    loader: () => import('@/components/ExampleComponent.vue'),
    loadingComponent: createSkeleton('card'),
    delay: 100
}));

app.component('modal-form', defineAsyncComponent({
    loader: () => import('@/components/ModalForm.vue'),
    loadingComponent: createSkeleton('form'),
    delay: 50
}));

app.component('modal-success', defineAsyncComponent({
    loader: () => import('@/components/ModalSuccess.vue'),
    loadingComponent: createSkeleton('form'),
    delay: 50
}));

app.component('qr-scanner', defineAsyncComponent({
    loader: () => import('@/components/QrScannerComponent.vue')
}));


app.mount('#app');

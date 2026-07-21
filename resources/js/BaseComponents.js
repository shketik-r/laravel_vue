import {createApp, defineAsyncComponent, reactive } from 'vue';
import {createSkeleton} from './Skeleton';
import ToastNotification from './components/UI/ToastNotification.vue';
import ModalManager from "@/components/UI/ModalManager.vue";


const app = createApp({
    setup() {
        // Храним массив активных уведомлений для стека
        const toasts = reactive([]);

        const showToast = (message, type = 'success', duration = 3000) => {
            const id = Date.now();

            // Добавляем новое уведомление в стек
            toasts.push({ id, message, type });

            // Автоматически удаляем конкретно это уведомление через X секунд
            setTimeout(() => {
                const index = toasts.findIndex(t => t.id === id);
                if (index !== -1) toasts.splice(index, 1);
            }, duration);
        };

        app.provide('showToast', showToast);

        return { toasts };
    }
});

// Регистрируем компоненты АСИНХРОННО
// 2. Регистрируем компоненты асинхронно с привязкой скелетонов
app.component('items-component', defineAsyncComponent({
    loader: () => import('@/components/ItemsComponent.vue'),
    loadingComponent: createSkeleton('card-users'),
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

app.component('modal-manager', ModalManager);
app.component('toast-notification', ToastNotification);


app.mount('#app');

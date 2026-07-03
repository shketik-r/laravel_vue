import './bootstrap';
import { createApp } from 'vue';

// Импортируем компонент напрямую по точному пути
import ExampleComponent from './components/ExampleComponent.vue';
import ModalForm from './components/ModalForm.vue'; // форма
import ModalSuccess from './components/ModalSuccess.vue'; // ответ

const app = createApp({});

// Регистрируем
app.component('example-component', ExampleComponent);
app.component('modal-form', ModalForm);
app.component('modal-success', ModalSuccess);


app.mount('#app');

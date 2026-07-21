import axios from 'axios';

// 1. Создаем базовый инстанс Axios с дефолтными настройками
const apiClient = axios.create({
    baseURL: '/api', // Все запросы будут автоматически начинаться с /api
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});

// 2. Глобальный перехватчик ошибок (Интерцептор)
// Он будет автоматически доставать сообщения об ошибках от Laravel
apiClient.interceptors.response.use(
    (response) => response.data, // Если всё ок, сразу отдаем тело ответа (data)
    (error) => {
        // Формируем понятный объект ошибки для наших компонентов
        const customError = new Error(error.response?.data?.message || 'Ошибка сервера');
        customError.errors = error.response?.data?.errors || null; // Ошибки валидации Laravel
        return Promise.reject(customError);
    }
);

// 3. Экспортируем чистый и красивый API-сервис
export const api = {
    clients: {
        // GET /api/clients?page=1
        // Передаем страницу, колонку и направление сортировки
        getAll: (page = 1, sortBy = 'created_at', sortOrder = 'desc') =>
            apiClient.get(`/clients?page=${page}&sort_by=${sortBy}&sort_order=${sortOrder}`),


        // POST /api/clients
        create: (data) => apiClient.post('/clients', data),

        // PUT /api/clients/5
        update: (id, data) => apiClient.put(`/clients/${id}`, data),

        // DELETE /api/clients/5
        delete: (id) => apiClient.delete(`/clients/${id}`)
    }
};

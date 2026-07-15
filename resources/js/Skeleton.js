import { h } from 'vue';
// 1. Создаем универсальный HTML-скелетон на чистом CSS
  export const createSkeleton = (type = 'card') => {
    return {
        render() {
            if (type === 'form') {
                return h('div', { class: 'sk-form' }, [
                    h('div', { class: 'sk-blink sk-title' }),
                    h('div', { class: 'sk-blink sk-input' }),
                    h('div', { class: 'sk-blink sk-input' }),
                    h('div', { class: 'sk-blink sk-btn' })
                ]);
            }
            // По умолчанию (для items-component и example-component) — сетка карточек
            return h('div', { class: 'sk-grid' },
                Array.from({ length: 3 }).map(() =>
                    h('div', { class: 'sk-card' }, [
                        h('div', { class: 'sk-blink sk-img' }),
                        h('div', { class: 'sk-blink sk-title' }),
                        h('div', { class: 'sk-blink sk-text' }),
                        h('div', { class: 'sk-blink sk-text short' })
                    ])
                )
            );
        }
    };
};



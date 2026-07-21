import { h } from 'vue';

// Универсальный HTML-скелетон
export const createSkeleton = (type = 'card' , count = 1) => {
    return {
        render() {
            // 1. Скелетон для ФОРМЫ
            if (type === 'form') {
                return h('div', { class: 'sk-form' }, [
                    h('div', { class: 'sk-blink sk-title' }),
                    h('div', { class: 'sk-blink sk-input' }),
                    h('div', { class: 'sk-blink sk-input' }),
                    h('div', { class: 'sk-blink sk-btn' })
                ]);
            }

            // 2. Скелетон для СПИСКА ПОЛЬЗОВАТЕЛЕЙ (Исправлено)
            if (type === 'card-users') {
                return h('div', { class: 'sk-users-list' },
                    Array.from({ length: count }).map(() =>
                        h('div', { class: 'sk-user-row' }, [
                            h('div', { class: 'sk-blink sk-user-avatar' }),
                            h('div', { class: 'sk-user-info' }, [
                                h('div', { class: 'sk-blink sk-user-line' }),
                                h('div', { class: 'sk-blink sk-user-line short' })
                            ])
                        ])
                    )
                );
            }

            // 3. По умолчанию — сетка карточек
            return h('div', { class: 'sk-grid' },
                Array.from({ length: count }).map(() =>
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


export const SkeletonComponent = (type, count) => createSkeleton(type, count);

import { defineAsyncComponent } from 'vue';
import { createSkeleton } from '../Skeleton';

export const modalRegistry = {
    'modal-form': {
        component: defineAsyncComponent({
            loader: () => import('@/components/ModalForm.vue'),
            loadingComponent: createSkeleton('form'),
            delay: 50
        }),
        keepAlive: true // Эта форма ОСТАНЕТСЯ в DOM после закрытия
    },
    'modal-success': {
        component: defineAsyncComponent({
            loader: () => import('@/components/ModalSuccess.vue'),
            loadingComponent: createSkeleton('form'),
            delay: 50
        }),
        keepAlive: false
    }
};

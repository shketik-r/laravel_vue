<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import { Html5Qrcode, Html5QrcodeSupportedFormats } from 'html5-qrcode';

const emit = defineEmits<{
    (e: 'scanned', data: { text: string; type: string }): void;
}>();

const containerId = 'qr-camera-stream';
let html5Qrcode: Html5Qrcode | null = null;

const isCameraLoading = ref(true);
const cameraError = ref<string | null>(null);
const isScanned = ref(false);

// Оставляем адекватный размер рамки для мелких кодов
const BOX_RATIO = 0.55;

// Функция включения аппаратного зума камеры
const applyHardwareZoom = async (scanner: Html5Qrcode) => {
    try {
        // Получаем запущенный видео-трек
        const runningTrack = scanner.getRunningTrackCapabilities();

        // Проверяем, поддерживает ли камера телефона зум на уровне железа
        if (runningTrack && 'zoom' in runningTrack) {
            const zoomCapabilities = (runningTrack as any).zoom;
            const minZoom = zoomCapabilities.min || 1;
            const maxZoom = zoomCapabilities.max || 1;

            // Выбираем оптимальное приближение (например, x2.5 или максимум устройства)
            const targetZoom = Math.min(2.5, maxZoom);

            if (targetZoom > minZoom) {
                // Передаем команду камере телефона физически приблизить картинку
                await scanner.applyVideoConstraints({
                    advanced: [{ zoom: targetZoom }]
                } as any);
                console.log(`Аппаратный зум успешно установлен на: ${targetZoom}x`);
            }
        } else {
            console.warn('Это устройство или браузер не поддерживают аппаратный зум камеры.');
        }
    } catch (err) {
        console.error('Не удалось применить аппаратный зум:', err);
    }
};

onMounted(async () => {
    isCameraLoading.value = true;
    try {
        html5Qrcode = new Html5Qrcode(containerId);

        await html5Qrcode.start(
            { facingMode: "environment" },
            {
                fps: 30, // Увеличиваем частоту кадров для быстрой фокусировки
                qrbox: (width, height) => {
                    const minSize = Math.min(width, height);
                    const boxSize = Math.floor(minSize * BOX_RATIO);
                    return { width: boxSize, height: boxSize };
                },
                formatsToSupport: [
                    Html5QrcodeSupportedFormats.DATA_MATRIX
                ]
            },
            (decodedText) => {
                if (isScanned.value) return;
                isScanned.value = true;

                emit('scanned', {
                    text: decodedText,
                    type: 'DataMatrix (Маркировка)'
                });

                if (navigator.vibrate) navigator.vibrate(120);
            },
            () => {}
        );

        // ВАЖНО: Аппаратный зум можно вызвать ТОЛЬКО после успешного старта стрима
        if (html5Qrcode) {
            await applyHardwareZoom(html5Qrcode);
        }

        isCameraLoading.value = false;
    } catch (err: any) {
        isCameraLoading.value = false;
        cameraError.value = 'Ошибка доступа к камере. Закройте другие вкладки и обновите страницу.';
        console.error('Критическая ошибка старта:', err);
    }
});

onUnmounted(async () => {
    if (html5Qrcode && html5Qrcode.isScanning) {
        try { await html5Qrcode.stop(); } catch (err) { console.error(err); }
    }
});
</script>

<template>
    <div class="camera-container" :class="{ 'state-success': isScanned }">
        <div class="macro-clipper">
            <div :id="containerId" class="video-viewport"></div>
        </div>

        <div v-if="isCameraLoading" class="camera-overlay loading">
            <div class="spinner"></div>
            <p>Включение макро-камеры...</p>
        </div>

        <div v-if="cameraError" class="camera-overlay error">
            <p>{{ cameraError }}</p>
        </div>

        <div v-if="!isCameraLoading && !cameraError" class="camera-overlay ui">
            <div class="laser-line"></div>
            <p class="scan-tip">Удерживайте код по центру рамки.<br>Камера автоматически сфокусируется.</p>
        </div>
    </div>
</template>

<style scoped>
.camera-container { position: relative; width: 100%; max-width: 450px; aspect-ratio: 1 / 1; margin: 0 auto; border-radius: 24px; overflow: hidden; background-color: #000; }
.macro-clipper { width: 100%; height: 100%; overflow: hidden; position: relative; }
.video-viewport { width: 100%; height: 100%; }

/* УДАЛЕН CSS-ZOOM SCALE(1.6): Теперь видео транслируется 1:1,
   а за приближение мелких пикселей отвечает реальный модуль камеры */
.video-viewport :deep(video) {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
}

.camera-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #fff; font-family: sans-serif; z-index: 2; }
.camera-overlay.loading, .camera-overlay.error { background: #111; z-index: 5; text-align: center; padding: 20px; }

/* Аккуратная рамка прицела, завязанная на BOX_RATIO библиотеки */
.video-viewport :deep(#qr-camera-stream__scan_region) {
    border: 2px solid #a855f7 !important;
    border-radius: 16px;
    box-shadow: 0 0 0 2000px rgba(0, 0, 0, 0.6);
}

.laser-line { position: absolute; width: 45%; height: 3px; background: linear-gradient(90deg, transparent, #a855f7, transparent); box-shadow: 0 0 8px #a855f7; animation: scanAnimation 2s linear infinite; }
@keyframes scanAnimation { 0% { top: 25%; } 50% { top: 70%; } 100% { top: 25%; } }

.scan-tip { position: absolute; bottom: 20px; background: rgba(0, 0, 0, 0.75); padding: 8px 16px; border-radius: 20px; font-size: 13px; pointer-events: none; text-align: center; max-width: 85%; line-height: 1.4; border: 1px solid rgba(255, 255, 255, 0.1); }
.spinner { width: 35px; height: 35px; border: 4px solid rgba(255, 255, 255, 0.1); border-top-color: #a855f7; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 12px; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>

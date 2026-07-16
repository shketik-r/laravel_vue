<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import { Html5Qrcode, Html5QrcodeSupportedFormats } from 'html5-qrcode';

const emit = defineEmits<{
    // Добавляем поле image в объект ответа
    (e: 'scanned', data: { text: string; type: string; image: string }): void;
}>();

const containerId = 'qr-camera-stream';
let html5Qrcode: Html5Qrcode | null = null;

const isCameraLoading = ref(true);
const cameraError = ref<string | null>(null);
const isScanned = ref(false);

const BOX_RATIO = 0.55;

// Функция создания снимка экрана в момент сканирования
const captureCurrentFrame = (): string => {
    try {
        // Находим тег видео, который создала библиотека внутри нашего контейнера
        const videoElement = document.querySelector(`#${containerId} video`) as HTMLVideoElement;
        if (!videoElement) return '';

        const canvas = document.createElement('canvas');
        // Берем реальное разрешение видеопотока, а не CSS размеры
        canvas.width = videoElement.videoWidth;
        canvas.height = videoElement.videoHeight;

        const ctx = canvas.getContext('2d');
        if (!ctx) return '';

        // Рисуем текущий кадр видео на холст
        ctx.drawImage(videoElement, 0, 0, canvas.width, canvas.height);

        // Конвертируем в формат webp (или jpeg) для экономии памяти
        return canvas.toDataURL('image/webp', 0.7);
    } catch (err) {
        console.error('Не удалось сделать снимок кадра:', err);
        return '';
    }
};

const applyHardwareZoom = async (scanner: Html5Qrcode) => {
    try {
        const runningTrack = scanner.getRunningTrackCapabilities();
        if (runningTrack && 'zoom' in runningTrack) {
            const zoomCapabilities = (runningTrack as any).zoom;
            const minZoom = zoomCapabilities.min || 1;
            const maxZoom = zoomCapabilities.max || 1;
            const targetZoom = Math.min(2.5, maxZoom);

            if (targetZoom > minZoom) {
                await scanner.applyVideoConstraints({
                    advanced: [{ zoom: targetZoom }]
                } as any);
            }
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
                fps: 30,
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

                // 1. Делаем снимок ДО того, как остановим камеру
                const capturedImage = captureCurrentFrame();

                // 2. Отправляем родителю текст и снимок
                emit('scanned', {
                    text: decodedText,
                    type: 'DataMatrix (Маркировка)',
                    image: capturedImage
                });

                if (navigator.vibrate) navigator.vibrate(120);
            },
            () => {}
        );

        if (html5Qrcode) {
            await applyHardwareZoom(html5Qrcode);
        }

        isCameraLoading.value = false;
    } catch (err: any) {
        isCameraLoading.value = false;
        cameraError.value = 'Ошибка доступа к камере. Закройте другие вкладки и обновите страницу.';
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
.video-viewport :deep(video) { width: 100% !important; height: 100% !important; object-fit: cover !important; }
.camera-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #fff; font-family: sans-serif; z-index: 2; }
.camera-overlay.loading, .camera-overlay.error { background: #111; z-index: 5; text-align: center; padding: 20px; }
.video-viewport :deep(#qr-camera-stream__scan_region) { border: 2px solid #a855f7 !important; border-radius: 16px; box-shadow: 0 0 0 2000px rgba(0, 0, 0, 0.6); }
.laser-line { position: absolute; width: 45%; height: 3px; background: linear-gradient(90deg, transparent, #a855f7, transparent); box-shadow: 0 0 8px #a855f7; animation: scanAnimation 2s linear infinite; }
@keyframes scanAnimation { 0% { top: 25%; } 50% { top: 70%; } 100% { top: 25%; } }
.scan-tip { position: absolute; bottom: 20px; background: rgba(0, 0, 0, 0.75); padding: 8px 16px; border-radius: 20px; font-size: 13px; pointer-events: none; text-align: center; max-width: 85%; line-height: 1.4; border: 1px solid rgba(255, 255, 255, 0.1); }
.spinner { width: 35px; height: 35px; border: 4px solid rgba(255, 255, 255, 0.1); border-top-color: #a855f7; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 12px; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>

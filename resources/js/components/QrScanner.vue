<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import { Html5Qrcode, Html5QrcodeSupportedFormats } from 'html5-qrcode';

const emit = defineEmits<{
    // Добавили свойство snapshot для передачи картинки родителю
    (e: 'scanned', data: { text: string; type: string; snapshot: string }): void;
}>();

const containerId = 'qr-camera-stream';
let html5Qrcode: Html5Qrcode | null = null;

const isCameraLoading = ref(true);
const cameraError = ref<string | null>(null);
const isScanned = ref(false);

const detectedTypeName = ref('');
const detectedTypeClass = ref('');

const BOX_RATIO = 0.7; // Размер прицела (70% от экрана)

onMounted(async () => {
    try {
        html5Qrcode = new Html5Qrcode(containerId);

        await html5Qrcode.start(
            { facingMode: "environment" },
            {
                fps: 20,
                qrbox: (width, height) => {
                    const minSize = Math.min(width, height);
                    const boxSize = Math.floor(minSize * BOX_RATIO);
                    return { width: boxSize, height: boxSize };
                },
                formatsToSupport: [
                    Html5QrcodeSupportedFormats.QR_CODE,
                    Html5QrcodeSupportedFormats.DATA_MATRIX
                ]
            },
            (decodedText, decodedResult) => {
                if (isScanned.value) return;
                isScanned.value = true;

                // 1. Определяем тип и цвет подсветки
                const rawFormat = decodedResult.result?.format?.formatName;
                let highlightColor = '#3b82f6'; // Синий для QR

                if (rawFormat === 'DATA_MATRIX') {
                    detectedTypeName.value = 'DataMatrix (Маркировка)';
                    detectedTypeClass.value = 'type-datamatrix';
                    highlightColor = '#a855f7'; // Фиолетовый для DataMatrix
                } else if (rawFormat === 'QR_CODE') {
                    detectedTypeName.value = 'QR-код';
                    detectedTypeClass.value = 'type-qrcode';
                } else {
                    detectedTypeName.value = 'Штрих-код';
                    detectedTypeClass.value = 'type-barcode';
                    highlightColor = '#10b981';
                }

                // 2. Генерируем скриншот с эффектом фокуса на коде
                const snapshotBase64 = makeFocusedSnapshot(highlightColor);

                // 3. Отправляем все данные в родительский компонент
                emit('scanned', {
                    text: decodedText,
                    type: detectedTypeName.value,
                    snapshot: snapshotBase64
                });

                if (navigator.vibrate) navigator.vibrate(120);
            },
            () => {}
        );

        isCameraLoading.value = false;
    } catch (err: any) {
        isCameraLoading.value = false;
        cameraError.value = 'Ошибка доступа к камере.';
    }
});

// Графическая генерация скриншота с выделением кода по центру
const makeFocusedSnapshot = (color: string): string => {
    const video = document.querySelector(`#${containerId} video`) as HTMLVideoElement;
    const canvas = document.getElementById('screenshot-canvas') as HTMLCanvasElement;
    if (!video || !canvas) return '';

    const ctx = canvas.getContext('2d');
    if (!ctx) return '';

    const videoWidth = video.videoWidth;
    const videoHeight = video.videoHeight;
    const minVideoSize = Math.min(videoWidth, videoHeight);
    const boxSize = Math.floor(minVideoSize * BOX_RATIO);

    // Координаты центрального прицела
    const sx = (videoWidth - boxSize) / 2;
    const sy = (videoHeight - boxSize) / 2;

    // Устанавливаем результирующий размер картинки
    canvas.width = 450;
    canvas.height = 450;

    // Слой 1: Рисуем базовый кадр из видеокамеры
    ctx.drawImage(video, sx, sy, boxSize, boxSize, 0, 0, 450, 450);

    // Слой 2: Создаем эффект полупрозрачного затемнения всего снимка
    ctx.fillStyle = 'rgba(0, 0, 0, 0.45)';
    ctx.fillRect(0, 0, 450, 450);

    // Слой 3: Вырезаем (осветляем) центральную область, где находится сам код
    ctx.save();
    ctx.globalCompositeOperation = 'destination-out';

    // Прорисовываем маску скругленного квадрата в центре кадра
    const pad = 60; // Отступ маски выделения от краев картинки
    const size = 450 - pad * 2;
    const radius = 16;

    ctx.beginPath();
    ctx.moveTo(pad + radius, pad);
    ctx.lineTo(pad + size - radius, pad);
    ctx.quadraticCurveTo(pad + size, pad, pad + size, pad + radius);
    ctx.lineTo(pad + size, pad + size - radius);
    ctx.quadraticCurveTo(pad + size, pad + size, pad + size - radius, pad + size);
    ctx.lineTo(pad + radius, pad + size);
    ctx.quadraticCurveTo(pad, pad + size, pad, pad + size - radius);
    ctx.lineTo(pad, pad + radius);
    ctx.quadraticCurveTo(pad, pad, pad + radius, pad);
    ctx.closePath();
    ctx.fill();
    ctx.restore();

    // Слой 4: Рисуем яркую неоновую рамку вокруг вырезанного кода
    ctx.save();
    ctx.strokeStyle = color;
    ctx.lineWidth = 5;
    ctx.lineJoin = 'round';
    ctx.shadowBlur = 12;
    ctx.shadowColor = color;
    ctx.stroke();
    ctx.restore();

    // Экспортируем в JPG
    return canvas.toDataURL('image/jpeg', 0.85);
};

onUnmounted(async () => {
    if (html5Qrcode && html5Qrcode.isScanning) {
        try { await html5Qrcode.stop(); } catch (err) { console.error(err); }
    }
});
</script>

<template>
    <div class="camera-container" :class="[detectedTypeClass, { 'state-success': isScanned }]">
        <div :id="containerId" class="video-viewport"></div>

        <!-- Скрытый графический движок для моментального скриншота -->
        <canvas id="screenshot-canvas" style="display: none;"></canvas>

        <!-- Прелоадер -->
        <div v-if="isCameraLoading" class="camera-overlay loading">
            <div class="spinner"></div>
            <p>Включение камеры...</p>
        </div>

        <!-- Интерфейс сканера -->
        <div v-if="!isCameraLoading && !cameraError" class="camera-overlay ui">
            <div v-if="!isScanned" class="laser-line"></div>

            <div v-if="isScanned" class="success-badge">
                <div class="success-icon">✓</div>
                <div class="code-type-label">{{ detectedTypeName }}</div>
            </div>

            <p class="scan-tip">
                {{ isScanned ? 'Успешно считано!' : 'Поместите код в рамку' }}
            </p>
        </div>
    </div>
</template>

<style scoped>
.camera-container { position: relative; width: 100%; max-width: 450px; margin: 0 auto; border-radius: 24px; overflow: hidden; background-color: #000; transition: box-shadow 0.3s ease; }
.camera-container.type-qrcode { box-shadow: 0 0 30px rgba(59, 130, 246, 0.8); }
.camera-container.type-datamatrix { box-shadow: 0 0 30px rgba(168, 85, 247, 0.8); }
.video-viewport :deep(video) { width: 100% !important; height: 100% !important; object-fit: cover !important; }
.camera-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #fff; pointer-events: none; }
.video-viewport :deep(#qr-camera-stream__scan_region) { border: 2px solid rgba(255, 255, 255, 0.4) !important; border-radius: 16px; box-shadow: 0 0 0 2000px rgba(0, 0, 0, 0.5); transition: border-color 0.2s ease; }
.camera-container.type-qrcode :deep(#qr-camera-stream__scan_region) { border-color: #3b82f6 !important; }
.camera-container.type-datamatrix :deep(#qr-camera-stream__scan_region) { border-color: #a855f7 !important; }
.laser-line { position: absolute; width: 65%; height: 3px; background: linear-gradient(90deg, transparent, #ef4444, transparent); box-shadow: 0 0 8px #ef4444; animation: scanAnimation 2s linear infinite; }
@keyframes scanAnimation { 0% { top: 20%; } 50% { top: 75%; } 100% { top: 20%; } }
.success-badge { background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(8px); padding: 16px 24px; border-radius: 16px; display: flex; flex-direction: column; align-items: center; gap: 8px; animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); border: 1px solid rgba(255, 255, 255, 0.1); }
.success-icon { font-size: 32px; font-weight: bold; }
.type-qrcode .success-icon { color: #3b82f6; }
.type-datamatrix .success-icon { color: #a855f7; }
.code-type-label { font-size: 16px; font-weight: 600; letter-spacing: 0.5px; }
@keyframes popIn { 0% { transform: scale(0.8); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
.scan-tip { position: absolute; bottom: 20px; background: rgba(0, 0, 0, 0.7); padding: 8px 16px; border-radius: 20px; font-size: 14px; }
.spinner { width: 40px; height: 40px; border: 4px solid rgba(255, 255, 255, 0.1); border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 12px; }
@keyframes spin { to { transform: rotate(360deg); } }
.camera-overlay.loading { background: #111; }
</style>

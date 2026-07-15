<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import { Html5Qrcode, Html5QrcodeSupportedFormats } from 'html5-qrcode';

const emit = defineEmits<{
    (e: 'scanned', data: { text: string; type: string; snapshot: string }): void;
}>();

const containerId = 'qr-camera-stream';
let html5Qrcode: Html5Qrcode | null = null;

const isCameraLoading = ref(true);
const cameraError = ref<string | null>(null);
const isScanned = ref(false);
const isAnalyzing = ref(false);

let lastDecodedText = '';
let lastDecodedResult: any = null;

const BOX_RATIO = 0.7;

const startCameraStream = async () => {
    isCameraLoading.value = true;
    try {
        if (html5Qrcode && html5Qrcode.isScanning) {
            await html5Qrcode.stop();
        }
        html5Qrcode = new Html5Qrcode(containerId);
        await html5Qrcode.start(
            { facingMode: "environment" },
            {
                fps: 26,
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
                lastDecodedText = decodedText;
                lastDecodedResult = decodedResult;
            },
            () => {
                lastDecodedText = '';
                lastDecodedResult = null;
            }
        );
        isCameraLoading.value = false;
    } catch (err: any) {
        isCameraLoading.value = false;
        cameraError.value = 'Ошибка доступа к камере.';
    }
};

onMounted(async () => {
    await startCameraStream();
});

const captureAndScan = async () => {
    if (isScanned.value || isAnalyzing.value) return;
    isAnalyzing.value = true;

    if (!lastDecodedText || !lastDecodedResult) {
        alert('Код еще не пойман в фокус. Наведите рамку точнее на код и нажмите кнопку.');
        bufferReset();
        isAnalyzing.value = false;
        await startCameraStream();
        return;
    }

    const video = document.querySelector(`#${containerId} video`) as HTMLVideoElement;
    if (!video) { isAnalyzing.value = false; return; }

    isScanned.value = true;

    const rawFormat = lastDecodedResult.result?.format?.formatName;
    let typeName = 'QR-код';
    let highlightColor = '#3b82f6';

    if (rawFormat === 'DATA_MATRIX') {
        typeName = 'DataMatrix (Маркировка)';
        highlightColor = '#a855f7';
    }

    const snapshotBase64 = makeFocusedSnapshot(video, highlightColor);

    emit('scanned', {
        text: lastDecodedText,
        type: typeName,
        snapshot: snapshotBase64
    });

    if (navigator.vibrate) navigator.vibrate(120);
    isAnalyzing.value = false;
};

const bufferReset = () => {
    lastDecodedText = '';
    lastDecodedResult = null;
};

const makeFocusedSnapshot = (video: HTMLVideoElement, color: string): string => {
    const canvas = document.getElementById('screenshot-canvas') as HTMLCanvasElement;
    const ctx = canvas!.getContext('2d')!;

    const videoWidth = video.videoWidth;
    const videoHeight = video.videoHeight;
    const minVideoSize = Math.min(videoWidth, videoHeight);
    const boxSize = Math.floor(minVideoSize * BOX_RATIO);

    const sx = (videoWidth - boxSize) / 2;
    const sy = (videoHeight - boxSize) / 2;

    canvas.width = 450;
    canvas.height = 450;

    // 1. Слой подложки: Рисуем чистый кадр из видеопотока
    ctx.drawImage(video, sx, sy, boxSize, boxSize, 0, 0, 450, 450);

    // 2. Слой вуали: Затемняем весь кадр полупрозрачным черным цветом
    ctx.fillStyle = 'rgba(0, 0, 0, 0.65)';
    ctx.fillRect(0, 0, 450, 450);

    const points = lastDecodedResult?.result?.textPoints;

    const mapPoint = (p: { x: number; y: number }) => {
        const relativeX = p.x - sx;
        const relativeY = p.y - sy;
        return {
            x: (relativeX / boxSize) * 450,
            y: (relativeY / boxSize) * 450
        };
    };

    if (points && points.length >= 3) {
        // --- СЦЕНАРИЙ А: Есть точные аппаратно-определенные координаты кода ---

        // Вырезаем маску (удаляем черный фон внутри контура кода)
        ctx.save();
        ctx.globalCompositeOperation = 'destination-out';
        ctx.beginPath();
        const p0 = mapPoint(points[0]);
        ctx.moveTo(p0.x, p0.y);
        for (let i = 1; i < points.length; i++) {
            const pt = mapPoint(points[i]);
            ctx.lineTo(pt.x, pt.y);
        }
        ctx.closePath();
        ctx.fill();
        ctx.restore(); // Сбрасываем режим destination-out обратно на обычное рисование

        // Рисуем неоновую рамку строго по этим же точкам
        ctx.save();
        ctx.strokeStyle = color;
        ctx.lineWidth = 5;
        ctx.lineJoin = 'round';
        ctx.shadowBlur = 14;
        ctx.shadowColor = color;

        ctx.beginPath();
        ctx.moveTo(p0.x, p0.y);
        for (let i = 1; i < points.length; i++) {
            const pt = mapPoint(points[i]);
            ctx.lineTo(pt.x, pt.y);
        }
        ctx.closePath();
        ctx.stroke();
        ctx.restore();

    } else {
        // --- СЦЕНАРИЙ Б: Подстраховка (если библиотека вернула пустой массив точек) ---
        const isDataMatrix = lastDecodedResult?.result?.format?.formatName === 'DATA_MATRIX';

        const maskWidth = 180;
        const maskHeight = 180;
        const maskY = 135;
        const maskX = isDataMatrix ? 245 : 25;
        const radius = 16;

        ctx.save();
        ctx.globalCompositeOperation = 'destination-out';
        ctx.beginPath();
        ctx.moveTo(maskX + radius, maskY);
        ctx.lineTo(maskX + maskWidth - radius, maskY);
        ctx.quadraticCurveTo(maskX + maskWidth, maskY, maskX + maskWidth, maskY + radius);
        ctx.lineTo(maskX + maskWidth, maskY + maskHeight - radius);
        ctx.quadraticCurveTo(maskX + maskWidth, maskY + maskHeight, maskX + maskWidth - radius, maskY + maskHeight);
        ctx.lineTo(maskX + radius, maskY + maskHeight);
        ctx.quadraticCurveTo(maskX, maskY + maskHeight, maskX, maskY + maskHeight - radius);
        ctx.lineTo(maskX, maskY + radius);
        ctx.quadraticCurveTo(maskX, maskY, maskX + radius, maskY);
        ctx.closePath();
        ctx.fill();
        ctx.restore();

        ctx.save();
        ctx.strokeStyle = color;
        ctx.lineWidth = 5;
        ctx.lineJoin = 'round';
        ctx.shadowBlur = 12;
        ctx.shadowColor = color;
        ctx.strokeRect(maskX, maskY, maskWidth, maskHeight);
        ctx.restore();
    }

    return canvas.toDataURL('image/jpeg', 0.85);
};

onUnmounted(async () => {
    if (html5Qrcode && html5Qrcode.isScanning) {
        try { await html5Qrcode.stop(); } catch (err) { console.error(err); }
    }
});
</script>

<template>
    <div class="camera-container" :class="{ 'state-success': isScanned }">
        <div :id="containerId" class="video-viewport"></div>
        <canvas id="screenshot-canvas" style="display: none;"></canvas>

        <div v-if="isCameraLoading" class="camera-overlay loading">
            <div class="spinner"></div>
            <p>Включение...</p>
        </div>

        <div v-if="!isCameraLoading && !cameraError" class="camera-overlay ui">
            <div v-if="!isScanned" class="laser-line"></div>

            <button v-if="!isScanned" @click="captureAndScan" class="capture-btn">
                <div class="capture-btn-inner"></div>
            </button>
            <p class="scan-tip">Наведите прицел и нажмите кнопку</p>
        </div>
    </div>
</template>

<style scoped>
.camera-container { position: relative; width: 100%; max-width: 450px; margin: 0 auto; border-radius: 24px; overflow: hidden; background-color: #000; }
.video-viewport :deep(video) { width: 100% !important; height: 100% !important; object-fit: cover !important; }
.camera-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #fff; }
.video-viewport :deep(#qr-camera-stream__scan_region) { border: 2px solid rgba(255, 255, 255, 0.4) !important; border-radius: 16px; box-shadow: 0 0 0 2000px rgba(0, 0, 0, 0.5); }
.laser-line { position: absolute; width: 65%; height: 3px; background: linear-gradient(90deg, transparent, #ef4444, transparent); box-shadow: 0 0 8px #ef4444; animation: scanAnimation 2s linear infinite; }
@keyframes scanAnimation { 0% { top: 20%; } 50% { top: 75%; } 100% { top: 20%; } }
.scan-tip { position: absolute; bottom: 20px; background: rgba(0, 0, 0, 0.7); padding: 8px 16px; border-radius: 20px; font-size: 14px; pointer-events: none; }
.capture-btn { position: absolute; bottom: 60px; width: 72px; height: 72px; border-radius: 50%; background: rgba(255, 255, 255, 0.3); border: 4px solid #ffffff; display: flex; align-items: center; justify-content: center; cursor: pointer; }
.capture-btn-inner { width: 52px; height: 52px; border-radius: 50%; background: #ffffff; }
.spinner { width: 40px; height: 40px; border: 4px solid rgba(255, 255, 255, 0.1); border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 12px; }
@keyframes spin { to { transform: rotate(360deg); } }
.camera-overlay.loading { background: #111; }
</style>

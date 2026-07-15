<template>
    <div class="features-block">
        <QrScanner v-if="!scanResult.text" :key="scannerKey" @scanned="handleQrResult" />

        <div v-else class="result-box">
            <div class="snapshot-wrapper">
                <!-- ТЕПЕРЬ ТУТ НАСТОЯЩЕЕ ФОТО: выводится динамический снимок из камеры -->
                <img :src="scanResult.snapshot" alt="Фокус на коде" class="snapshot-img" />

                <span class="badge" :class="isQrCode ? 'badge-blue' : 'badge-purple'">
                    {{ scanResult.type }}
                </span>
            </div>

            <div class="result-header">
                <p class="status-msg">Код успешно распознан</p>
            </div>

            <code>{{ scanResult.text }}</code>

            <div class="actions-grid">
                <button v-if="isLink" @click="goToLink" class="btn-action btn-primary">
                    <span>Перейти по ссылке</span>
                </button>

                <button @click="resetScanner" class="btn-action btn-secondary">
                    <span>Сканировать заново</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import QrScanner from './QrScanner.vue';

const scanResult = ref({ text: '', type: '', snapshot: '' });
const scannerKey = ref(0);

const handleQrResult = (data: { text: string; type: string; snapshot: string }) => {
    scanResult.value = data;
};

const isLink = computed(() => {
    const text = scanResult.value.text.toLowerCase().trim();
    return text.startsWith('http://') || text.startsWith('https://');
});

const isQrCode = computed(() => {
    return scanResult.value.type.includes('QR');
});

const goToLink = () => {
    if (isLink.value) window.location.href = scanResult.value.text.trim();
};

const resetScanner = () => {
    scanResult.value = { text: '', type: '', snapshot: '' };
    scannerKey.value++;
};
</script>

<style scoped>
.result-box { margin: 24px auto 0; padding: 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 28px; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.06); max-width: 450px; overflow: hidden; }
.snapshot-wrapper { position: relative; width: 100%; aspect-ratio: 1 / 1; border-radius: 18px; overflow: hidden; margin-bottom: 16px; background-color: #000; }
.snapshot-img { width: 100%; height: 100%; object-fit: cover; animation: flashEffect 0.4s ease-out; }
@keyframes flashEffect { 0% { filter: brightness(2.5) contrast(1.2); } 100% { filter: brightness(1) contrast(1); } }
.result-header { display: flex; align-items: center; margin-bottom: 10px; }
.status-msg { font-size: 13px; color: #94a3b8; text-transform: uppercase; font-weight: 700; letter-spacing: 0.6px; }
.badge { position: absolute; top: 14px; right: 14px; padding: 6px 14px; border-radius: 20px; font-size: 12px; color: #fff; font-weight: 700; z-index: 10; }
.badge-blue { background-color: #3b82f6; }
.badge-purple { background-color: #a855f7; }
code { display: block; background: #0f172a; color: #38bdf8; padding: 14px; border-radius: 12px; font-family: monospace; word-break: break-all; font-size: 14px; line-height: 1.4; margin-bottom: 18px; }
.actions-grid { display: flex; flex-direction: column; gap: 10px; }
.btn-action { width: 100%; border: none; padding: 14px 20px; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background-color 0.2s; }
.btn-primary { background-color: #10b981; color: #ffffff; }
.btn-secondary { background-color: #e2e8f0; color: #334155; }
</style>

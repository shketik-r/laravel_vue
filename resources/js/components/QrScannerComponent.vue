<template>
    <div class="scanner-wrapper">
        <!-- Показываем макро-камеру -->
        <QrScanner v-if="!scanResult.text" :key="scannerKey" @scanned="handleQrResult" />

        <!-- Карточка отображения зашитого текста -->
        <div v-else class="result-card">
            <div class="result-header">
                <span class="badge bg-purple">
                    {{ scanResult.type }}
                </span>
                <span class="status-text">Данные успешно извлечены</span>
            </div>

            <!-- Блок вывода расшифрованного текста кода маркировки -->
            <div class="code-preview">
                <label class="input-label">Зашитый текст (Строка маркировки):</label>
                <code>{{ scanResult.text }}</code>
            </div>

            <div class="actions-container">
                <!-- Если зашита ссылка — кнопка перехода -->
                <button v-if="isLink" @click="goToLink" class="btn btn-primary">
                    <span>Перейти по ссылке</span>
                </button>

                <!-- Если зашит текст маркировки — кнопка копирования в буфер обмена -->
                <button v-else @click="copyToClipboard" class="btn btn-copy">
                    <span>{{ isCopied ? '✓ Текст скопирован' : 'Скопировать код в буфер' }}</span>
                </button>

                <!-- Кнопка сброса возвращает интерфейс к камере -->
                <button @click="resetScanner" class="btn btn-secondary">
                    <span>Сканировать заново</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import QrScanner from './QrScanner.vue';

const scanResult = ref({ text: '', type: '' });
const scannerKey = ref(0);
const isCopied = ref(false);

const handleQrResult = (data: { text: string; type: string }) => {
    scanResult.value = data;
    isCopied.value = false; // сброс статуса копирования
};

const isLink = computed(() => {
    const text = scanResult.value.text.toLowerCase().trim();
    return text.startsWith('http://') || text.startsWith('https://');
});

const goToLink = () => {
    if (isLink.value) {
        window.location.href = scanResult.value.text.trim();
    }
};

// Функция быстрого копирования криптохвоста DataMatrix
const copyToClipboard = async () => {
    try {
        await navigator.clipboard.writeText(scanResult.value.text);
        isCopied.value = true;
        setTimeout(() => { isCopied.value = false; }, 2000);
    } catch (err) {
        console.error('Не удалось скопировать текст:', err);
    }
};

const resetScanner = () => {
    scanResult.value = { text: '', type: '' };
    scannerKey.value++;
};
</script>

<style scoped>
.scanner-wrapper { padding: 16px; max-width: 450px; margin: 0 auto; font-family: sans-serif; }
.result-card { background: #ffffff; border-radius: 24px; box-shadow: 0 12px 30px rgba(0, 0, 0, 0.06); border: 1px solid #e2e8f0; padding: 20px; }
.result-header { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }

.badge { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 0.5px; }
.bg-purple { background-color: #a855f7; box-shadow: 0 4px 10px rgba(168, 85, 247, 0.25); }
.status-text { font-size: 14px; color: #94a3b8; font-weight: 600; }

.code-preview { margin-bottom: 20px; }
.input-label { font-size: 13px; font-weight: 700; color: #64748b; display: block; margin-bottom: 8px; }

code {
    display: block;
    background: #0f172a;
    color: #38bdf8;
    padding: 16px;
    border-radius: 14px;
    font-family: monospace;
    word-break: break-all; /* Автоперенос длинной строки DataMatrix */
    font-size: 13px;
    line-height: 1.5;
}

.actions-container { display: flex; flex-direction: column; gap: 10px; }

.btn { width: 100%; border: none; padding: 15px; border-radius: 14px; font-size: 16px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background-color 0.2s, transform 0.1s; }
.btn:active { transform: scale(0.98); }

.btn-primary { background-color: #10b981; color: #ffffff; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); }
.btn-primary:active { background-color: #059669; }

.btn-copy { background-color: #475569; color: #ffffff; box-shadow: 0 4px 12px rgba(71, 85, 105, 0.15); }
.btn-copy:active { background-color: #334155; }

.btn-secondary { background-color: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
.btn-secondary:active { background-color: #e2e8f0; }
</style>

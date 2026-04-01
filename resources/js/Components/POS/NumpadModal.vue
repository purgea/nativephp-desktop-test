<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    mode: String,
});

const emit = defineEmits(['close', 'submit']);

const display = ref('');
const label = ref('');

const modeLabel = computed(() => {
    const labels = {
        typeIn: 'Type In Amount',
        qty: 'Enter Quantity',
        changeQty: 'Change Quantity',
        openFood: 'Open Food Price',
        openBar: 'Open Bar Price',
    };
    return labels[props.mode] || 'Enter Value';
});

function press(val) {
    if (val === '.' && display.value.includes('.')) return;
    if (val === '.' && display.value === '') { display.value = '0'; }
    display.value += val;
}

function backspace() {
    display.value = display.value.slice(0, -1);
}

function clear() {
    display.value = '';
    label.value = '';
}

function submit() {
    if (!display.value) return;
    emit('submit', display.value, label.value);
}

const numKeys = [
    ['7', '8', '9'],
    ['4', '5', '6'],
    ['1', '2', '3'],
    ['0', '00', '.'],
];
</script>

<template>
    <div class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4" @click.self="$emit('close')">
        <div class="bg-wolf-bg-card border border-wolf-border rounded-xl shadow-2xl w-80 overflow-hidden">
            <!-- Header -->
            <div class="bg-wolf-bg-elevated px-5 py-3 border-b border-wolf-border flex items-center justify-between">
                <h3 class="text-wolf-cream font-bold text-sm">{{ modeLabel }}</h3>
                <button @click="$emit('close')" class="text-wolf-text-muted hover:text-wolf-cream text-xl leading-none">&times;</button>
            </div>

            <!-- Display -->
            <div class="p-4">
                <div class="bg-wolf-bg rounded-lg border border-wolf-border p-3 mb-3">
                    <div class="text-right text-2xl font-mono font-bold text-wolf-cream min-h-[36px]">
                        <span v-if="mode !== 'qty' && mode !== 'changeQty'" class="text-wolf-gold text-lg mr-1">$</span>{{ display || '0' }}
                    </div>
                </div>

                <!-- Label input for open food/bar -->
                <div v-if="mode === 'openFood' || mode === 'openBar'" class="mb-3">
                    <input
                        v-model="label"
                        type="text"
                        :placeholder="mode === 'openBar' ? 'Drink name...' : 'Item name...'"
                        class="w-full bg-wolf-bg border border-wolf-border rounded-lg px-3 py-2 text-sm text-wolf-cream placeholder-wolf-text-muted focus:border-wolf-gold focus:ring-1 focus:ring-wolf-gold/30 outline-none"
                    />
                </div>

                <!-- Numpad -->
                <div class="grid grid-cols-3 gap-1.5">
                    <template v-for="row in numKeys" :key="row.join()">
                        <button
                            v-for="key in row"
                            :key="key"
                            @click="press(key)"
                            class="bg-wolf-bg-elevated hover:bg-wolf-border border border-wolf-border rounded-lg py-3 text-lg font-bold text-wolf-cream transition-colors pos-btn-press"
                        >
                            {{ key }}
                        </button>
                    </template>
                </div>

                <!-- Bottom row -->
                <div class="grid grid-cols-3 gap-1.5 mt-1.5">
                    <button @click="clear" class="bg-wolf-burgundy/30 hover:bg-wolf-burgundy/50 border border-wolf-burgundy/40 rounded-lg py-3 text-xs font-bold text-wolf-burgundy-light transition-colors pos-btn-press">
                        CLEAR
                    </button>
                    <button @click="backspace" class="bg-wolf-bg-elevated hover:bg-wolf-border border border-wolf-border rounded-lg py-3 text-xs font-bold text-wolf-cream transition-colors pos-btn-press">
                        ← DEL
                    </button>
                    <button @click="submit" class="bg-wolf-gold hover:bg-wolf-gold-light rounded-lg py-3 text-xs font-bold text-wolf-bg transition-colors pos-btn-press">
                        ENTER
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

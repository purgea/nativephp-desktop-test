<script setup>
import { ref } from 'vue';

const props = defineProps({
    subtotal: Number,
});

const emit = defineEmits(['close', 'apply']);

const type = ref('percent');
const value = ref('');

const presets = [5, 10, 15, 20, 25, 50];

function selectPreset(pct) {
    type.value = 'percent';
    value.value = pct.toString();
}

function apply() {
    const v = parseFloat(value.value) || 0;
    if (v <= 0) return;
    emit('apply', type.value, v);
}
</script>

<template>
    <div class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4" @click.self="$emit('close')">
        <div class="bg-wolf-bg-card border border-wolf-border rounded-xl shadow-2xl w-80 overflow-hidden">
            <div class="bg-wolf-bg-elevated px-5 py-3 border-b border-wolf-border flex items-center justify-between">
                <h3 class="text-wolf-cream font-bold text-sm">Apply Discount</h3>
                <button @click="$emit('close')" class="text-wolf-text-muted hover:text-wolf-cream text-xl leading-none">&times;</button>
            </div>

            <div class="p-4 space-y-4">
                <!-- Type Toggle -->
                <div class="flex rounded-lg overflow-hidden border border-wolf-border">
                    <button
                        @click="type = 'percent'"
                        class="flex-1 py-2.5 text-xs font-bold transition-colors"
                        :class="type === 'percent' ? 'bg-wolf-gold text-wolf-bg' : 'bg-wolf-bg-elevated text-wolf-cream-dark'"
                    >
                        Percentage %
                    </button>
                    <button
                        @click="type = 'fixed'"
                        class="flex-1 py-2.5 text-xs font-bold transition-colors"
                        :class="type === 'fixed' ? 'bg-wolf-gold text-wolf-bg' : 'bg-wolf-bg-elevated text-wolf-cream-dark'"
                    >
                        Fixed Amount $
                    </button>
                </div>

                <!-- Quick Presets -->
                <div v-if="type === 'percent'" class="grid grid-cols-3 gap-1.5">
                    <button
                        v-for="pct in presets"
                        :key="pct"
                        @click="selectPreset(pct)"
                        class="rounded-lg py-3 text-sm font-bold border transition-all pos-btn-press"
                        :class="value == pct && type === 'percent'
                            ? 'bg-wolf-gold/15 border-wolf-gold text-wolf-gold'
                            : 'bg-wolf-bg-elevated border-wolf-border text-wolf-cream-dark hover:border-wolf-gold/50'
                        "
                    >
                        {{ pct }}%
                    </button>
                </div>

                <!-- Manual Input -->
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-wolf-gold font-bold">{{ type === 'percent' ? '%' : '$' }}</span>
                    <input
                        v-model="value"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="0"
                        class="w-full bg-wolf-bg border border-wolf-border rounded-lg pl-8 pr-3 py-3 text-lg text-wolf-cream font-mono text-center focus:border-wolf-gold focus:ring-1 focus:ring-wolf-gold/30 outline-none"
                    />
                </div>

                <!-- Preview -->
                <div v-if="value" class="text-center text-wolf-text-secondary text-xs">
                    Discount: <span class="text-wolf-gold font-bold">
                        {{ type === 'percent' ? `${value}% = $${(subtotal * (parseFloat(value) || 0) / 100).toFixed(2)}` : `$${parseFloat(value).toFixed(2)}` }}
                    </span>
                </div>
            </div>

            <div class="bg-wolf-bg-elevated px-5 py-3 border-t border-wolf-border flex justify-end gap-2">
                <button @click="$emit('close')" class="px-4 py-2 text-xs font-bold text-wolf-text-muted hover:text-wolf-cream bg-wolf-bg-card border border-wolf-border rounded-lg transition-colors">
                    Cancel
                </button>
                <button @click="apply" class="px-6 py-2 text-xs font-bold text-wolf-bg bg-wolf-gold hover:bg-wolf-gold-light rounded-lg transition-colors pos-btn-press">
                    Apply
                </button>
            </div>
        </div>
    </div>
</template>

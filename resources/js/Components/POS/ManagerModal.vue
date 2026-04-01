<script setup>
import { ref } from 'vue';

const props = defineProps({
    items: Array,
    selectedIndex: Number,
});

const emit = defineEmits(['close', 'void', 'comp', 'open-drawer', 'reprint']);

const pin = ref('');
const authenticated = ref(false);

function authenticate() {
    // Demo: any 4-digit pin works
    if (pin.value.length >= 4) {
        authenticated.value = true;
    }
}

function pressKey(k) {
    if (pin.value.length < 6) pin.value += k;
}

const actions = [
    { key: 'void', label: 'Void Item', icon: '🗑️', desc: 'Remove a sent item from the order', color: 'bg-red-900/40 border-red-800/50 text-red-300 hover:bg-red-900/60' },
    { key: 'comp', label: 'Comp Item', icon: '🎁', desc: 'Zero out an item as complimentary', color: 'bg-amber-900/40 border-amber-800/50 text-amber-300 hover:bg-amber-900/60' },
    { key: 'open-drawer', label: 'Open Drawer', icon: '🗄️', desc: 'Open the cash register drawer', color: 'bg-blue-900/40 border-blue-800/50 text-blue-300 hover:bg-blue-900/60' },
    { key: 'reprint', label: 'Reprint Check', icon: '🖨️', desc: 'Print a copy of the current check', color: 'bg-purple-900/40 border-purple-800/50 text-purple-300 hover:bg-purple-900/60' },
];
</script>

<template>
    <div class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4" @click.self="$emit('close')">
        <div class="bg-wolf-bg-card border border-wolf-border rounded-xl shadow-2xl w-96 overflow-hidden">
            <div class="bg-wolf-burgundy-dark px-5 py-3 border-b border-wolf-border flex items-center justify-between">
                <h3 class="text-wolf-cream font-bold text-base">Manager Functions</h3>
                <button @click="$emit('close')" class="text-wolf-cream/50 hover:text-wolf-cream text-xl leading-none">&times;</button>
            </div>

            <!-- PIN Entry -->
            <div v-if="!authenticated" class="p-6">
                <div class="text-center mb-4">
                    <div class="text-wolf-text-secondary text-xs mb-2">Enter Manager PIN</div>
                    <div class="flex justify-center gap-2 mb-4">
                        <div v-for="i in 4" :key="i" class="w-4 h-4 rounded-full border-2 border-wolf-border" :class="pin.length >= i ? 'bg-wolf-gold border-wolf-gold' : ''"></div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 max-w-[200px] mx-auto">
                        <button v-for="k in [1,2,3,4,5,6,7,8,9,'',0,'⌫']" :key="k"
                            @click="k === '⌫' ? pin = pin.slice(0,-1) : (k !== '' && pressKey(k.toString()))"
                            class="rounded-lg py-3 text-lg font-bold transition-colors pos-btn-press"
                            :class="k === '' ? 'opacity-0 pointer-events-none' : 'bg-wolf-bg-elevated border border-wolf-border text-wolf-cream hover:bg-wolf-border'"
                        >
                            {{ k }}
                        </button>
                    </div>
                    <button @click="authenticate" class="mt-4 px-8 py-2 bg-wolf-gold text-wolf-bg font-bold text-sm rounded-lg pos-btn-press hover:bg-wolf-gold-light transition-colors">
                        Enter
                    </button>
                </div>
            </div>

            <!-- Actions -->
            <div v-else class="p-4 space-y-2">
                <button
                    v-for="action in actions"
                    :key="action.key"
                    @click="$emit(action.key, selectedIndex)"
                    class="w-full flex items-center gap-3 rounded-lg p-3 border transition-all pos-btn-press"
                    :class="action.color"
                >
                    <span class="text-2xl">{{ action.icon }}</span>
                    <div class="text-left">
                        <div class="text-sm font-bold">{{ action.label }}</div>
                        <div class="text-[10px] opacity-60">{{ action.desc }}</div>
                    </div>
                </button>
            </div>

            <div class="bg-wolf-bg-elevated px-5 py-3 border-t border-wolf-border flex justify-end">
                <button @click="$emit('close')" class="px-4 py-2 text-xs font-bold text-wolf-text-muted hover:text-wolf-cream bg-wolf-bg-card border border-wolf-border rounded-lg transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</template>

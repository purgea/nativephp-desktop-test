<script setup>
import { ref } from 'vue';

const props = defineProps({
    modifiers: Object,
    currentMods: Array,
    itemName: String,
});

const emit = defineEmits(['close', 'apply']);

const selectedMods = ref([...(props.currentMods || [])]);
const activeTab = ref('temps');

function toggleMod(mod) {
    const existing = selectedMods.value.findIndex(m => m.name === mod.name);
    if (existing >= 0) {
        selectedMods.value.splice(existing, 1);
    } else {
        selectedMods.value.push({ name: mod.name, price: mod.price || 0 });
    }
}

function isSelected(mod) {
    return selectedMods.value.some(m => m.name === mod.name);
}

function apply() {
    emit('apply', selectedMods.value);
}

const tabs = [
    { key: 'temps', label: 'Temperature' },
    { key: 'prep', label: 'Preparation' },
    { key: 'addons', label: 'Add-Ons' },
];
</script>

<template>
    <div class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4" @click.self="$emit('close')">
        <div class="bg-wolf-bg-card border border-wolf-border rounded-xl shadow-2xl w-full max-w-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-wolf-bg-elevated px-5 py-3 border-b border-wolf-border flex items-center justify-between">
                <div>
                    <h3 class="text-wolf-cream font-bold text-base">Modify Item</h3>
                    <p class="text-wolf-gold text-xs font-medium mt-0.5">{{ itemName }}</p>
                </div>
                <button @click="$emit('close')" class="text-wolf-text-muted hover:text-wolf-cream text-xl leading-none">&times;</button>
            </div>

            <!-- Tabs -->
            <div class="flex border-b border-wolf-border">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    @click="activeTab = tab.key"
                    class="flex-1 py-2.5 text-xs font-bold uppercase tracking-wider transition-colors border-b-2"
                    :class="activeTab === tab.key ? 'text-wolf-gold border-wolf-gold bg-wolf-bg-elevated/50' : 'text-wolf-text-muted border-transparent hover:text-wolf-cream'"
                >
                    {{ tab.label }}
                </button>
            </div>

            <!-- Content -->
            <div class="p-4 max-h-[300px] overflow-y-auto">
                <div class="grid grid-cols-3 gap-2">
                    <button
                        v-for="mod in modifiers[activeTab] || []"
                        :key="mod.name"
                        @click="toggleMod(mod)"
                        class="rounded-lg px-2 py-3 text-xs font-bold text-center transition-all pos-btn-press border"
                        :class="isSelected(mod)
                            ? 'bg-wolf-gold/20 border-wolf-gold text-wolf-gold ring-1 ring-wolf-gold/30'
                            : 'bg-wolf-bg-elevated border-wolf-border text-wolf-cream-dark hover:border-wolf-gold/50'
                        "
                    >
                        <div>{{ mod.abbr || mod.name }}</div>
                        <div v-if="mod.price" class="text-[9px] mt-1 opacity-60">+${{ mod.price.toFixed(2) }}</div>
                    </button>
                </div>
            </div>

            <!-- Selected Modifiers -->
            <div v-if="selectedMods.length" class="px-4 pb-3">
                <div class="flex flex-wrap gap-1.5">
                    <span
                        v-for="mod in selectedMods"
                        :key="mod.name"
                        class="text-[10px] bg-wolf-gold/15 text-wolf-gold border border-wolf-gold/30 px-2 py-0.5 rounded-full font-medium"
                    >
                        {{ mod.name }}
                        <button @click="toggleMod(mod)" class="ml-1 opacity-60 hover:opacity-100">&times;</button>
                    </span>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-wolf-bg-elevated px-5 py-3 border-t border-wolf-border flex justify-end gap-2">
                <button @click="$emit('close')" class="px-4 py-2 text-xs font-bold text-wolf-text-muted hover:text-wolf-cream bg-wolf-bg-card border border-wolf-border rounded-lg transition-colors">
                    Cancel
                </button>
                <button @click="apply" class="px-6 py-2 text-xs font-bold text-wolf-bg bg-wolf-gold hover:bg-wolf-gold-light rounded-lg transition-colors pos-btn-press">
                    Apply Modifiers
                </button>
            </div>
        </div>
    </div>
</template>

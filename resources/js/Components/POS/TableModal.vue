<script setup>
import { ref } from 'vue';

const emit = defineEmits(['close', 'select']);

const floors = ref([
    {
        name: 'Main Dining',
        tables: [
            { num: 1, seats: 2, status: 'available' },
            { num: 2, seats: 2, status: 'occupied' },
            { num: 3, seats: 4, status: 'available' },
            { num: 4, seats: 4, status: 'available' },
            { num: 5, seats: 6, status: 'occupied' },
            { num: 6, seats: 6, status: 'available' },
            { num: 7, seats: 4, status: 'available' },
            { num: 8, seats: 2, status: 'reserved' },
            { num: 9, seats: 8, status: 'available' },
            { num: 10, seats: 4, status: 'occupied' },
        ],
    },
    {
        name: 'Private Room',
        tables: [
            { num: 21, seats: 10, status: 'available' },
            { num: 22, seats: 12, status: 'reserved' },
            { num: 23, seats: 8, status: 'available' },
        ],
    },
    {
        name: 'Bar Area',
        tables: [
            { num: 31, seats: 2, status: 'available' },
            { num: 32, seats: 2, status: 'occupied' },
            { num: 33, seats: 4, status: 'available' },
            { num: 34, seats: 2, status: 'available' },
        ],
    },
]);

const activeFloor = ref(0);

function statusColor(status) {
    return {
        available: 'bg-emerald-900/40 border-emerald-700/50 text-emerald-300 hover:bg-emerald-900/60 hover:border-emerald-600',
        occupied: 'bg-red-900/40 border-red-700/50 text-red-300 cursor-not-allowed opacity-60',
        reserved: 'bg-amber-900/40 border-amber-700/50 text-amber-300 hover:bg-amber-900/60 hover:border-amber-600',
    }[status];
}

function statusLabel(status) {
    return { available: 'Open', occupied: 'In Use', reserved: 'Reserved' }[status];
}
</script>

<template>
    <div class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4" @click.self="$emit('close')">
        <div class="bg-wolf-bg-card border border-wolf-border rounded-xl shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="bg-wolf-bg-elevated px-5 py-3 border-b border-wolf-border flex items-center justify-between">
                <h3 class="text-wolf-cream font-bold text-base">Select Table</h3>
                <button @click="$emit('close')" class="text-wolf-text-muted hover:text-wolf-cream text-xl leading-none">&times;</button>
            </div>

            <!-- Floor Tabs -->
            <div class="flex border-b border-wolf-border">
                <button
                    v-for="(floor, i) in floors"
                    :key="i"
                    @click="activeFloor = i"
                    class="flex-1 py-2.5 text-xs font-bold uppercase tracking-wider transition-colors border-b-2"
                    :class="activeFloor === i ? 'text-wolf-gold border-wolf-gold bg-wolf-bg-elevated/50' : 'text-wolf-text-muted border-transparent hover:text-wolf-cream'"
                >
                    {{ floor.name }}
                </button>
            </div>

            <!-- Tables Grid -->
            <div class="p-4">
                <div class="grid grid-cols-5 gap-2">
                    <button
                        v-for="table in floors[activeFloor].tables"
                        :key="table.num"
                        @click="table.status !== 'occupied' && $emit('select', table.num)"
                        class="rounded-lg p-3 text-center border transition-all pos-btn-press"
                        :class="statusColor(table.status)"
                    >
                        <div class="text-lg font-black">{{ table.num }}</div>
                        <div class="text-[9px] mt-0.5 opacity-70">{{ table.seats }} seats</div>
                        <div class="text-[8px] mt-1 uppercase font-semibold tracking-wider">{{ statusLabel(table.status) }}</div>
                    </button>
                </div>

                <!-- Legend -->
                <div class="flex items-center gap-4 mt-4 justify-center">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded bg-emerald-700/60 border border-emerald-600/50"></span>
                        <span class="text-[10px] text-wolf-text-muted">Available</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded bg-red-700/60 border border-red-600/50"></span>
                        <span class="text-[10px] text-wolf-text-muted">Occupied</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded bg-amber-700/60 border border-amber-600/50"></span>
                        <span class="text-[10px] text-wolf-text-muted">Reserved</span>
                    </div>
                </div>
            </div>

            <div class="bg-wolf-bg-elevated px-5 py-3 border-t border-wolf-border flex justify-end">
                <button @click="$emit('close')" class="px-4 py-2 text-xs font-bold text-wolf-text-muted hover:text-wolf-cream bg-wolf-bg-card border border-wolf-border rounded-lg transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</template>

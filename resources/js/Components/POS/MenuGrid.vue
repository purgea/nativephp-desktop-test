<script setup>
defineProps({
    items: Array,
    categoryName: String,
});

defineEmits(['addItem']);

function itemBgClass(color) {
    const map = {
        red: 'bg-gradient-to-b from-red-800 to-red-950 hover:from-red-700 hover:to-red-900 ring-red-700/40',
        green: 'bg-gradient-to-b from-emerald-800 to-emerald-950 hover:from-emerald-700 hover:to-emerald-900 ring-emerald-700/40',
        blue: 'bg-gradient-to-b from-sky-800 to-sky-950 hover:from-sky-700 hover:to-sky-900 ring-sky-700/40',
        gold: 'bg-gradient-to-b from-amber-700 to-amber-900 hover:from-amber-600 hover:to-amber-800 ring-amber-600/40',
        burgundy: 'bg-gradient-to-b from-wolf-burgundy to-wolf-burgundy-dark hover:from-wolf-burgundy-light hover:to-wolf-burgundy ring-wolf-burgundy/50',
        orange: 'bg-gradient-to-b from-orange-700 to-orange-900 hover:from-orange-600 hover:to-orange-800 ring-orange-600/40',
        rose: 'bg-gradient-to-b from-rose-700 to-rose-900 hover:from-rose-600 hover:to-rose-800 ring-rose-600/40',
    };
    return map[color] || 'bg-gradient-to-b from-wolf-bg-elevated to-wolf-bg-card hover:from-wolf-border-light hover:to-wolf-bg-elevated ring-wolf-border/40';
}
</script>

<template>
    <div class="flex-1 flex flex-col overflow-hidden bg-wolf-bg">
        <!-- Category Header -->
        <div class="px-4 py-2 bg-wolf-bg-light border-b border-wolf-border shrink-0 flex items-center justify-between">
            <h3 class="text-[11px] font-bold text-wolf-gold uppercase tracking-[0.2em]">{{ categoryName }}</h3>
            <span class="text-[10px] text-wolf-text-muted">{{ items.length }} items</span>
        </div>

        <!-- Items Grid -->
        <div class="flex-1 overflow-y-auto p-3">
            <div class="grid grid-cols-5 gap-2 auto-rows-min">
                <button
                    v-for="(item, index) in items"
                    :key="index"
                    @click="$emit('addItem', item)"
                    class="relative rounded-lg p-2 min-h-[76px] flex flex-col items-center justify-center text-center shadow-lg transition-all duration-100 ring-1 ring-inset pos-btn-press"
                    :class="itemBgClass(item.color)"
                >
                    <span class="text-[11px] font-bold leading-tight text-white/95 drop-shadow-sm">
                        {{ item.name }}
                    </span>
                    <span class="text-[10px] mt-1.5 text-white/50 font-mono font-semibold">
                        ${{ item.price.toFixed(2) }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>

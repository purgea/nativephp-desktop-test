<script setup>
defineProps({
    orderType: String,
    qtyMode: Boolean,
});

defineEmits(['delivery', 'pickup', 'take-table', 'qty', 'error-correct', 'reorder', 'modify', 'discount']);
</script>

<template>
    <div class="border-t border-wolf-border bg-wolf-bg-card px-2 py-2 shrink-0 space-y-1.5">
        <!-- Row 1: Order Type + Core -->
        <div class="grid grid-cols-4 gap-1.5">
            <button @click="$emit('take-table')"
                class="rounded-lg px-1 py-2 text-[10px] font-bold text-wolf-cream transition-all pos-btn-press bg-emerald-900/60 border border-emerald-700/40 hover:bg-emerald-800/70"
                :class="orderType === 'Dine In' ? 'ring-1 ring-emerald-500/50' : ''"
            >
                <span class="block text-sm leading-none mb-0.5">🍽️</span>
                Dine In
            </button>
            <button @click="$emit('delivery')"
                class="rounded-lg px-1 py-2 text-[10px] font-bold text-wolf-cream transition-all pos-btn-press bg-amber-900/60 border border-amber-700/40 hover:bg-amber-800/70"
                :class="orderType === 'Delivery' ? 'ring-1 ring-amber-500/50' : ''"
            >
                <span class="block text-sm leading-none mb-0.5">🚗</span>
                Delivery
            </button>
            <button @click="$emit('pickup')"
                class="rounded-lg px-1 py-2 text-[10px] font-bold text-wolf-cream transition-all pos-btn-press bg-sky-900/60 border border-sky-700/40 hover:bg-sky-800/70"
                :class="orderType === 'Pick Up' ? 'ring-1 ring-sky-500/50' : ''"
            >
                <span class="block text-sm leading-none mb-0.5">📦</span>
                Pick Up
            </button>
            <button @click="$emit('qty')"
                class="rounded-lg px-1 py-2 text-[10px] font-bold transition-all pos-btn-press border"
                :class="qtyMode
                    ? 'bg-wolf-gold/20 border-wolf-gold text-wolf-gold ring-1 ring-wolf-gold/40'
                    : 'bg-wolf-bg-elevated border-wolf-border text-wolf-cream-dark hover:bg-wolf-border/60'
                "
            >
                <span class="block text-sm leading-none mb-0.5">#</span>
                QTY
            </button>
        </div>

        <!-- Row 2: Item Actions -->
        <div class="grid grid-cols-4 gap-1.5">
            <button @click="$emit('error-correct')"
                class="rounded-lg px-1 py-2 text-[10px] font-bold text-wolf-cream transition-all pos-btn-press bg-wolf-burgundy/40 border border-wolf-burgundy/50 hover:bg-wolf-burgundy/60"
            >
                <span class="block text-sm leading-none mb-0.5">✕</span>
                Error Corr.
            </button>
            <button @click="$emit('reorder')"
                class="rounded-lg px-1 py-2 text-[10px] font-bold text-wolf-cream transition-all pos-btn-press bg-wolf-bg-elevated border border-wolf-border hover:bg-wolf-border/60"
            >
                <span class="block text-sm leading-none mb-0.5">↺</span>
                Reorder
            </button>
            <button @click="$emit('modify')"
                class="rounded-lg px-1 py-2 text-[10px] font-bold text-wolf-cream transition-all pos-btn-press bg-purple-900/40 border border-purple-700/40 hover:bg-purple-900/60"
            >
                <span class="block text-sm leading-none mb-0.5">✎</span>
                Modify
            </button>
            <button @click="$emit('discount')"
                class="rounded-lg px-1 py-2 text-[10px] font-bold text-wolf-cream transition-all pos-btn-press bg-wolf-bg-elevated border border-wolf-border hover:bg-wolf-border/60"
            >
                <span class="block text-sm leading-none mb-0.5">%</span>
                Discount
            </button>
        </div>
    </div>
</template>

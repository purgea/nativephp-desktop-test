<script setup>
defineProps({
    subtotal: Number,
    tax: Number,
    gratuity: Number,
    discount: Number,
    amountDue: Number,
    orderType: String,
});

defineEmits(['settle', 'manager', 'server']);
</script>

<template>
    <div class="border-t border-wolf-border bg-wolf-bg-light shrink-0">
        <!-- Order Type + Functions -->
        <div class="px-3 py-2 bg-wolf-bg-card border-b border-wolf-border flex items-center gap-2">
            <div class="flex-1">
                <span class="text-[9px] text-wolf-text-muted uppercase tracking-[0.15em] font-semibold block">Order Type</span>
                <span class="text-sm font-bold text-wolf-gold">{{ orderType }}</span>
            </div>
            <button @click="$emit('manager')" class="rounded bg-wolf-burgundy-dark hover:bg-wolf-burgundy border border-wolf-burgundy/50 text-wolf-cream px-4 py-3 text-sm font-bold transition-colors pos-btn-press min-h-[52px]">
                MGR
            </button>
            <button @click="$emit('server')" class="rounded bg-wolf-bg-elevated hover:bg-wolf-border border border-wolf-border text-wolf-cream px-4 py-3 text-sm font-bold transition-colors pos-btn-press min-h-[52px]">
                SERVER
            </button>
        </div>

        <!-- Totals -->
        <div class="px-3 py-2 grid grid-cols-2 gap-x-4 gap-y-0.5 text-[11px]">
            <div class="flex justify-between text-wolf-text-muted">
                <span>Sub:</span>
                <span class="font-mono text-wolf-cream-dark">${{ subtotal.toFixed(2) }}</span>
            </div>
            <div class="flex justify-between text-wolf-text-muted">
                <span>Tax:</span>
                <span class="font-mono text-wolf-cream-dark">${{ tax.toFixed(2) }}</span>
            </div>
            <div class="flex justify-between text-wolf-text-muted">
                <span>Grat:</span>
                <span class="font-mono" :class="gratuity > 0 ? 'text-emerald-400' : 'text-wolf-cream-dark'">${{ gratuity.toFixed(2) }}</span>
            </div>
            <div class="flex justify-between text-wolf-text-muted">
                <span>Disc:</span>
                <span class="font-mono" :class="discount > 0 ? 'text-red-400' : 'text-wolf-cream-dark'">-${{ discount.toFixed(2) }}</span>
            </div>
        </div>

        <!-- Amount Due + Settle -->
        <div class="px-3 py-3 bg-wolf-bg-card border-t border-wolf-border flex items-center gap-3">
            <div class="flex-1">
                <span class="text-[9px] font-semibold text-wolf-text-muted uppercase tracking-wider">Amount Due</span>
                <div class="text-3xl font-black text-wolf-gold font-mono tracking-tight">${{ amountDue.toFixed(2) }}</div>
            </div>
            <button
                @click="$emit('settle')"
                class="bg-gradient-to-b from-wolf-gold to-wolf-gold-dark hover:from-wolf-gold-light hover:to-wolf-gold text-wolf-bg rounded-xl px-6 py-5 text-base font-black transition-all pos-btn-press shadow-lg shadow-wolf-gold/20 uppercase tracking-wider min-h-[72px]"
            >
                Settle<br/>Check
            </button>
        </div>
    </div>
</template>

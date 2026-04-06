<script setup>
defineProps({
    seats: Array,
    activeSeat: Number,
});

defineEmits(['select', 'addSeat', 'removeSeat']);
</script>

<template>
    <div class="flex items-center border-b border-wolf-border bg-wolf-bg-card/50 shrink-0">
        <button
            v-for="(seat, index) in seats"
            :key="seat.id"
            @click="$emit('select', index)"
            class="flex-1 px-3 py-3.5 text-sm font-bold uppercase tracking-wider transition-colors border-b-2 relative group min-h-[56px]"
            :class="[
                activeSeat === index
                    ? 'text-wolf-gold bg-wolf-bg-elevated/50 border-wolf-gold'
                    : 'text-wolf-text-muted border-transparent hover:text-wolf-cream-dark hover:bg-wolf-bg-elevated/30',
            ]"
        >
            {{ seat.name }}
            <span v-if="seat.items.length" class="ml-1 text-[9px] bg-wolf-bg-elevated text-wolf-text-secondary px-1 py-0.5 rounded border border-wolf-border">
                {{ seat.items.length }}
            </span>
            <!-- Remove seat button (on hover, non-active empty seats) -->
            <button
                v-if="seat.items.length === 0 && seats.length > 1"
                @click.stop="$emit('removeSeat', index)"
                class="absolute -top-0.5 -right-0.5 w-3.5 h-3.5 rounded-full bg-wolf-burgundy text-white text-[8px] leading-none flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
            >×</button>
        </button>
        <button
            @click="$emit('addSeat')"
            class="px-3 py-1.5 text-wolf-text-muted hover:text-wolf-gold transition-colors border-b-2 border-transparent"
            title="Add seat"
        >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
        </button>
    </div>
</template>

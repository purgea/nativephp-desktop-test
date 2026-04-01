<script setup>
const props = defineProps({
    orders: Array,
});

const emit = defineEmits(['close', 'recall', 'delete']);
</script>

<template>
    <div class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4" @click.self="$emit('close')">
        <div class="bg-wolf-bg-card border border-wolf-border rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="bg-wolf-bg-elevated px-5 py-3 border-b border-wolf-border flex items-center justify-between">
                <h3 class="text-wolf-cream font-bold text-base">Held Orders</h3>
                <button @click="$emit('close')" class="text-wolf-text-muted hover:text-wolf-cream text-xl leading-none">&times;</button>
            </div>

            <div class="max-h-[400px] overflow-y-auto">
                <div v-if="orders.length === 0" class="p-8 text-center">
                    <div class="text-wolf-text-muted text-3xl mb-2">📋</div>
                    <div class="text-wolf-text-muted text-sm">No held orders</div>
                </div>

                <div v-else class="divide-y divide-wolf-border">
                    <div
                        v-for="order in orders"
                        :key="order.id"
                        class="p-4 hover:bg-wolf-bg-elevated/50 transition-colors"
                    >
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <div class="text-wolf-cream font-bold text-sm">
                                    Order #{{ order.id }}
                                    <span v-if="order.table" class="text-wolf-gold ml-1">Table {{ order.table }}</span>
                                </div>
                                <div class="text-wolf-text-muted text-[10px]">{{ order.type }} • {{ order.time }}</div>
                            </div>
                            <div class="text-wolf-gold font-bold font-mono text-sm">${{ order.total.toFixed(2) }}</div>
                        </div>

                        <!-- Items preview -->
                        <div class="text-[10px] text-wolf-text-secondary mb-2">
                            <span v-for="(seat, i) in order.seats" :key="i">
                                <span v-for="(item, j) in seat.items.slice(0, 3)" :key="j">
                                    {{ item.qty }}× {{ item.name }}<span v-if="j < seat.items.length - 1 || i < order.seats.length - 1">, </span>
                                </span>
                                <span v-if="seat.items.length > 3" class="text-wolf-text-muted">+{{ seat.items.length - 3 }} more</span>
                            </span>
                        </div>

                        <div class="flex gap-2">
                            <button
                                @click="$emit('recall', order)"
                                class="flex-1 px-3 py-1.5 text-[10px] font-bold bg-wolf-gold/15 text-wolf-gold border border-wolf-gold/30 rounded-lg hover:bg-wolf-gold/25 transition-colors pos-btn-press"
                            >
                                Recall Order
                            </button>
                            <button
                                @click="$emit('delete', order.id)"
                                class="px-3 py-1.5 text-[10px] font-bold text-wolf-burgundy-light border border-wolf-burgundy/30 rounded-lg hover:bg-wolf-burgundy/10 transition-colors pos-btn-press"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-wolf-bg-elevated px-5 py-3 border-t border-wolf-border flex justify-end">
                <button @click="$emit('close')" class="px-4 py-2 text-xs font-bold text-wolf-text-muted hover:text-wolf-cream bg-wolf-bg-card border border-wolf-border rounded-lg transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</template>

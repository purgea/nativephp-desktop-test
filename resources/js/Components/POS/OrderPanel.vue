<script setup>
import { ref, watch, nextTick } from 'vue';

const props = defineProps({
    items: Array,
    selectedIndex: Number,
    seats: Array,
    activeSeat: Number,
});

const emit = defineEmits(['select', 'moveItem', 'change-qty']);

const scrollContainer = ref(null);
const showMoveMenu = ref(null);

watch(() => props.items.length, async () => {
    await nextTick();
    if (scrollContainer.value) {
        scrollContainer.value.scrollTop = scrollContainer.value.scrollHeight;
    }
});

function toggleMoveMenu(index) {
    showMoveMenu.value = showMoveMenu.value === index ? null : index;
}
</script>

<template>
    <div
        ref="scrollContainer"
        class="flex-1 overflow-y-auto min-h-0"
    >
        <div v-if="items.length === 0" class="flex flex-col items-center justify-center h-full text-wolf-text-muted">
            <svg class="w-10 h-10 mb-2 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <span class="text-xs">Tap items to add</span>
        </div>

        <div v-else class="divide-y divide-wolf-border/50">
            <div
                v-for="(item, index) in items"
                :key="item.id"
                @click="$emit('select', index)"
                class="flex items-center gap-2.5 px-3 py-2 cursor-pointer transition-colors duration-75 relative"
                :class="[
                    selectedIndex === index
                        ? 'bg-wolf-gold/10 border-l-[3px] border-wolf-gold'
                        : 'hover:bg-wolf-bg-elevated/60 border-l-[3px] border-transparent',
                ]"
            >
                <!-- Qty Badge (tap to edit qty on selected item) -->
                <button
                    @click.stop="selectedIndex === index ? $emit('change-qty', index) : $emit('select', index)"
                    class="w-7 h-7 rounded flex items-center justify-center text-[10px] font-bold shrink-0 transition-all"
                    :class="[
                        item.sent
                            ? (item.fired ? 'bg-red-900/40 text-red-400' : 'bg-emerald-900/40 text-emerald-400')
                            : 'bg-wolf-bg-elevated text-wolf-text-muted border border-wolf-border',
                        selectedIndex === index ? 'ring-1 ring-wolf-gold/60 hover:bg-wolf-gold/20' : ''
                    ]"
                    :title="selectedIndex === index ? 'Tap to change quantity' : ''"
                >
                    {{ item.qty }}
                </button>

                <!-- Item Details -->
                <div class="flex-1 min-w-0">
                    <div class="text-[12px] font-medium text-wolf-cream truncate">{{ item.name }}</div>
                    <div v-if="item.modifiers?.length" class="flex flex-wrap gap-1 mt-0.5">
                        <span
                            v-for="mod in item.modifiers"
                            :key="mod.name"
                            class="text-[8px] bg-wolf-gold/10 text-wolf-gold border border-wolf-gold/20 px-1.5 py-0 rounded font-medium"
                        >
                            {{ mod.name }}
                        </span>
                    </div>
                    <div v-if="item.sent" class="flex items-center gap-1.5 mt-0.5">
                        <span class="text-[8px] font-semibold uppercase tracking-wider"
                            :class="item.fired ? 'text-red-400' : 'text-emerald-500'">
                            {{ item.fired ? 'FIRED' : 'SENT' }}
                        </span>
                    </div>
                </div>

                <!-- Price -->
                <span class="text-[12px] font-mono font-semibold text-wolf-cream-dark shrink-0">
                    ${{ (item.price * item.qty).toFixed(2) }}
                </span>

                <!-- Move to seat button -->
                <button
                    v-if="seats.length > 1 && selectedIndex === index"
                    @click.stop="toggleMoveMenu(index)"
                    class="text-wolf-text-muted hover:text-wolf-gold text-[10px] shrink-0 px-1"
                    title="Move to another seat"
                >
                    ↗
                </button>

                <!-- Move Menu Dropdown -->
                <div v-if="showMoveMenu === index && seats.length > 1"
                    class="absolute right-2 top-full z-20 bg-wolf-bg-card border border-wolf-border rounded-lg shadow-xl py-1 min-w-[120px]"
                >
                    <div class="text-[9px] text-wolf-text-muted px-3 py-1 uppercase tracking-wider font-semibold">Move to:</div>
                    <button
                        v-for="(seat, si) in seats"
                        :key="si"
                        v-show="si !== activeSeat"
                        @click.stop="$emit('moveItem', index, si); showMoveMenu = null"
                        class="w-full text-left px-3 py-1.5 text-[11px] text-wolf-cream hover:bg-wolf-bg-elevated transition-colors"
                    >
                        {{ seat.name }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

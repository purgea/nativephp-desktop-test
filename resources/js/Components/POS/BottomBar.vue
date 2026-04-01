<script setup>
defineProps({
    heldCount: Number,
});

defineEmits(['fire-order', 'type-in', 'open-food', 'open-bar', 'print-check', 'send-order', 'hold-order', 'recall', 'sign-off', 'kitchen-mod']);
</script>

<template>
    <div class="border-t border-wolf-border bg-wolf-bg-light px-2 py-1.5 flex items-center gap-1.5 shrink-0">
        <!-- Function Buttons -->
        <div class="flex gap-1 flex-1 overflow-x-auto">
            <button @click="$emit('kitchen-mod')"
                class="rounded px-2.5 py-2 text-[9px] font-bold text-wolf-cream whitespace-nowrap transition-all pos-btn-press bg-teal-900/50 border border-teal-700/40 hover:bg-teal-800/60"
            >
                Kitchen Mod
            </button>
            <button @click="$emit('fire-order')"
                class="rounded px-2.5 py-2 text-[9px] font-bold text-wolf-cream whitespace-nowrap transition-all pos-btn-press bg-red-900/50 border border-red-700/40 hover:bg-red-800/60"
            >
                🔥 Fire
            </button>
            <button @click="$emit('type-in')"
                class="rounded px-2.5 py-2 text-[9px] font-bold text-wolf-gold whitespace-nowrap transition-all pos-btn-press bg-wolf-gold/10 border border-wolf-gold/30 hover:bg-wolf-gold/20"
            >
                Type In
            </button>
            <button @click="$emit('open-food')"
                class="rounded px-2.5 py-2 text-[9px] font-bold text-wolf-cream whitespace-nowrap transition-all pos-btn-press bg-emerald-900/50 border border-emerald-700/40 hover:bg-emerald-800/60"
            >
                Open Food
            </button>
            <button @click="$emit('open-bar')"
                class="rounded px-2.5 py-2 text-[9px] font-bold text-wolf-cream whitespace-nowrap transition-all pos-btn-press bg-emerald-900/50 border border-emerald-700/40 hover:bg-emerald-800/60"
            >
                Open Bar
            </button>
            <button @click="$emit('recall')"
                class="rounded px-2.5 py-2 text-[9px] font-bold whitespace-nowrap transition-all pos-btn-press border relative"
                :class="heldCount > 0 ? 'bg-wolf-gold/15 border-wolf-gold/40 text-wolf-gold' : 'bg-wolf-bg-elevated border-wolf-border text-wolf-cream-dark hover:bg-wolf-border/60'"
            >
                Recall
                <span v-if="heldCount > 0" class="absolute -top-1 -right-1 w-4 h-4 bg-wolf-gold text-wolf-bg rounded-full text-[8px] flex items-center justify-center font-black">
                    {{ heldCount }}
                </span>
            </button>
        </div>

        <!-- Divider -->
        <div class="w-px h-8 bg-wolf-border mx-0.5"></div>

        <!-- Main Actions -->
        <div class="flex gap-1.5 shrink-0">
            <button @click="$emit('print-check')"
                class="bg-wolf-bg-elevated hover:bg-wolf-border text-wolf-cream border border-wolf-border rounded-lg px-3 py-2 text-[10px] font-bold transition-all pos-btn-press"
            >
                🖨️ Print
            </button>
            <button @click="$emit('send-order')"
                class="bg-gradient-to-b from-emerald-700 to-emerald-900 hover:from-emerald-600 hover:to-emerald-800 text-white rounded-lg px-4 py-2 text-[10px] font-bold transition-all pos-btn-press shadow-lg ring-1 ring-inset ring-white/10"
            >
                📤 Send
            </button>
            <button @click="$emit('hold-order')"
                class="bg-wolf-bg-elevated hover:bg-wolf-border text-wolf-cream-dark border border-wolf-border rounded-lg px-3 py-2 text-[10px] font-bold transition-all pos-btn-press"
            >
                ⏸ Hold
            </button>
            <button @click="$emit('sign-off')"
                class="bg-gradient-to-b from-wolf-gold-dark to-amber-900 hover:from-wolf-gold hover:to-wolf-gold-dark text-wolf-bg rounded-lg px-3 py-2 text-[10px] font-bold transition-all pos-btn-press shadow ring-1 ring-inset ring-white/10"
            >
                🔐 Off
            </button>
        </div>
    </div>
</template>

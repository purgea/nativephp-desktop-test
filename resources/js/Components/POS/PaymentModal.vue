<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    amountDue: Number,
    subtotal: Number,
    tax: Number,
});

const emit = defineEmits(['close', 'pay']);

const tipPercent = ref(0);
const customTip = ref('');
const cashTendered = ref('');
const selectedMethod = ref(null);

// ─── Gift Card State ────────────────────────────────────────
const giftCardInput = ref('');
const giftCardLookupError = ref('');
const giftCardLookedUp = ref(null); // matched card object
const giftCardSearching = ref(false);

const giftCards = [
    { serial: 'WG-1001-2024', pin: '4821', holder: 'James R.', balance: 250.00, issued: '2025-12-01', status: 'active' },
    { serial: 'WG-1002-2024', pin: '7735', holder: 'Maria S.', balance: 100.00, issued: '2025-12-15', status: 'active' },
    { serial: 'WG-1003-2024', pin: '1199', holder: 'Robert K.', balance: 50.00, issued: '2026-01-10', status: 'active' },
    { serial: 'WG-1004-2024', pin: '3344', holder: 'Elizabeth W.', balance: 500.00, issued: '2025-11-20', status: 'active' },
    { serial: 'WG-1005-2024', pin: '5500', holder: 'Thomas L.', balance: 0.00, issued: '2025-10-05', status: 'depleted' },
    { serial: 'WG-1006-2024', pin: '2288', holder: 'Sophia M.', balance: 75.50, issued: '2026-02-14', status: 'active' },
    { serial: 'WG-1007-2024', pin: '9900', holder: 'William D.', balance: 150.00, issued: '2026-03-01', status: 'active' },
    { serial: 'WG-1008-2024', pin: '6611', holder: 'Grace P.', balance: 25.00, issued: '2025-09-18', status: 'expired' },
];

function lookupGiftCard() {
    const query = giftCardInput.value.trim().toUpperCase();
    giftCardLookupError.value = '';
    giftCardLookedUp.value = null;

    if (!query) {
        giftCardLookupError.value = 'Please enter a serial number';
        return;
    }

    giftCardSearching.value = true;
    // Simulate a brief lookup delay
    setTimeout(() => {
        const card = giftCards.find(c => c.serial.toUpperCase() === query);
        giftCardSearching.value = false;

        if (!card) {
            giftCardLookupError.value = 'Gift card not found. Check the serial and try again.';
            return;
        }
        if (card.status === 'expired') {
            giftCardLookupError.value = `Card ${card.serial} is expired (issued ${card.issued}).`;
            return;
        }
        if (card.status === 'depleted' || card.balance <= 0) {
            giftCardLookupError.value = `Card ${card.serial} has a $0.00 balance.`;
            return;
        }

        giftCardLookedUp.value = card;
    }, 400);
}

function clearGiftCard() {
    giftCardInput.value = '';
    giftCardLookedUp.value = null;
    giftCardLookupError.value = '';
}

const giftCardCoversTotal = computed(() => {
    if (!giftCardLookedUp.value) return false;
    return giftCardLookedUp.value.balance >= finalTotal.value;
});

const giftCardRemaining = computed(() => {
    if (!giftCardLookedUp.value) return 0;
    return Math.max(0, finalTotal.value - giftCardLookedUp.value.balance);
});

// ─── Tip & Totals ───────────────────────────────────────────
const tipAmount = computed(() => {
    if (customTip.value) return parseFloat(customTip.value) || 0;
    return Math.round(props.subtotal * (tipPercent.value / 100) * 100) / 100;
});

const finalTotal = computed(() => props.amountDue + tipAmount.value);

const changeDue = computed(() => {
    if (selectedMethod.value !== 'cash') return 0;
    const tendered = parseFloat(cashTendered.value) || 0;
    return Math.max(0, tendered - finalTotal.value);
});

function selectTip(pct) {
    tipPercent.value = pct;
    customTip.value = '';
}

function selectMethod(key) {
    selectedMethod.value = key;
    // Reset gift card state when switching away
    if (key !== 'gift') {
        clearGiftCard();
    }
}

const canPay = computed(() => {
    if (!selectedMethod.value) return false;
    if (selectedMethod.value === 'gift') return !!giftCardLookedUp.value;
    return true;
});

function pay() {
    if (selectedMethod.value === 'gift' && giftCardLookedUp.value) {
        emit('pay', selectedMethod.value, tipAmount.value, {
            serial: giftCardLookedUp.value.serial,
            holder: giftCardLookedUp.value.holder,
            charged: Math.min(giftCardLookedUp.value.balance, finalTotal.value),
            remainingBalance: Math.max(0, giftCardLookedUp.value.balance - finalTotal.value),
            shortfall: giftCardRemaining.value,
        });
    } else {
        emit('pay', selectedMethod.value, tipAmount.value);
    }
}

const methods = [
    { key: 'cash', label: 'Cash', icon: '💵' },
    { key: 'credit', label: 'Credit Card', icon: '💳' },
    { key: 'debit', label: 'Debit Card', icon: '💳' },
    { key: 'gift', label: 'Gift Card', icon: '🎁' },
];

const tipOptions = [0, 15, 18, 20, 22, 25];

const quickCash = computed(() => {
    const total = finalTotal.value;
    const values = [];
    values.push(Math.ceil(total));
    values.push(Math.ceil(total / 5) * 5);
    values.push(Math.ceil(total / 10) * 10);
    values.push(Math.ceil(total / 20) * 20);
    values.push(Math.ceil(total / 50) * 50);
    values.push(100);
    return [...new Set(values)].filter(v => v >= total).slice(0, 6);
});
</script>

<template>
    <div class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4" @click.self="$emit('close')">
        <div class="bg-wolf-bg-card border border-wolf-border rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[92vh]">
            <!-- Header -->
            <div class="bg-wolf-bg-elevated px-6 py-5 border-b border-wolf-border flex items-center justify-between shrink-0">
                <h3 class="text-wolf-cream font-bold text-xl">Settle Check</h3>
                <button @click="$emit('close')" class="text-wolf-text-muted hover:text-wolf-cream text-3xl leading-none w-12 h-12 flex items-center justify-center">&times;</button>
            </div>

            <div class="p-5 space-y-5 overflow-y-auto flex-1">
                <!-- Amount -->
                <div class="bg-wolf-bg rounded-xl border border-wolf-border p-6 text-center">
                    <div class="text-wolf-text-muted text-sm uppercase tracking-wider mb-2">Amount Due</div>
                    <div class="text-6xl font-black text-wolf-gold font-mono">${{ amountDue.toFixed(2) }}</div>
                </div>

                <!-- Payment Method -->
                <div>
                    <div class="text-wolf-text-muted text-xs uppercase tracking-wider font-semibold mb-3">Payment Method</div>
                    <div class="grid grid-cols-4 gap-3">
                        <button
                            v-for="m in methods"
                            :key="m.key"
                            @click="selectMethod(m.key)"
                            class="rounded-xl py-5 text-center border transition-all pos-btn-press"
                            :class="selectedMethod === m.key
                                ? 'bg-wolf-gold/15 border-wolf-gold text-wolf-gold'
                                : 'bg-wolf-bg-elevated border-wolf-border text-wolf-cream-dark hover:border-wolf-gold/50'
                            "
                        >
                            <span class="block text-3xl mb-1">{{ m.icon }}</span>
                            <span class="text-xs font-bold">{{ m.label }}</span>
                        </button>
                    </div>
                </div>

                <!-- Tip -->
                <div>
                    <div class="text-wolf-text-muted text-xs uppercase tracking-wider font-semibold mb-3">Gratuity</div>
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            v-for="pct in tipOptions"
                            :key="pct"
                            @click="selectTip(pct)"
                            class="rounded-xl py-4 text-sm font-bold border transition-all pos-btn-press"
                            :class="tipPercent === pct && !customTip
                                ? 'bg-wolf-gold/15 border-wolf-gold text-wolf-gold'
                                : 'bg-wolf-bg-elevated border-wolf-border text-wolf-cream-dark hover:border-wolf-gold/50'
                            "
                        >
                            {{ pct }}%
                        </button>
                    </div>
                    <div class="flex items-center gap-3 mt-3">
                        <span class="text-wolf-text-muted text-sm">Custom:</span>
                        <div class="flex-1 relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-wolf-gold text-sm">$</span>
                            <input
                                v-model="customTip"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                class="w-full bg-wolf-bg border border-wolf-border rounded-xl pl-8 pr-3 py-3.5 text-base text-wolf-cream font-mono focus:border-wolf-gold focus:ring-1 focus:ring-wolf-gold/30 outline-none"
                                @input="tipPercent = 0"
                            />
                        </div>
                        <div class="text-wolf-gold text-base font-bold font-mono min-w-[72px] text-right">${{ tipAmount.toFixed(2) }}</div>
                    </div>
                </div>

                <!-- Gift Card Lookup -->
                <div v-if="selectedMethod === 'gift'">
                    <div class="text-wolf-text-muted text-[10px] uppercase tracking-wider font-semibold mb-2">Gift Card Serial / QR Code</div>

                    <!-- Lookup input -->
                    <div v-if="!giftCardLookedUp" class="space-y-3">
                        <div class="flex gap-3">
                            <input
                                v-model="giftCardInput"
                                type="text"
                                placeholder="e.g. WG-1001-2024"
                                class="flex-1 bg-wolf-bg border border-wolf-border rounded-xl px-4 py-3.5 text-base text-wolf-cream font-mono uppercase tracking-wider placeholder:text-wolf-text-muted/40 focus:border-wolf-gold focus:ring-1 focus:ring-wolf-gold/30 outline-none"
                                @keydown.enter="lookupGiftCard"
                            />
                            <button
                                @click="lookupGiftCard"
                                :disabled="giftCardSearching"
                                class="px-6 py-3.5 rounded-xl text-sm font-bold transition-all pos-btn-press bg-wolf-gold/15 border border-wolf-gold/40 text-wolf-gold hover:bg-wolf-gold/25 disabled:opacity-50"
                            >
                                {{ giftCardSearching ? '...' : 'Lookup' }}
                            </button>
                        </div>

                        <!-- Error -->
                        <div v-if="giftCardLookupError" class="bg-red-900/20 border border-red-700/30 rounded-lg px-3 py-2 text-red-300 text-xs">
                            {{ giftCardLookupError }}
                        </div>

                        <!-- Hint -->
                        <div class="text-wolf-text-muted/50 text-[9px] italic">
                            Scan QR code or type serial number (e.g. WG-1001-2024 through WG-1008-2024)
                        </div>
                    </div>

                    <!-- Matched Card Info -->
                    <div v-else class="space-y-2">
                        <div class="bg-wolf-bg rounded-lg border border-wolf-gold/30 p-3">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">🎁</span>
                                    <div>
                                        <div class="text-wolf-cream text-sm font-bold font-mono">{{ giftCardLookedUp.serial }}</div>
                                        <div class="text-wolf-text-muted text-[10px]">Holder: {{ giftCardLookedUp.holder }}</div>
                                    </div>
                                </div>
                                <button @click="clearGiftCard" class="text-wolf-text-muted hover:text-red-400 text-xs px-2 py-1 rounded hover:bg-red-900/20 transition-colors">
                                    ✕ Clear
                                </button>
                            </div>
                            <div class="flex items-center justify-between pt-2 border-t border-wolf-border">
                                <span class="text-wolf-text-muted text-xs">Available Balance</span>
                                <span class="text-wolf-gold text-lg font-black font-mono">${{ giftCardLookedUp.balance.toFixed(2) }}</span>
                            </div>
                            <div class="text-wolf-text-muted/50 text-[9px] mt-1">Issued: {{ giftCardLookedUp.issued }}</div>
                        </div>

                        <!-- Balance vs Total -->
                        <div v-if="giftCardCoversTotal" class="bg-emerald-900/20 border border-emerald-700/30 rounded-lg px-3 py-2 text-center">
                            <span class="text-emerald-300 text-xs font-semibold">✓ Card covers full amount</span>
                            <div class="text-emerald-200/60 text-[10px] mt-0.5">
                                Remaining card balance after payment: <span class="font-mono font-bold">${{ (giftCardLookedUp.balance - finalTotal).toFixed(2) }}</span>
                            </div>
                        </div>
                        <div v-else class="bg-amber-900/20 border border-amber-700/30 rounded-lg px-3 py-2 text-center">
                            <span class="text-amber-300 text-xs font-semibold">⚠ Partial coverage</span>
                            <div class="text-amber-200/60 text-[10px] mt-0.5">
                                Card will be fully used. Remaining balance of <span class="font-mono font-bold">${{ giftCardRemaining.toFixed(2) }}</span> needs another payment method.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cash Tendered -->
                <div v-if="selectedMethod === 'cash'">
                    <div class="text-wolf-text-muted text-xs uppercase tracking-wider font-semibold mb-3">Cash Tendered</div>
                    <input
                        v-model="cashTendered"
                        type="number"
                        step="0.01"
                        :placeholder="finalTotal.toFixed(2)"
                        class="w-full bg-wolf-bg border border-wolf-border rounded-xl px-4 py-4 text-2xl text-wolf-cream font-mono text-center focus:border-wolf-gold focus:ring-1 focus:ring-wolf-gold/30 outline-none mb-3"
                    />
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            v-for="val in quickCash"
                            :key="val"
                            @click="cashTendered = val.toString()"
                            class="bg-wolf-bg-elevated border border-wolf-border rounded-xl py-4 text-sm font-bold text-wolf-cream-dark hover:border-wolf-gold/50 transition-colors pos-btn-press"
                        >
                            ${{ val }}
                        </button>
                    </div>
                    <div v-if="changeDue > 0" class="mt-2 bg-emerald-900/30 border border-emerald-700/40 rounded-lg p-2 text-center">
                        <span class="text-emerald-300 text-xs font-semibold">Change Due: </span>
                        <span class="text-emerald-200 text-lg font-bold font-mono">${{ changeDue.toFixed(2) }}</span>
                    </div>
                </div>

                <!-- Total Line -->
                <div class="bg-wolf-bg rounded-xl border border-wolf-gold/30 p-5 flex items-center justify-between">
                    <span class="text-wolf-cream font-semibold text-base">Final Total</span>
                    <span class="text-wolf-gold text-3xl font-black font-mono">${{ finalTotal.toFixed(2) }}</span>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-wolf-bg-elevated px-6 py-5 border-t border-wolf-border flex justify-end gap-3 shrink-0">
                <button @click="$emit('close')" class="px-6 py-4 text-sm font-bold text-wolf-text-muted hover:text-wolf-cream bg-wolf-bg-card border border-wolf-border rounded-xl transition-colors min-w-[120px]">
                    Cancel
                </button>
                <button
                    @click="pay"
                    :disabled="!canPay"
                    class="px-10 py-4 text-base font-bold rounded-xl transition-colors pos-btn-press min-w-[220px]"
                    :class="canPay
                        ? 'text-wolf-bg bg-wolf-gold hover:bg-wolf-gold-light'
                        : 'text-wolf-text-muted bg-wolf-bg-elevated border border-wolf-border cursor-not-allowed'
                    "
                >
                    {{ selectedMethod === 'gift' && giftCardLookedUp ? `Charge $${Math.min(giftCardLookedUp.balance, finalTotal).toFixed(2)} to Gift Card` : 'Process Payment' }}
                </button>
            </div>
        </div>
    </div>
</template>

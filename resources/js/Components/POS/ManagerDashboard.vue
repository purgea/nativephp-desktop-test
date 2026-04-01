<script setup>
import { ref } from 'vue';

const props = defineProps({
    heldOrders:          { type: Array,  default: () => [] },
    currentSeatItems:    { type: Array,  default: () => [] },
    selectedOrderItem:   { default: null },
    subtotal:            { type: Number, default: 0 },
    employees:           { type: Array,  default: () => [] },
    sessionChecks:       { type: Number, default: 0 },
    sessionTotal:        { type: Number, default: 0 },
    currentEmployee:     { default: null },
});

const emit = defineEmits([
    'go-to-terminal',
    'sign-off',
    'void-sale',
    'apply-discount',
    'void-item',
    'comp-item',
    'open-tables',
    'open-discounts',
    'recall-order',
    'delete-held',
    'open-drawer',
    'reprint',
    'notify',
]);

const activePanel = ref(null);

// ─── Button grid matching the reference image ─────────────────
// color keys: fuchsia | red | lime | amber | dark | green | pink | blue | teal | exit
const buttonRows = [
    [
        { label: 'Void\nSale',                  action: 'void-sale',       color: 'fuchsia' },
        { label: 'Recall Closed\nCheck by #',   action: 'noop',            color: 'red' },
        { label: 'Recall Recent\nClosed Check', action: 'noop',            color: 'red' },
        { label: 'See Open\nTables',            action: 'open-tables',     color: 'lime' },
        { label: 'See Open Tables\nby List',    action: 'open-tables',     color: 'lime' },
    ],
    [
        { label: 'Gift Card\nDiscount',         action: 'noop',            color: 'red' },
        null,
        { label: 'Paid\nOut',                   action: 'noop',            color: 'lime' },
        { label: 'Refund',                      action: 'noop',            color: 'lime' },
        { label: 'Deposit',                     action: 'noop',            color: 'lime' },
    ],
    [
        { label: '100%\nOwner',                 action: 'comp-100',        color: 'red' },
        { label: '100%\nManager',               action: 'comp-100',        color: 'red' },
        { label: 'Discounts',                   action: 'discounts',       color: 'red' },
        { label: '100%\nComp',                  action: 'comp-100',        color: 'red' },
        { label: '100% Comp\nSelected Items',   action: 'comp-item',       color: 'red' },
    ],
    [
        { label: 'Void Item',                   action: 'void-item',       color: 'red' },
        { label: '15% Comp\nWhole Check',       action: 'comp-15',         color: 'red' },
        { label: '25% Comp\nWhole Check',       action: 'comp-25',         color: 'red' },
        { label: '50% Comp\nWhole Check',       action: 'comp-50',         color: 'red' },
        { label: 'Unapply All\nDiscounts',      action: 'unapply',         color: 'red' },
    ],
    [
        { label: 'Today Sales\nReport',         action: 'sales-report',    color: 'dark' },
        { label: 'Yesterday Sales\nReport',     action: 'noop',            color: 'dark' },
        { label: 'Sales by\nRange Report',      action: 'noop',            color: 'dark' },
        { label: 'Yesterday Tips\nReport',      action: 'noop',            color: 'dark' },
        { label: 'Open Checks\nReport',         action: 'open-checks',     color: 'dark' },
    ],
    [
        { label: 'Employees\nClocked In',       action: 'clocked-in',      color: 'green' },
        { label: 'Edit\nTimecard',              action: 'noop',            color: 'green' },
        { label: 'View\nSchedules',             action: 'noop',            color: 'green' },
        { label: 'Fingerprint\nEmployee',       action: 'noop',            color: 'pink' },
        { label: 'Assign\nBadge',               action: 'noop',            color: 'pink' },
    ],
    [
        { label: 'Item\nAvailability',          action: 'noop',            color: 'blue' },
        { label: 'Item\nCount',                 action: 'noop',            color: 'blue' },
        { label: 'Top/Bottom\nSellers',         action: 'noop',            color: 'blue' },
        { label: 'Todays\nSales',               action: 'sales-report',    color: 'blue' },
        { label: 'Employee\nTimecard Report',   action: 'noop',            color: 'blue' },
    ],
    [
        { label: '86 Item',                     action: 'noop',            color: 'blue' },
        { label: 'Un 86 Item',                  action: 'noop',            color: 'blue' },
        { label: 'Adjust Manually\nAvailable',  action: 'noop',            color: 'blue' },
        { label: 'Change Menu\nPrice',          action: 'noop',            color: 'blue' },
        { label: 'Override Price',              action: 'noop',            color: 'blue' },
    ],
    [
        { label: 'Open Cash\nDrawer',           action: 'open-drawer',     color: 'teal' },
        { label: 'Toggle Offline\nCredit Card', action: 'noop',            color: 'teal' },
        { label: 'Offline Credit\nCard Report', action: 'noop',            color: 'teal' },
        { label: 'Process Offline\nCredit Card',action: 'noop',            color: 'teal' },
        { label: 'Clocked IN\nEmployees',       action: 'clocked-in',      color: 'blue' },
    ],
    [
        { label: 'EXIT',                        action: 'exit',            color: 'exit' },
        { label: 'Training\nMode',              action: 'noop',            color: 'dark' },
        { label: 'Reprint\nCheck',              action: 'reprint',         color: 'dark' },
        { label: 'Restart\nFPOS',               action: 'noop',            color: 'dark' },
        { label: 'Clocked IN\nEmployees',       action: 'clocked-in',      color: 'blue' },
    ],
];

const colorClass = {
    fuchsia: 'bg-fuchsia-700 hover:bg-fuchsia-600 border-fuchsia-600 text-white',
    red:     'bg-red-800     hover:bg-red-700     border-red-700     text-white',
    lime:    'bg-lime-600    hover:bg-lime-500    border-lime-500    text-white',
    amber:   'bg-amber-600   hover:bg-amber-500   border-amber-500   text-white',
    dark:    'bg-zinc-700    hover:bg-zinc-600    border-zinc-600    text-zinc-200',
    green:   'bg-green-700   hover:bg-green-600   border-green-600   text-white',
    pink:    'bg-pink-700    hover:bg-pink-600    border-pink-600    text-white',
    blue:    'bg-blue-700    hover:bg-blue-600    border-blue-600    text-white',
    teal:    'bg-teal-700    hover:bg-teal-600    border-teal-600    text-white',
    exit:    'bg-red-600     hover:bg-red-500     border-red-500     text-white font-black text-base',
};

const panelActions = new Set(['open-checks', 'clocked-in', 'sales-report']);

function handle(action) {
    if (panelActions.has(action)) {
        activePanel.value = activePanel.value === action ? null : action;
        return;
    }
    switch (action) {
        case 'void-sale':
            emit('void-sale');
            break;
        case 'open-tables':
            emit('go-to-terminal');
            emit('open-tables');
            break;
        case 'discounts':
            emit('go-to-terminal');
            emit('open-discounts');
            break;
        case 'void-item':
            if (props.selectedOrderItem !== null) {
                emit('go-to-terminal');
                emit('void-item', props.selectedOrderItem);
            } else {
                emit('notify', 'Go to Terminal, select an item, then use this function', 'error');
            }
            break;
        case 'comp-item':
            if (props.selectedOrderItem !== null) {
                emit('go-to-terminal');
                emit('comp-item', props.selectedOrderItem);
            } else {
                emit('notify', 'Go to Terminal, select an item, then use this function', 'error');
            }
            break;
        case 'comp-15':  emit('go-to-terminal'); emit('apply-discount', 'percent', 15);  break;
        case 'comp-25':  emit('go-to-terminal'); emit('apply-discount', 'percent', 25);  break;
        case 'comp-50':  emit('go-to-terminal'); emit('apply-discount', 'percent', 50);  break;
        case 'comp-100': emit('go-to-terminal'); emit('apply-discount', 'percent', 100); break;
        case 'unapply':  emit('go-to-terminal'); emit('apply-discount', 'fixed', 0);     break;
        case 'open-drawer': emit('open-drawer'); break;
        case 'reprint':     emit('go-to-terminal'); emit('reprint'); break;
        case 'exit':        emit('go-to-terminal'); break;
        default:
            emit('notify', 'Feature not yet available', 'info');
    }
}
</script>

<template>
    <div class="h-screen w-screen bg-wolf-bg flex flex-col overflow-hidden select-none font-sans">

        <!-- Header -->
        <div class="h-11 bg-zinc-900 border-b border-zinc-700 flex items-center justify-between px-4 shrink-0">
            <div class="flex items-center gap-3">
                <span class="text-xs font-black tracking-widest uppercase text-red-400">&#9632; MANAGER</span>
                <span class="text-wolf-gold font-bold text-sm tracking-wider">Wolfgang's POS</span>
                <span v-if="currentEmployee" class="text-zinc-400 text-xs">— {{ currentEmployee.name }}</span>
            </div>
            <div class="flex items-center gap-2">
                <button
                    @click="$emit('go-to-terminal')"
                    class="text-zinc-300 hover:text-white px-3 py-1 rounded border border-zinc-600 hover:border-zinc-400 text-xs transition-colors pos-btn-press bg-zinc-800 hover:bg-zinc-700"
                >
                    ▶ Open Terminal
                </button>
                <button
                    @click="$emit('sign-off')"
                    class="text-red-400 hover:text-red-300 px-3 py-1 rounded border border-red-900 hover:border-red-700 text-xs transition-colors pos-btn-press bg-red-950/40 hover:bg-red-900/40"
                >
                    ⏻ Sign Off
                </button>
            </div>
        </div>

        <!-- Body -->
        <div class="flex-1 flex overflow-hidden">

            <!-- Button Grid -->
            <div class="flex-1 overflow-y-auto p-2.5">
                <div class="grid gap-1.5" style="grid-template-columns: repeat(5, 1fr)">
                    <template v-for="(row, ri) in buttonRows" :key="ri">
                        <template v-for="(btn, bi) in row" :key="bi">
                            <!-- Empty cell -->
                            <div v-if="!btn" />
                            <!-- Button -->
                            <button
                                v-else
                                @click="handle(btn.action)"
                                :class="[
                                    'rounded border text-center text-[11px] font-semibold leading-tight px-1 py-1.5 transition-all h-14 flex items-center justify-center pos-btn-press',
                                    colorClass[btn.color],
                                    panelActions.has(btn.action) && activePanel === btn.action
                                        ? 'ring-2 ring-white/40 brightness-125'
                                        : '',
                                ]"
                            >
                                <span style="white-space: pre-line">{{ btn.label }}</span>
                            </button>
                        </template>
                    </template>
                </div>
            </div>

            <!-- Slide-in Side Panel -->
            <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="translate-x-4 opacity-0"
                enter-to-class="translate-x-0 opacity-100"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="translate-x-0 opacity-100"
                leave-to-class="translate-x-4 opacity-0"
            >
                <div v-if="activePanel" class="w-72 border-l border-zinc-700 bg-zinc-900 flex flex-col shrink-0">

                    <!-- ── Open Checks ── -->
                    <template v-if="activePanel === 'open-checks'">
                        <div class="px-4 py-3 border-b border-zinc-700 flex items-center justify-between">
                            <span class="text-white font-bold text-sm">Open Checks</span>
                            <button @click="activePanel = null" class="text-zinc-500 hover:text-white text-xs transition-colors">✕</button>
                        </div>
                        <div class="flex-1 overflow-y-auto p-2">
                            <div v-if="heldOrders.length === 0" class="text-zinc-500 text-sm text-center py-10">
                                No open checks
                            </div>
                            <div
                                v-for="order in heldOrders"
                                :key="order.id"
                                class="bg-zinc-800 rounded border border-zinc-700 p-3 mb-2"
                            >
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-white font-bold text-sm">#{{ order.id }}</span>
                                    <span class="text-wolf-gold font-mono text-sm">${{ order.total.toFixed(2) }}</span>
                                </div>
                                <div class="text-zinc-400 text-xs mb-2">
                                    {{ order.type }}{{ order.table ? ` · Table ${order.table}` : '' }} · {{ order.time }}
                                </div>
                                <div class="flex gap-1.5">
                                    <button
                                        @click="$emit('recall-order', order); $emit('close')"
                                        class="flex-1 bg-blue-700 hover:bg-blue-600 text-white text-xs rounded py-1.5 font-semibold transition-colors"
                                    >Recall</button>
                                    <button
                                        @click="$emit('delete-held', order.id)"
                                        class="bg-red-900 hover:bg-red-800 text-red-300 text-xs rounded px-2.5 py-1.5 transition-colors"
                                    >Delete</button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- ── Clocked In ── -->
                    <template v-else-if="activePanel === 'clocked-in'">
                        <div class="px-4 py-3 border-b border-zinc-700 flex items-center justify-between">
                            <span class="text-white font-bold text-sm">Employees Clocked In</span>
                            <button @click="activePanel = null" class="text-zinc-500 hover:text-white text-xs transition-colors">✕</button>
                        </div>
                        <div class="flex-1 overflow-y-auto p-2">
                            <div
                                v-for="emp in employees"
                                :key="emp.id"
                                class="bg-zinc-800 rounded border border-zinc-700 p-3 mb-2 flex items-center gap-3"
                            >
                                <div class="w-8 h-8 rounded-full bg-emerald-800 border border-emerald-600 flex items-center justify-center text-emerald-300 font-bold text-sm shrink-0">
                                    {{ emp.name.charAt(0) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="text-white text-sm font-semibold truncate">{{ emp.name }}</div>
                                    <div class="text-zinc-400 text-xs">{{ emp.role }} · <span class="font-mono">#{{ emp.id }}</span></div>
                                </div>
                                <span class="ml-auto text-[9px] bg-emerald-900/60 text-emerald-400 border border-emerald-700/40 px-1.5 py-0.5 rounded font-bold shrink-0">ACTIVE</span>
                            </div>
                        </div>
                    </template>

                    <!-- ── Sales Report ── -->
                    <template v-else-if="activePanel === 'sales-report'">
                        <div class="px-4 py-3 border-b border-zinc-700 flex items-center justify-between">
                            <span class="text-white font-bold text-sm">Today's Sales</span>
                            <button @click="activePanel = null" class="text-zinc-500 hover:text-white text-xs transition-colors">✕</button>
                        </div>
                        <div class="flex-1 p-3 flex flex-col gap-2 overflow-y-auto">
                            <div class="bg-zinc-800 rounded border border-zinc-700 p-4">
                                <div class="text-zinc-500 text-[10px] uppercase tracking-widest mb-1">Checks Closed</div>
                                <div class="text-white font-bold text-3xl font-mono">{{ sessionChecks }}</div>
                            </div>
                            <div class="bg-zinc-800 rounded border border-zinc-700 p-4">
                                <div class="text-zinc-500 text-[10px] uppercase tracking-widest mb-1">Session Sales</div>
                                <div class="text-wolf-gold font-bold text-3xl font-mono">${{ sessionTotal.toFixed(2) }}</div>
                            </div>
                            <div class="bg-zinc-800 rounded border border-zinc-700 p-4">
                                <div class="text-zinc-500 text-[10px] uppercase tracking-widest mb-1">Current Check</div>
                                <div class="text-white font-bold text-3xl font-mono">${{ subtotal.toFixed(2) }}</div>
                            </div>
                            <div class="bg-zinc-800 rounded border border-zinc-700 p-4">
                                <div class="text-zinc-500 text-[10px] uppercase tracking-widest mb-1">Open Checks</div>
                                <div class="text-white font-bold text-3xl font-mono">{{ heldOrders.length }}</div>
                            </div>
                        </div>
                    </template>

                </div>
            </Transition>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    heldOrders:        { type: Array,  default: () => [] },
    currentSeatItems:  { type: Array,  default: () => [] },
    seats:             { type: Array,  default: () => [] },
    selectedOrderItem: { default: null },
    subtotal:          { type: Number, default: 0 },
    tax:               { type: Number, default: 0 },
    gratuity:          { type: Number, default: 0 },
    discount:          { type: Number, default: 0 },
    amountDue:         { type: Number, default: 0 },
    orderType:         { type: String, default: 'Dine In' },
    tableNumber:       { default: null },
    checkNumber:       { type: Number, default: 0 },
    serverName:        { type: String, default: '' },
    employees:         { type: Array,  default: () => [] },
    categories:        { type: Array,  default: () => [] },
    sessionChecks:     { type: Number, default: 0 },
    sessionTotal:      { type: Number, default: 0 },
    currentEmployee:   { default: null },
});

const emit = defineEmits([
    'go-to-terminal',
    'sign-off',
    'void-sale',
    'apply-discount',
    'void-item',
    'comp-item',
    'recall-order',
    'delete-held',
    'open-drawer',
    'notify',
    'update-price',
    'add-employee',
    'update-employee',
    'delete-employee',
]);

// ─── Local notification ──────────────────────────────────────
const localNotif = ref(null);
let notifTimer = null;

function showNotif(msg, type = 'success') {
    localNotif.value = { msg, type };
    clearTimeout(notifTimer);
    notifTimer = setTimeout(() => { localNotif.value = null; }, 3000);
}

// ─── Active panel ────────────────────────────────────────────
const activePanel = ref(null);

function openPanel(name) {
    activePanel.value = activePanel.value === name ? null : name;
}

// ─── Mock tables (demo) ──────────────────────────────────────
const tableStatuses = ['available', 'seated', 'ordered', 'check'];
const tableStatusColors = {
    available: 'bg-zinc-700 border-zinc-600 text-zinc-300',
    seated:    'bg-sky-900 border-sky-700 text-sky-200',
    ordered:   'bg-emerald-900 border-emerald-700 text-emerald-200',
    check:     'bg-amber-900 border-amber-700 text-amber-200',
};
const tableStatusLabel = {
    available: 'OPEN',
    seated:    'SEATED',
    ordered:   'ORDERED',
    check:     'CHECK',
};
const mockTables = ref(
    Array.from({ length: 35 }, (_, i) => {
        const servers = ['Sarah', 'Emma', 'Jake', 'Marcus'];
        const st = tableStatuses[Math.floor(Math.random() * 4)];
        return {
            number: i + 1,
            status: st,
            covers: st === 'available' ? 0 : Math.floor(Math.random() * 6) + 1,
            server: st === 'available' ? '' : servers[Math.floor(Math.random() * 4)],
            total:  st === 'available' ? 0 : parseFloat((Math.random() * 450 + 30).toFixed(2)),
        };
    })
);

// ─── Discount panel ──────────────────────────────────────────
const discountMode = ref('percent');
const discountInput = ref('');

const discountPreview = computed(() => {
    const v = parseFloat(discountInput.value) || 0;
    if (!v) return '';
    return discountMode.value === 'percent'
        ? `$${(props.subtotal * v / 100).toFixed(2)} off`
        : `$${v.toFixed(2)} off`;
});

function applyDiscount() {
    const v = parseFloat(discountInput.value) || 0;
    if (v <= 0) { showNotif('Enter a discount value', 'error'); return; }
    emit('apply-discount', discountMode.value, v);
    showNotif(`${discountMode.value === 'percent' ? v + '%' : '$' + v.toFixed(2)} discount applied`);
    discountInput.value = '';
    activePanel.value = null;
}

function quickDiscount(pct) {
    emit('apply-discount', 'percent', pct);
    showNotif(`${pct}% comp applied to check`);
    activePanel.value = null;
}

function removeAllDiscounts() {
    emit('apply-discount', 'fixed', 0);
    showNotif('All discounts removed');
    activePanel.value = null;
}

// ─── Void sale confirmation ──────────────────────────────────
function confirmVoidSale() {
    emit('void-sale');
    showNotif('Sale voided — order cleared');
    activePanel.value = null;
}

// ─── Void / Comp item ─────────────────────────────────────────
function voidItem(idx) {
    emit('void-item', idx);
    showNotif('Item voided');
    activePanel.value = null;
}

function compItem(idx) {
    emit('comp-item', idx);
    showNotif('Item comped');
    activePanel.value = null;
}

// ─── Paid-out / Refund (demo) ────────────────────────────────
const paidOutAmount = ref('');
const paidOutReason = ref('');
const refundAmount = ref('');

function submitPaidOut() {
    const amount = parseFloat(paidOutAmount.value) || 0;
    if (!amount) { showNotif('Enter an amount', 'error'); return; }
    showNotif(`Paid out $${amount.toFixed(2)}${paidOutReason.value ? ' — ' + paidOutReason.value : ''}`);
    emit('open-drawer');
    paidOutAmount.value = '';
    paidOutReason.value = '';
    activePanel.value = null;
}

function submitRefund() {
    const amount = parseFloat(refundAmount.value) || 0;
    if (!amount) { showNotif('Enter a refund amount', 'error'); return; }
    showNotif(`Refund of $${amount.toFixed(2)} processed`);
    emit('open-drawer');
    refundAmount.value = '';
    activePanel.value = null;
}

// ─── Change Price ─────────────────────────────────────────────
const selectedCatIdx = ref(0);
const editingItemKey = ref(null);
const editingPrice = ref('');

function startEditPrice(catIdx, itemIdx, currentPrice) {
    editingItemKey.value = `${catIdx}-${itemIdx}`;
    editingPrice.value = currentPrice.toFixed(2);
}

function savePrice(catIdx, itemIdx) {
    const price = parseFloat(editingPrice.value);
    if (!isNaN(price) && price >= 0) {
        emit('update-price', catIdx, itemIdx, price);
        showNotif('Price updated');
    }
    editingItemKey.value = null;
    editingPrice.value = '';
}

function cancelEditPrice() {
    editingItemKey.value = null;
    editingPrice.value = '';
}

// ─── Employee Management ──────────────────────────────────────
const empFormMode = ref(null);
const empForm = ref({ id: '', name: '', role: 'Server' });
const empEditId = ref(null);
const empRoles = ['Manager', 'Host', 'Server', 'Bartender', 'Busser', 'Runner'];

function startAddEmployee() {
    empForm.value = { id: '', name: '', role: 'Server' };
    empEditId.value = null;
    empFormMode.value = 'add';
}

function startEditEmployee(emp) {
    empForm.value = { id: emp.id, name: emp.name, role: emp.role };
    empEditId.value = emp.id;
    empFormMode.value = 'edit';
}

function cancelEmpForm() {
    empFormMode.value = null;
    empEditId.value = null;
}

function submitEmpForm() {
    if (!empForm.value.name.trim() || !empForm.value.id.trim()) {
        showNotif('Name and Employee # are required', 'error');
        return;
    }
    if (empFormMode.value === 'add') {
        emit('add-employee', { id: empForm.value.id.trim(), name: empForm.value.name.trim(), role: empForm.value.role });
        showNotif(`${empForm.value.name.trim()} added`);
    } else {
        emit('update-employee', empEditId.value, { name: empForm.value.name.trim(), role: empForm.value.role });
        showNotif('Employee updated');
    }
    empFormMode.value = null;
    empEditId.value = null;
}

// ─── Inventory mock ──────────────────────────────────────────
const inventoryItems = ref(
    (props.categories || []).flatMap((cat, ci) =>
        (cat.items || []).map((item, ii) => ({
            catName: cat.name, name: item.name, catIdx: ci, itemIdx: ii,
            status: ['in_stock', 'in_stock', 'low', '86'][Math.floor(Math.random() * 4)],
        }))
    )
);
const inventoryStatusColors = {
    in_stock: 'text-emerald-400 bg-emerald-900/30 border-emerald-700/40',
    low:      'text-amber-400 bg-amber-900/30 border-amber-700/40',
    '86':     'text-red-400 bg-red-900/30 border-red-700/40',
};
const inventoryStatusLabel = { in_stock: 'In Stock', low: 'Low', '86': '86\'d' };

function toggleInventory(item) {
    const cycle = ['in_stock', 'low', '86'];
    item.status = cycle[(cycle.indexOf(item.status) + 1) % cycle.length];
    showNotif(`${item.name}: ${inventoryStatusLabel[item.status]}`);
}

// ─── Timecard mock ───────────────────────────────────────────
const timecards = computed(() =>
    props.employees.map(emp => ({
        ...emp,
        clockIn: `${8 + Math.floor(Math.random() * 4)}:${['00','15','30','45'][Math.floor(Math.random()*4)]} AM`,
        hours: parseFloat((4 + Math.random() * 4).toFixed(1)),
        tips: parseFloat((20 + Math.random() * 80).toFixed(2)),
    }))
);

// ─── Receipt date/time ───────────────────────────────────────
const now = new Date();
const receiptDate = now.toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' });
const receiptTime = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });

// ─── Button grid ─────────────────────────────────────────────
const buttonRows = [
    [
        { label: 'Void\nSale',                 action: 'void-sale-confirm', color: 'fuchsia' },
        { label: 'Recall\nOpen Check',         action: 'open-checks',       color: 'red' },
        { label: 'Open\nTables',               action: 'tables',            color: 'lime' },
        { label: 'Open Cash\nDrawer',          action: 'open-drawer',       color: 'teal' },
        { label: 'Reprint\nCheck',             action: 'reprint-panel',     color: 'dark' },
    ],
    [
        { label: 'Discounts',                  action: 'discounts-panel',   color: 'red' },
        { label: '15%\nComp',                  action: 'comp-15',           color: 'red' },
        { label: '25%\nComp',                  action: 'comp-25',           color: 'red' },
        { label: '50%\nComp',                  action: 'comp-50',           color: 'red' },
        { label: '100%\nComp',                 action: 'comp-100',          color: 'red' },
    ],
    [
        { label: 'Remove All\nDiscounts',      action: 'unapply',           color: 'red' },
        { label: 'Void\nItem',                 action: 'void-item-panel',   color: 'red' },
        { label: 'Comp\nItem',                 action: 'comp-item-panel',   color: 'red' },
        { label: 'Paid\nOut',                  action: 'paidout-panel',     color: 'lime' },
        { label: 'Refund',                     action: 'refund-panel',      color: 'lime' },
    ],
    [
        { label: 'Sales\nReport',              action: 'sales-report',      color: 'dark' },
        { label: 'Open Checks\nReport',        action: 'open-checks',       color: 'dark' },
        { label: 'Item\nAvailability',         action: 'inventory-panel',   color: 'blue' },
        { label: 'Change Menu\nPrice',         action: 'change-price',      color: 'blue' },
        { label: 'Override\nPrice',            action: 'change-price',      color: 'blue' },
    ],
    [
        { label: 'Employees\nClocked In',      action: 'clocked-in',        color: 'green' },
        { label: 'Manage\nEmployees',          action: 'employee-mgmt',     color: 'pink' },
        { label: 'Edit\nTimecard',             action: 'timecard-panel',    color: 'green' },
        { label: 'Top / Bottom\nSellers',      action: 'sales-report',      color: 'dark' },
        { label: 'EXIT to\nTerminal',          action: 'exit',              color: 'exit' },
    ],
];

const colorClass = {
    fuchsia: 'bg-fuchsia-700 hover:bg-fuchsia-600 border-fuchsia-600',
    red:     'bg-red-800     hover:bg-red-700     border-red-700',
    lime:    'bg-lime-600    hover:bg-lime-500    border-lime-500',
    amber:   'bg-amber-600   hover:bg-amber-500   border-amber-500',
    dark:    'bg-zinc-700    hover:bg-zinc-600    border-zinc-600    text-zinc-200',
    green:   'bg-green-700   hover:bg-green-600   border-green-600',
    pink:    'bg-pink-700    hover:bg-pink-600    border-pink-600',
    blue:    'bg-blue-700    hover:bg-blue-600    border-blue-600',
    teal:    'bg-teal-700    hover:bg-teal-600    border-teal-600',
    exit:    'bg-red-600     hover:bg-red-500     border-red-500     font-black',
};

const panelActions = new Set([
    'open-checks', 'clocked-in', 'sales-report', 'change-price', 'employee-mgmt',
    'tables', 'discounts-panel', 'void-item-panel', 'comp-item-panel',
    'void-sale-confirm', 'reprint-panel', 'paidout-panel', 'refund-panel',
    'inventory-panel', 'timecard-panel',
]);

function handle(action) {
    if (panelActions.has(action)) {
        activePanel.value = activePanel.value === action ? null : action;
        return;
    }
    switch (action) {
        case 'comp-15':  quickDiscount(15);  break;
        case 'comp-25':  quickDiscount(25);  break;
        case 'comp-50':  quickDiscount(50);  break;
        case 'comp-100': quickDiscount(100); break;
        case 'unapply':  removeAllDiscounts(); break;
        case 'open-drawer':
            emit('open-drawer');
            showNotif('Cash drawer opened');
            break;
        case 'exit':
            emit('go-to-terminal');
            break;
        default:
            showNotif('Feature available in panel', 'info');
    }
}
</script>

<template>
    <div class="h-screen w-screen bg-wolf-bg flex flex-col overflow-hidden select-none font-sans">

        <!-- Header -->
        <div class="h-16 bg-zinc-900 border-b border-zinc-700 flex items-center justify-between px-5 shrink-0">
            <div class="flex items-center gap-4">
                <span class="text-sm font-black tracking-widest uppercase text-red-400">&#9632; MANAGER</span>
                <span class="text-wolf-gold font-bold text-base tracking-wider">Wolfgang's POS</span>
                <span v-if="currentEmployee" class="text-zinc-400 text-sm">— {{ currentEmployee.name }}</span>
            </div>
            <div class="flex items-center gap-3">
                <Transition enter-active-class="transition duration-150 ease-out" enter-from-class="opacity-0 -translate-y-1" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-100 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
                    <span v-if="localNotif" class="px-4 py-1.5 rounded-full text-sm font-semibold border"
                        :class="{
                            'bg-emerald-900/80 text-emerald-200 border-emerald-700': localNotif.type === 'success',
                            'bg-red-900/80 text-red-200 border-red-700': localNotif.type === 'error',
                            'bg-zinc-800 text-zinc-200 border-zinc-600': localNotif.type === 'info',
                        }"
                    >{{ localNotif.msg }}</span>
                </Transition>
                <button @click="$emit('go-to-terminal')" class="text-zinc-300 hover:text-white px-6 py-3 rounded-lg border border-zinc-600 hover:border-zinc-400 text-sm font-bold transition-colors pos-btn-press bg-zinc-800 hover:bg-zinc-700 min-h-[52px]">
                    ▶ Terminal
                </button>
                <button @click="$emit('sign-off')" class="text-red-400 hover:text-red-300 px-6 py-3 rounded-lg border border-red-900 hover:border-red-700 text-sm font-bold transition-colors pos-btn-press bg-red-950/40 hover:bg-red-900/40 min-h-[52px]">
                    ⏻ Sign Off
                </button>
            </div>
        </div>

        <!-- Body -->
        <div class="flex-1 flex overflow-hidden">

            <!-- Button Grid -->
            <div class="flex-1 overflow-y-auto p-3">
                <div class="grid gap-2" style="grid-template-columns: repeat(5, 1fr)">
                    <template v-for="(row, ri) in buttonRows" :key="ri">
                        <template v-for="(btn, bi) in row" :key="bi">
                            <div v-if="!btn" />
                            <button v-else @click="handle(btn.action)"
                                :class="[
                                    'rounded-lg border text-center text-sm font-bold leading-snug px-2 py-3 transition-all h-24 flex items-center justify-center pos-btn-press text-white',
                                    colorClass[btn.color],
                                    panelActions.has(btn.action) && activePanel === btn.action ? 'ring-2 ring-white/50 brightness-125' : '',
                                ]"
                            ><span style="white-space: pre-line">{{ btn.label }}</span></button>
                        </template>
                    </template>
                </div>
            </div>

            <!-- Slide-in Side Panel -->
            <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="translate-x-6 opacity-0" enter-to-class="translate-x-0 opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="translate-x-0 opacity-100" leave-to-class="translate-x-6 opacity-0">
                <div v-if="activePanel" class="w-[460px] border-l border-zinc-700 bg-zinc-900 flex flex-col shrink-0">

                    <!-- ══ VOID SALE CONFIRM ══ -->
                    <template v-if="activePanel === 'void-sale-confirm'">
                        <div class="px-5 py-4 border-b border-zinc-700 flex items-center justify-between shrink-0">
                            <span class="text-white font-bold text-base">Void Sale</span>
                            <button @click="activePanel = null" class="text-zinc-500 hover:text-white text-xl w-10 h-10 flex items-center justify-center">✕</button>
                        </div>
                        <div class="flex-1 flex flex-col items-center justify-center p-8 gap-6">
                            <div class="text-5xl">⚠️</div>
                            <div class="text-center">
                                <div class="text-white text-xl font-bold mb-2">Void Current Order?</div>
                                <div class="text-zinc-400 text-sm">This will clear all items from the current check.</div>
                                <div v-if="subtotal > 0" class="mt-3 bg-zinc-800 rounded-xl px-6 py-3 border border-zinc-700">
                                    <span class="text-zinc-400 text-xs uppercase tracking-wider">Check Total: </span>
                                    <span class="text-wolf-gold font-mono font-bold text-xl">${{ subtotal.toFixed(2) }}</span>
                                </div>
                            </div>
                            <div class="flex gap-4 w-full">
                                <button @click="activePanel = null" class="flex-1 bg-zinc-700 hover:bg-zinc-600 text-white font-bold py-5 rounded-xl text-base transition-colors pos-btn-press">Cancel</button>
                                <button @click="confirmVoidSale" class="flex-1 bg-red-700 hover:bg-red-600 text-white font-bold py-5 rounded-xl text-base transition-colors pos-btn-press">Void Sale</button>
                            </div>
                        </div>
                    </template>

                    <!-- ══ OPEN CHECKS ══ -->
                    <template v-else-if="activePanel === 'open-checks'">
                        <div class="px-5 py-4 border-b border-zinc-700 flex items-center justify-between shrink-0">
                            <span class="text-white font-bold text-base">Open Checks ({{ heldOrders.length }})</span>
                            <button @click="activePanel = null" class="text-zinc-500 hover:text-white text-xl w-10 h-10 flex items-center justify-center">✕</button>
                        </div>
                        <div class="flex-1 overflow-y-auto p-3 space-y-2">
                            <div v-if="heldOrders.length === 0" class="text-zinc-500 text-base text-center py-16">No open checks</div>
                            <div v-for="order in heldOrders" :key="order.id" class="bg-zinc-800 rounded-xl border border-zinc-700 p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-white font-bold text-base">#{{ order.id }}</span>
                                    <span class="text-wolf-gold font-mono font-bold text-base">${{ order.total.toFixed(2) }}</span>
                                </div>
                                <div class="text-zinc-400 text-sm mb-3">{{ order.type }}{{ order.table ? ` · Table ${order.table}` : '' }} · {{ order.time }}</div>
                                <div class="flex gap-2">
                                    <button @click="$emit('recall-order', order); activePanel = null" class="flex-1 bg-blue-700 hover:bg-blue-600 text-white text-sm rounded-xl py-3 font-bold transition-colors pos-btn-press">Recall to Terminal</button>
                                    <button @click="$emit('delete-held', order.id)" class="bg-red-900 hover:bg-red-800 text-red-300 text-sm rounded-xl px-4 py-3 transition-colors pos-btn-press">Delete</button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- ══ TABLES ══ -->
                    <template v-else-if="activePanel === 'tables'">
                        <div class="px-5 py-4 border-b border-zinc-700 flex items-center justify-between shrink-0">
                            <span class="text-white font-bold text-base">Floor Map</span>
                            <button @click="activePanel = null" class="text-zinc-500 hover:text-white text-xl w-10 h-10 flex items-center justify-center">✕</button>
                        </div>
                        <div class="flex gap-2 px-4 py-2 border-b border-zinc-800 shrink-0 flex-wrap">
                            <span v-for="s in tableStatuses" :key="s" class="text-[10px] font-bold px-2 py-0.5 rounded border" :class="tableStatusColors[s]">{{ tableStatusLabel[s] }}</span>
                        </div>
                        <div class="flex-1 overflow-y-auto p-3">
                            <div class="grid grid-cols-5 gap-2">
                                <div v-for="t in mockTables" :key="t.number" class="rounded-xl border p-2 flex flex-col items-center justify-center text-center min-h-[76px]" :class="tableStatusColors[t.status]">
                                    <div class="font-black text-base">{{ t.number }}</div>
                                    <div class="text-[9px] font-bold uppercase mt-0.5">{{ tableStatusLabel[t.status] }}</div>
                                    <div v-if="t.covers" class="text-[9px] mt-0.5">{{ t.covers }} cvr</div>
                                    <div v-if="t.total" class="text-[9px] font-mono font-bold">${{ t.total.toFixed(0) }}</div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- ══ DISCOUNTS ══ -->
                    <template v-else-if="activePanel === 'discounts-panel'">
                        <div class="px-5 py-4 border-b border-zinc-700 flex items-center justify-between shrink-0">
                            <span class="text-white font-bold text-base">Apply Discount</span>
                            <button @click="activePanel = null" class="text-zinc-500 hover:text-white text-xl w-10 h-10 flex items-center justify-center">✕</button>
                        </div>
                        <div class="flex-1 overflow-y-auto p-4 space-y-5">
                            <div class="bg-zinc-800 rounded-xl border border-zinc-700 p-4">
                                <div class="text-zinc-400 text-xs uppercase tracking-widest mb-1">Current Subtotal</div>
                                <div class="text-wolf-gold font-mono font-black text-3xl">${{ subtotal.toFixed(2) }}</div>
                            </div>
                            <div>
                                <div class="text-zinc-400 text-xs uppercase tracking-widest font-bold mb-2">Quick Comp</div>
                                <div class="grid grid-cols-3 gap-2">
                                    <button v-for="pct in [10, 15, 20, 25, 50, 100]" :key="pct" @click="quickDiscount(pct)" class="bg-red-800 hover:bg-red-700 border border-red-700 text-white font-bold py-4 rounded-xl text-lg transition-colors pos-btn-press">{{ pct }}%</button>
                                </div>
                            </div>
                            <div>
                                <div class="text-zinc-400 text-xs uppercase tracking-widest font-bold mb-2">Manual Discount</div>
                                <div class="flex rounded-xl overflow-hidden border border-zinc-700 mb-3">
                                    <button @click="discountMode = 'percent'" class="flex-1 py-3.5 text-sm font-bold transition-colors" :class="discountMode === 'percent' ? 'bg-wolf-gold text-zinc-900' : 'bg-zinc-800 text-zinc-400'">Percent %</button>
                                    <button @click="discountMode = 'fixed'" class="flex-1 py-3.5 text-sm font-bold transition-colors" :class="discountMode === 'fixed' ? 'bg-wolf-gold text-zinc-900' : 'bg-zinc-800 text-zinc-400'">Fixed $</button>
                                </div>
                                <div class="flex gap-2">
                                    <input v-model="discountInput" type="number" step="0.01" min="0" placeholder="0" class="flex-1 bg-zinc-800 border border-zinc-600 rounded-xl px-4 py-3.5 text-white text-base font-mono focus:border-wolf-gold outline-none" />
                                    <button @click="applyDiscount" class="bg-wolf-gold hover:brightness-110 text-zinc-900 font-bold px-5 py-3.5 rounded-xl text-sm transition-colors pos-btn-press">Apply</button>
                                </div>
                                <div v-if="discountPreview" class="mt-2 text-center text-wolf-gold text-sm font-semibold">→ {{ discountPreview }}</div>
                            </div>
                            <button @click="removeAllDiscounts" class="w-full bg-zinc-700 hover:bg-zinc-600 border border-zinc-600 text-zinc-300 font-bold py-4 rounded-xl text-sm transition-colors pos-btn-press">Remove All Discounts</button>
                        </div>
                    </template>

                    <!-- ══ VOID ITEM ══ -->
                    <template v-else-if="activePanel === 'void-item-panel'">
                        <div class="px-5 py-4 border-b border-zinc-700 flex items-center justify-between shrink-0">
                            <span class="text-white font-bold text-base">Void Item</span>
                            <button @click="activePanel = null" class="text-zinc-500 hover:text-white text-xl w-10 h-10 flex items-center justify-center">✕</button>
                        </div>
                        <div class="flex-1 overflow-y-auto p-3 space-y-2">
                            <div v-if="currentSeatItems.length === 0" class="text-zinc-500 text-base text-center py-16">No items on current check</div>
                            <div v-for="(item, idx) in currentSeatItems" :key="item.id" class="bg-zinc-800 rounded-xl border border-zinc-700 p-4 flex items-center gap-3">
                                <div class="w-11 h-11 rounded-lg bg-zinc-700 border border-zinc-600 flex items-center justify-center font-bold text-lg text-zinc-300 shrink-0">{{ item.qty }}</div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-white font-semibold text-base truncate">{{ item.name }}</div>
                                    <div class="text-zinc-400 text-xs mt-0.5">${{ (item.price * item.qty).toFixed(2) }}</div>
                                </div>
                                <button @click="voidItem(idx)" class="bg-red-800 hover:bg-red-700 text-white text-sm font-bold px-5 py-3 rounded-xl transition-colors pos-btn-press shrink-0">Void</button>
                            </div>
                        </div>
                    </template>

                    <!-- ══ COMP ITEM ══ -->
                    <template v-else-if="activePanel === 'comp-item-panel'">
                        <div class="px-5 py-4 border-b border-zinc-700 flex items-center justify-between shrink-0">
                            <span class="text-white font-bold text-base">Comp Item</span>
                            <button @click="activePanel = null" class="text-zinc-500 hover:text-white text-xl w-10 h-10 flex items-center justify-center">✕</button>
                        </div>
                        <div class="flex-1 overflow-y-auto p-3 space-y-2">
                            <div v-if="currentSeatItems.length === 0" class="text-zinc-500 text-base text-center py-16">No items on current check</div>
                            <div v-for="(item, idx) in currentSeatItems" :key="item.id" class="bg-zinc-800 rounded-xl border border-zinc-700 p-4 flex items-center gap-3">
                                <div class="flex-1 min-w-0">
                                    <div class="text-white font-semibold text-base truncate">{{ item.qty }}× {{ item.name }}</div>
                                    <div class="text-zinc-400 text-sm mt-0.5">${{ (item.price * item.qty).toFixed(2) }}</div>
                                </div>
                                <button v-if="item.price > 0" @click="compItem(idx)" class="bg-fuchsia-800 hover:bg-fuchsia-700 text-white text-sm font-bold px-5 py-3 rounded-xl transition-colors pos-btn-press">Comp</button>
                                <span v-else class="text-fuchsia-400 font-bold text-xs px-3">COMPED</span>
                            </div>
                        </div>
                    </template>

                    <!-- ══ PAID OUT ══ -->
                    <template v-else-if="activePanel === 'paidout-panel'">
                        <div class="px-5 py-4 border-b border-zinc-700 flex items-center justify-between shrink-0">
                            <span class="text-white font-bold text-base">Paid Out</span>
                            <button @click="activePanel = null" class="text-zinc-500 hover:text-white text-xl w-10 h-10 flex items-center justify-center">✕</button>
                        </div>
                        <div class="flex-1 flex flex-col p-5 gap-5">
                            <div class="text-zinc-400 text-sm">Record a cash payment out of the register.</div>
                            <div class="space-y-4">
                                <div>
                                    <label class="text-zinc-400 text-xs uppercase tracking-wider font-bold block mb-2">Amount ($)</label>
                                    <input v-model="paidOutAmount" type="number" step="0.01" min="0" placeholder="0.00" class="w-full bg-zinc-800 border border-zinc-600 rounded-xl px-4 py-4 text-white text-xl font-mono focus:border-wolf-gold outline-none" />
                                </div>
                                <div>
                                    <label class="text-zinc-400 text-xs uppercase tracking-wider font-bold block mb-2">Reason</label>
                                    <input v-model="paidOutReason" type="text" placeholder="e.g. Delivery supplies" class="w-full bg-zinc-800 border border-zinc-600 rounded-xl px-4 py-4 text-white text-base focus:border-wolf-gold outline-none" />
                                </div>
                            </div>
                            <button @click="submitPaidOut" class="w-full bg-lime-700 hover:bg-lime-600 text-white font-bold py-5 rounded-xl text-base transition-colors pos-btn-press mt-auto">Confirm Paid Out</button>
                        </div>
                    </template>

                    <!-- ══ REFUND ══ -->
                    <template v-else-if="activePanel === 'refund-panel'">
                        <div class="px-5 py-4 border-b border-zinc-700 flex items-center justify-between shrink-0">
                            <span class="text-white font-bold text-base">Refund</span>
                            <button @click="activePanel = null" class="text-zinc-500 hover:text-white text-xl w-10 h-10 flex items-center justify-center">✕</button>
                        </div>
                        <div class="flex-1 flex flex-col p-5 gap-5">
                            <div class="text-zinc-400 text-sm">Issue a cash refund to the guest. Opens cash drawer.</div>
                            <div>
                                <label class="text-zinc-400 text-xs uppercase tracking-wider font-bold block mb-2">Refund Amount ($)</label>
                                <input v-model="refundAmount" type="number" step="0.01" min="0" placeholder="0.00" class="w-full bg-zinc-800 border border-zinc-600 rounded-xl px-4 py-4 text-white text-xl font-mono focus:border-wolf-gold outline-none" />
                            </div>
                            <button @click="submitRefund" class="w-full bg-lime-700 hover:bg-lime-600 text-white font-bold py-5 rounded-xl text-base transition-colors pos-btn-press mt-auto">Process Refund</button>
                        </div>
                    </template>

                    <!-- ══ CLOCKED IN ══ -->
                    <template v-else-if="activePanel === 'clocked-in'">
                        <div class="px-5 py-4 border-b border-zinc-700 flex items-center justify-between shrink-0">
                            <span class="text-white font-bold text-base">Clocked In ({{ employees.length }})</span>
                            <button @click="activePanel = null" class="text-zinc-500 hover:text-white text-xl w-10 h-10 flex items-center justify-center">✕</button>
                        </div>
                        <div class="flex-1 overflow-y-auto p-3 space-y-2">
                            <div v-for="emp in employees" :key="emp.id" class="bg-zinc-800 rounded-xl border border-zinc-700 p-4 flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-emerald-800 border border-emerald-600 flex items-center justify-center text-emerald-300 font-black text-xl shrink-0">{{ emp.name.charAt(0) }}</div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-white text-base font-bold truncate">{{ emp.name }}</div>
                                    <div class="text-zinc-400 text-sm">{{ emp.role }} · <span class="font-mono">#{{ emp.id }}</span></div>
                                </div>
                                <span class="text-xs bg-emerald-900/60 text-emerald-400 border border-emerald-700/40 px-2.5 py-1 rounded-lg font-bold shrink-0">ACTIVE</span>
                            </div>
                        </div>
                    </template>

                    <!-- ══ SALES REPORT ══ -->
                    <template v-else-if="activePanel === 'sales-report'">
                        <div class="px-5 py-4 border-b border-zinc-700 flex items-center justify-between shrink-0">
                            <span class="text-white font-bold text-base">Today's Report</span>
                            <button @click="activePanel = null" class="text-zinc-500 hover:text-white text-xl w-10 h-10 flex items-center justify-center">✕</button>
                        </div>
                        <div class="flex-1 p-4 flex flex-col gap-3 overflow-y-auto">
                            <div class="bg-zinc-800 rounded-xl border border-zinc-700 p-5">
                                <div class="text-zinc-500 text-xs uppercase tracking-widest mb-2">Checks Closed (Session)</div>
                                <div class="text-white font-bold text-5xl font-mono">{{ sessionChecks }}</div>
                            </div>
                            <div class="bg-zinc-800 rounded-xl border border-zinc-700 p-5">
                                <div class="text-zinc-500 text-xs uppercase tracking-widest mb-2">Session Sales</div>
                                <div class="text-wolf-gold font-bold text-5xl font-mono">${{ sessionTotal.toFixed(2) }}</div>
                            </div>
                            <div class="bg-zinc-800 rounded-xl border border-zinc-700 p-5">
                                <div class="text-zinc-500 text-xs uppercase tracking-widest mb-2">Current Open Check</div>
                                <div class="text-white font-bold text-4xl font-mono">${{ subtotal.toFixed(2) }}</div>
                            </div>
                            <div class="bg-zinc-800 rounded-xl border border-zinc-700 p-5">
                                <div class="text-zinc-500 text-xs uppercase tracking-widest mb-2">Held Orders</div>
                                <div class="text-white font-bold text-4xl font-mono">{{ heldOrders.length }}</div>
                            </div>
                            <div class="bg-zinc-800 rounded-xl border border-zinc-700 p-5">
                                <div class="text-zinc-500 text-xs uppercase tracking-widest mb-2">Staff On Floor</div>
                                <div class="text-white font-bold text-4xl font-mono">{{ employees.length }}</div>
                            </div>
                        </div>
                    </template>

                    <!-- ══ CHANGE PRICE ══ -->
                    <template v-else-if="activePanel === 'change-price'">
                        <div class="px-5 py-4 border-b border-zinc-700 flex items-center justify-between shrink-0">
                            <span class="text-white font-bold text-base">Menu Prices</span>
                            <button @click="activePanel = null" class="text-zinc-500 hover:text-white text-xl w-10 h-10 flex items-center justify-center">✕</button>
                        </div>
                        <div class="flex overflow-x-auto border-b border-zinc-700 shrink-0">
                            <button v-for="(cat, ci) in categories" :key="ci" @click="selectedCatIdx = ci; editingItemKey = null"
                                class="shrink-0 px-4 py-3 text-xs font-bold uppercase whitespace-nowrap border-b-2 transition-colors"
                                :class="selectedCatIdx === ci ? 'border-wolf-gold text-wolf-gold bg-zinc-800' : 'border-transparent text-zinc-400 hover:text-zinc-200'"
                            >{{ cat.name }}</button>
                        </div>
                        <div class="flex-1 overflow-y-auto p-3 space-y-2">
                            <div v-for="(item, ii) in (categories[selectedCatIdx]?.items || [])" :key="ii" class="bg-zinc-800 rounded-xl border border-zinc-700 p-3">
                                <div v-if="editingItemKey === `${selectedCatIdx}-${ii}`" class="flex items-center gap-2">
                                    <span class="text-white text-sm font-semibold flex-1 min-w-0 truncate">{{ item.name }}</span>
                                    <span class="text-zinc-400">$</span>
                                    <input v-model="editingPrice" type="number" step="0.01" min="0" class="w-24 bg-zinc-900 border border-zinc-600 rounded-lg px-3 py-2.5 text-wolf-gold text-sm font-mono focus:border-wolf-gold outline-none" @keydown.enter="savePrice(selectedCatIdx, ii)" @keydown.escape="cancelEditPrice" />
                                    <button @click="savePrice(selectedCatIdx, ii)" class="bg-emerald-700 hover:bg-emerald-600 text-white font-bold px-3 py-2.5 rounded-lg transition-colors text-sm">✓</button>
                                    <button @click="cancelEditPrice" class="bg-zinc-700 hover:bg-zinc-600 text-zinc-300 px-3 py-2.5 rounded-lg transition-colors text-sm">✕</button>
                                </div>
                                <div v-else class="flex items-center justify-between">
                                    <span class="text-white text-sm font-semibold truncate flex-1 mr-3">{{ item.name }}</span>
                                    <span class="text-wolf-gold font-mono font-bold text-sm mr-3">${{ item.price.toFixed(2) }}</span>
                                    <button @click="startEditPrice(selectedCatIdx, ii, item.price)" class="bg-blue-800 hover:bg-blue-700 text-blue-200 text-sm font-bold px-4 py-2.5 rounded-lg transition-colors">Edit</button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- ══ EMPLOYEE MGMT ══ -->
                    <template v-else-if="activePanel === 'employee-mgmt'">
                        <div class="px-5 py-4 border-b border-zinc-700 flex items-center justify-between shrink-0">
                            <span class="text-white font-bold text-base">Employees</span>
                            <div class="flex items-center gap-3">
                                <button v-if="!empFormMode" @click="startAddEmployee" class="bg-emerald-700 hover:bg-emerald-600 text-white text-sm font-bold px-4 py-2 rounded-lg transition-colors">+ Add</button>
                                <button @click="activePanel = null; empFormMode = null" class="text-zinc-500 hover:text-white text-xl w-10 h-10 flex items-center justify-center">✕</button>
                            </div>
                        </div>
                        <div v-if="empFormMode" class="p-4 border-b border-zinc-700 bg-zinc-800/60 shrink-0 space-y-3">
                            <div class="text-zinc-300 text-sm font-bold uppercase tracking-widest">{{ empFormMode === 'add' ? 'Add Employee' : 'Edit Employee' }}</div>
                            <div>
                                <label class="text-zinc-400 text-xs block mb-1.5 font-semibold">Employee # (PIN)</label>
                                <input v-model="empForm.id" :disabled="empFormMode === 'edit'" type="text" placeholder="e.g. 42" class="w-full bg-zinc-900 border border-zinc-600 rounded-xl px-4 py-3.5 text-white text-base font-mono focus:border-wolf-gold outline-none disabled:opacity-50" />
                            </div>
                            <div>
                                <label class="text-zinc-400 text-xs block mb-1.5 font-semibold">Full Name</label>
                                <input v-model="empForm.name" type="text" placeholder="First Last" class="w-full bg-zinc-900 border border-zinc-600 rounded-xl px-4 py-3.5 text-white text-base focus:border-wolf-gold outline-none" />
                            </div>
                            <div>
                                <label class="text-zinc-400 text-xs block mb-1.5 font-semibold">Role</label>
                                <select v-model="empForm.role" class="w-full bg-zinc-900 border border-zinc-600 rounded-xl px-4 py-3.5 text-white text-base focus:border-wolf-gold outline-none">
                                    <option v-for="r in empRoles" :key="r" :value="r">{{ r }}</option>
                                </select>
                            </div>
                            <div class="flex gap-3 pt-1">
                                <button @click="submitEmpForm" class="flex-1 bg-emerald-700 hover:bg-emerald-600 text-white font-bold py-4 rounded-xl text-base transition-colors pos-btn-press">Save</button>
                                <button @click="cancelEmpForm" class="px-5 bg-zinc-700 hover:bg-zinc-600 text-zinc-300 py-4 rounded-xl text-base transition-colors">Cancel</button>
                            </div>
                        </div>
                        <div class="flex-1 overflow-y-auto p-3 space-y-2">
                            <div v-for="emp in employees" :key="emp.id" class="bg-zinc-800 rounded-xl border border-zinc-700 p-4 flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-emerald-800 border border-emerald-600 flex items-center justify-center text-emerald-300 font-black text-xl shrink-0">{{ emp.name.charAt(0) }}</div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-white text-base font-bold truncate">{{ emp.name }}</div>
                                    <div class="text-zinc-400 text-sm">{{ emp.role }} · <span class="font-mono">#{{ emp.id }}</span></div>
                                </div>
                                <button @click="startEditEmployee(emp)" class="bg-blue-800 hover:bg-blue-700 text-blue-200 text-sm font-bold px-3 py-2.5 rounded-lg transition-colors">Edit</button>
                                <button @click="$emit('delete-employee', emp.id)" class="bg-red-900 hover:bg-red-800 text-red-300 text-sm px-3 py-2.5 rounded-lg transition-colors">✕</button>
                            </div>
                            <div v-if="employees.length === 0" class="text-zinc-500 text-base text-center py-16">No employees</div>
                        </div>
                    </template>

                    <!-- ══ INVENTORY ══ -->
                    <template v-else-if="activePanel === 'inventory-panel'">
                        <div class="px-5 py-4 border-b border-zinc-700 flex items-center justify-between shrink-0">
                            <span class="text-white font-bold text-base">Item Availability</span>
                            <button @click="activePanel = null" class="text-zinc-500 hover:text-white text-xl w-10 h-10 flex items-center justify-center">✕</button>
                        </div>
                        <div class="flex-1 overflow-y-auto p-3 space-y-2">
                            <div v-for="item in inventoryItems" :key="`${item.catIdx}-${item.itemIdx}`" class="flex items-center gap-3 bg-zinc-800 rounded-xl border border-zinc-700 px-4 py-3.5">
                                <div class="flex-1 min-w-0">
                                    <div class="text-white text-sm font-semibold truncate">{{ item.name }}</div>
                                    <div class="text-zinc-500 text-xs">{{ item.catName }}</div>
                                </div>
                                <button @click="toggleInventory(item)" class="text-xs font-bold px-3.5 py-2 rounded-lg border transition-colors pos-btn-press" :class="inventoryStatusColors[item.status]">{{ inventoryStatusLabel[item.status] }}</button>
                            </div>
                        </div>
                    </template>

                    <!-- ══ TIMECARD ══ -->
                    <template v-else-if="activePanel === 'timecard-panel'">
                        <div class="px-5 py-4 border-b border-zinc-700 flex items-center justify-between shrink-0">
                            <span class="text-white font-bold text-base">Timecards — Today</span>
                            <button @click="activePanel = null" class="text-zinc-500 hover:text-white text-xl w-10 h-10 flex items-center justify-center">✕</button>
                        </div>
                        <div class="flex-1 overflow-y-auto p-3 space-y-2">
                            <div v-for="tc in timecards" :key="tc.id" class="bg-zinc-800 rounded-xl border border-zinc-700 p-4">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-11 h-11 rounded-full bg-blue-900 border border-blue-700 flex items-center justify-center text-blue-300 font-black text-lg shrink-0">{{ tc.name.charAt(0) }}</div>
                                    <div>
                                        <div class="text-white font-bold text-base">{{ tc.name }}</div>
                                        <div class="text-zinc-400 text-xs">{{ tc.role }}</div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-2 text-center">
                                    <div class="bg-zinc-700/50 rounded-lg p-2.5">
                                        <div class="text-zinc-400 text-[10px] uppercase">Clock In</div>
                                        <div class="text-white font-mono text-xs font-bold mt-0.5">{{ tc.clockIn }}</div>
                                    </div>
                                    <div class="bg-zinc-700/50 rounded-lg p-2.5">
                                        <div class="text-zinc-400 text-[10px] uppercase">Hours</div>
                                        <div class="text-wolf-gold font-mono text-sm font-bold mt-0.5">{{ tc.hours }}h</div>
                                    </div>
                                    <div class="bg-zinc-700/50 rounded-lg p-2.5">
                                        <div class="text-zinc-400 text-[10px] uppercase">Tips</div>
                                        <div class="text-emerald-400 font-mono text-sm font-bold mt-0.5">${{ tc.tips.toFixed(2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- ══ REPRINT ══ -->
                    <template v-else-if="activePanel === 'reprint-panel'">
                        <div class="px-5 py-4 border-b border-zinc-700 flex items-center justify-between shrink-0">
                            <span class="text-white font-bold text-base">Print Check</span>
                            <button @click="activePanel = null" class="text-zinc-500 hover:text-white text-xl w-10 h-10 flex items-center justify-center">✕</button>
                        </div>
                        <div class="flex-1 overflow-y-auto p-4">
                            <div class="bg-white rounded-xl p-5 font-mono text-xs text-gray-800 leading-relaxed shadow-xl mx-auto max-w-[280px]">
                                <div class="text-center mb-3">
                                    <div class="text-base font-black tracking-wider">WOLFGANG'S</div>
                                    <div class="text-[10px] tracking-widest text-gray-500">STEAKHOUSE</div>
                                    <div class="text-[9px] text-gray-400 mt-1">Park Ave, New York, NY 10016</div>
                                </div>
                                <div class="border-t border-dashed border-gray-300 my-2"></div>
                                <div class="flex justify-between text-[10px] text-gray-500 mb-1"><span>Check #{{ checkNumber }}</span><span>{{ orderType }}</span></div>
                                <div class="flex justify-between text-[10px] text-gray-500 mb-1"><span>Server: {{ serverName }}</span><span v-if="tableNumber">Table {{ tableNumber }}</span></div>
                                <div class="flex justify-between text-[10px] text-gray-500 mb-2"><span>{{ receiptDate }}</span><span>{{ receiptTime }}</span></div>
                                <div class="border-t border-dashed border-gray-300 my-2"></div>
                                <div v-if="currentSeatItems.length === 0" class="text-gray-400 text-center py-2 text-[10px]">No items</div>
                                <div v-for="item in currentSeatItems" :key="item.id" class="flex justify-between mb-1">
                                    <span><span class="font-bold">{{ item.qty }}</span> {{ item.name }}</span>
                                    <span class="font-bold">${{ (item.price * item.qty).toFixed(2) }}</span>
                                </div>
                                <div class="border-t border-dashed border-gray-300 my-2"></div>
                                <div class="space-y-0.5 text-[10px]">
                                    <div class="flex justify-between"><span>Subtotal</span><span>${{ subtotal.toFixed(2) }}</span></div>
                                    <div class="flex justify-between"><span>Tax</span><span>${{ tax.toFixed(2) }}</span></div>
                                    <div v-if="gratuity > 0" class="flex justify-between"><span>Gratuity</span><span>${{ gratuity.toFixed(2) }}</span></div>
                                    <div v-if="discount > 0" class="flex justify-between text-red-600"><span>Discount</span><span>-${{ discount.toFixed(2) }}</span></div>
                                </div>
                                <div class="border-t border-gray-400 my-2"></div>
                                <div class="flex justify-between font-black text-sm"><span>TOTAL</span><span>${{ amountDue.toFixed(2) }}</span></div>
                                <div class="border-t border-dashed border-gray-300 my-2"></div>
                                <div class="text-center text-[9px] text-gray-400">Thank you for dining with us!</div>
                            </div>
                        </div>
                        <div class="px-4 py-4 border-t border-zinc-700 shrink-0 flex gap-3">
                            <button @click="activePanel = null" class="flex-1 bg-zinc-700 hover:bg-zinc-600 text-white font-bold py-5 rounded-xl text-base transition-colors pos-btn-press">Close</button>
                            <button @click="showNotif('Sent to printer')" class="flex-1 bg-zinc-200 hover:bg-white text-zinc-900 font-bold py-5 rounded-xl text-base transition-colors pos-btn-press">🖨️ Print</button>
                        </div>
                    </template>

                </div>
            </Transition>
        </div>
    </div>
</template>

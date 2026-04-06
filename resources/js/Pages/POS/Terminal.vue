<script setup>
import { ref, computed, reactive, onMounted, onUnmounted } from 'vue';
import CategorySidebar from '@/Components/POS/CategorySidebar.vue';
import MenuGrid from '@/Components/POS/MenuGrid.vue';
import OrderPanel from '@/Components/POS/OrderPanel.vue';
import ActionButtons from '@/Components/POS/ActionButtons.vue';
import BottomBar from '@/Components/POS/BottomBar.vue';
import OrderTotals from '@/Components/POS/OrderTotals.vue';
import SeatTabs from '@/Components/POS/SeatTabs.vue';
import ModifierModal from '@/Components/POS/ModifierModal.vue';
import NumpadModal from '@/Components/POS/NumpadModal.vue';
import PaymentModal from '@/Components/POS/PaymentModal.vue';
import DiscountModal from '@/Components/POS/DiscountModal.vue';
import ManagerDashboard from '@/Components/POS/ManagerDashboard.vue';
import ManagerModal from '@/Components/POS/ManagerModal.vue';
import TableModal from '@/Components/POS/TableModal.vue';
import ReceiptModal from '@/Components/POS/ReceiptModal.vue';
import HeldOrdersModal from '@/Components/POS/HeldOrdersModal.vue';

const props = defineProps({
    categories: Array,
    modifiers: Object,
    serverName: String,
    tableNumber: Number,
});

// ─── State ──────────────────────────────────────────────────
const activeCategory = ref(0);
const seats = ref([{ id: 1, name: 'Seat 1', items: [] }]);
const activeSeat = ref(0);
const orderType = ref('Dine In');
const nextOrderId = ref(100001);
const selectedOrderItem = ref(null);
const qtyMode = ref(false);
const qtyBuffer = ref('');
const currentTable = ref(props.tableNumber);
const orderNumber = ref(1001);
const checkNumber = ref(5001);

// Modals
const showModifierModal = ref(false);
const showNumpadModal = ref(false);
const showPaymentModal = ref(false);
const showDiscountModal = ref(false);
const showManagerModal = ref(false);
const showTableModal = ref(false);
const showReceiptModal = ref(false);
const showHeldOrdersModal = ref(false);
const numpadMode = ref('typeIn'); // 'typeIn', 'qty', 'openFood', 'openBar'

// Editable categories (for price management)
const editableCategories = ref(props.categories.map(cat => ({
    ...cat,
    items: cat.items.map(item => ({ ...item })),
})));

// Receipt-after-payment tracking
const postPaymentReceipt = ref(false);
const lastPaymentMethod = ref(null);

// Held orders
const heldOrders = ref([]);

// Session sales tracking
const sessionChecks = ref(0);
const sessionTotal = ref(0);

// ─── Employees & Auth ────────────────────────────────────────
const employees = ref([
    { id: '1',  name: 'Wolfgang',      role: 'Manager' },
    { id: '7',  name: 'Marcus Webb',   role: 'Host' },
    { id: '15', name: 'Sarah Miller',  role: 'Server' },
    { id: '23', name: 'Emma Chen',     role: 'Server' },
    { id: '50', name: 'Jake Torres',   role: 'Bartender' },
]);

const isLoggedIn = ref(false);
const currentEmployee = ref(null);
const loginInput = ref('');
const loginError = ref('');
const managerInTerminal = ref(false);

const isManager = computed(() => currentEmployee.value?.role === 'Manager');

const liveEmployee = computed(() =>
    loginInput.value ? (employees.value.find(e => e.id === loginInput.value) ?? null) : null
);

function pressLoginDigit(d) {
    if (loginInput.value.length >= 6) return;
    loginInput.value += d;
    loginError.value = '';
}

function backspaceLogin() {
    loginInput.value = loginInput.value.slice(0, -1);
    loginError.value = '';
}

function submitLogin() {
    const emp = employees.value.find(e => e.id === loginInput.value);
    if (emp) {
        currentEmployee.value = emp;
        isLoggedIn.value = true;
        loginInput.value = '';
        loginError.value = '';
    } else {
        loginError.value = 'Employee not found';
        loginInput.value = '';
    }
}

function signOff() {
    isLoggedIn.value = false;
    currentEmployee.value = null;
    loginInput.value = '';
    loginError.value = '';
    managerInTerminal.value = false;
    newSale();
}

// Demo notification
const notifications = ref([]);
let notifId = 0;

// ─── Clock ──────────────────────────────────────────────────
const currentTime = ref(new Date());
let clockInterval;
onMounted(() => {
    clockInterval = setInterval(() => { currentTime.value = new Date(); }, 1000);
});
onUnmounted(() => clearInterval(clockInterval));

const formattedTime = computed(() =>
    currentTime.value.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true })
);
const formattedDate = computed(() =>
    currentTime.value.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' })
);

// ─── Computed ───────────────────────────────────────────────
const currentSeatItems = computed(() => seats.value[activeSeat.value]?.items || []);
const totalItemCount = computed(() => currentSeatItems.value.reduce((s, i) => s + i.qty, 0));
const allSeatItems = computed(() => seats.value.flatMap(s => s.items));

const subtotal = computed(() =>
    currentSeatItems.value.reduce((sum, item) => {
        const addonTotal = (item.modifiers || [])
            .filter(m => m.price)
            .reduce((s, m) => s + m.price, 0);
        return sum + ((item.price + addonTotal) * item.qty);
    }, 0)
);

const tax = computed(() => Math.round(subtotal.value * 0.08875 * 100) / 100);
const gratuity = ref(0);
const discount = ref(0);
const discountType = ref('fixed'); // 'fixed' or 'percent'
const discountValue = ref(0);
const computedDiscount = computed(() => {
    if (discountType.value === 'percent') {
        return Math.round(subtotal.value * (discountValue.value / 100) * 100) / 100;
    }
    return discountValue.value;
});

const amountDue = computed(() =>
    Math.max(0, subtotal.value + tax.value + gratuity.value - computedDiscount.value)
);

// ─── Notifications ──────────────────────────────────────────
function notify(message, type = 'info') {
    const id = ++notifId;
    notifications.value.push({ id, message, type });
    setTimeout(() => {
        notifications.value = notifications.value.filter(n => n.id !== id);
    }, 3000);
}

// ─── Order Actions ──────────────────────────────────────────
function addItem(item) {
    const qty = qtyMode.value && qtyBuffer.value ? parseInt(qtyBuffer.value) || 1 : 1;

    const newItem = {
        id: nextOrderId.value++,
        name: item.name,
        price: item.price,
        qty: qty,
        modifiers: [],
        sent: false,
        fired: false,
        voided: false,
        seatId: seats.value[activeSeat.value].id,
    };
    seats.value[activeSeat.value].items.push(newItem);
    selectedOrderItem.value = seats.value[activeSeat.value].items.length - 1;

    qtyMode.value = false;
    qtyBuffer.value = '';
}

function removeSelectedItem() {
    if (selectedOrderItem.value !== null) {
        const item = seats.value[activeSeat.value].items[selectedOrderItem.value];
        if (item.sent) {
            notify('Cannot remove sent items. Use Manager Void.', 'error');
            return;
        }
        seats.value[activeSeat.value].items.splice(selectedOrderItem.value, 1);
        selectedOrderItem.value = null;
        notify('Item removed');
    }
}

function voidItem(index) {
    if (index !== null && index !== undefined) {
        seats.value[activeSeat.value].items.splice(index, 1);
        selectedOrderItem.value = null;
        notify('Item voided by manager');
    }
}

function addSeat() {
    const newId = seats.value.length + 1;
    seats.value.push({ id: newId, name: `Seat ${newId}`, items: [] });
    activeSeat.value = seats.value.length - 1;
    selectedOrderItem.value = null;
}

function removeSeat(index) {
    if (seats.value.length <= 1) return;
    if (seats.value[index].items.length > 0) {
        notify('Move items first before removing seat', 'error');
        return;
    }
    seats.value.splice(index, 1);
    if (activeSeat.value >= seats.value.length) activeSeat.value = seats.value.length - 1;
}

function setQtyMode() {
    qtyMode.value = !qtyMode.value;
    if (qtyMode.value) {
        numpadMode.value = 'qty';
        showNumpadModal.value = true;
    }
}

function setOrderType(type) {
    orderType.value = type;
    notify(`Order type: ${type}`);
}

function selectCategory(index) {
    activeCategory.value = index;
}

function newSale() {
    seats.value = [{ id: 1, name: 'Seat 1', items: [] }];
    activeSeat.value = 0;
    selectedOrderItem.value = null;
    gratuity.value = 0;
    discountValue.value = 0;
    discountType.value = 'fixed';
    currentTable.value = null;
    orderType.value = 'Dine In';
    orderNumber.value++;
    checkNumber.value++;
    notify('New sale started');
}

function sendOrder() {
    const unsent = seats.value[activeSeat.value].items.filter(i => !i.sent);
    if (unsent.length === 0) {
        notify('No new items to send', 'error');
        return;
    }
    unsent.forEach(item => { item.sent = true; });
    notify(`${unsent.length} item(s) sent to kitchen`, 'success');
}

function holdOrder() {
    if (allSeatItems.value.length === 0) {
        notify('Nothing to hold', 'error');
        return;
    }
    heldOrders.value.push({
        id: orderNumber.value,
        table: currentTable.value,
        type: orderType.value,
        seats: JSON.parse(JSON.stringify(seats.value)),
        time: new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }),
        total: subtotal.value,
    });
    notify(`Order #${orderNumber.value} held`);
    newSale();
}

function recallOrder(order) {
    seats.value = order.seats;
    currentTable.value = order.table;
    orderType.value = order.type;
    orderNumber.value = order.id;
    activeSeat.value = 0;
    selectedOrderItem.value = null;
    heldOrders.value = heldOrders.value.filter(o => o.id !== order.id);
    showHeldOrdersModal.value = false;
    notify(`Order #${order.id} recalled`);
}

function fireOrder() {
    const unfired = seats.value[activeSeat.value].items.filter(i => i.sent && !i.fired);
    if (unfired.length === 0) {
        notify('No items to fire', 'error');
        return;
    }
    unfired.forEach(item => { item.fired = true; });
    notify(`${unfired.length} item(s) fired!`, 'success');
}

function reorderSelected() {
    if (selectedOrderItem.value !== null) {
        const orig = seats.value[activeSeat.value].items[selectedOrderItem.value];
        const item = {
            ...orig,
            id: nextOrderId.value++,
            sent: false,
            fired: false,
            modifiers: [...(orig.modifiers || [])],
        };
        seats.value[activeSeat.value].items.push(item);
        selectedOrderItem.value = seats.value[activeSeat.value].items.length - 1;
        notify('Item reordered');
    }
}

function openModifyItem() {
    if (selectedOrderItem.value === null) {
        notify('Select an item first', 'error');
        return;
    }
    showModifierModal.value = true;
}

function applyModifiers(mods) {
    if (selectedOrderItem.value !== null) {
        seats.value[activeSeat.value].items[selectedOrderItem.value].modifiers = mods;
    }
    showModifierModal.value = false;
}

function openTypeIn() {
    numpadMode.value = 'typeIn';
    showNumpadModal.value = true;
}

function openOpenFood() {
    numpadMode.value = 'openFood';
    showNumpadModal.value = true;
}

function openOpenBar() {
    numpadMode.value = 'openBar';
    showNumpadModal.value = true;
}

function changeItemQty(index) {
    selectedOrderItem.value = index;
    numpadMode.value = 'changeQty';
    showNumpadModal.value = true;
}

function handleNumpadSubmit(value, label) {
    if (numpadMode.value === 'changeQty') {
        const qty = parseInt(value);
        if (!qty || qty < 1) {
            notify('Invalid quantity', 'error');
            showNumpadModal.value = false;
            return;
        }
        if (selectedOrderItem.value !== null) {
            const item = seats.value[activeSeat.value].items[selectedOrderItem.value];
            if (item.sent) {
                notify('Cannot change qty of sent items. Use Manager Void.', 'error');
                showNumpadModal.value = false;
                return;
            }
            item.qty = qty;
            notify(`Quantity updated to ${qty}`);
        }
        showNumpadModal.value = false;
        return;
    }
    if (numpadMode.value === 'qty') {
        qtyBuffer.value = value;
        qtyMode.value = true;
        showNumpadModal.value = false;
        notify(`Quantity set to ${value}`);
        return;
    }

    const price = parseFloat(value);
    if (isNaN(price) || price <= 0) {
        notify('Invalid amount', 'error');
        return;
    }

    const prefix = numpadMode.value === 'openBar' ? 'Open Bar' : 'Open Food';
    const name = label || `${prefix} Item`;
    addItem({ name, price, color: 'gold' });
    showNumpadModal.value = false;
}

function openPayment() {
    if (amountDue.value <= 0) {
        notify('Nothing to settle', 'error');
        return;
    }
    showPaymentModal.value = true;
}

function processPayment(method, tipAmount, giftCardInfo) {
    gratuity.value = tipAmount || 0;
    sessionChecks.value++;
    sessionTotal.value += amountDue.value;
    lastPaymentMethod.value = method;
    postPaymentReceipt.value = true;
    showPaymentModal.value = false;
    if (method === 'gift' && giftCardInfo) {
        notify(`$${giftCardInfo.charged.toFixed(2)} charged to gift card ${giftCardInfo.serial} (remaining: $${giftCardInfo.remainingBalance.toFixed(2)})`, 'success');
    } else {
        notify(`Payment of $${amountDue.value.toFixed(2)} processed via ${method}`, 'success');
    }
    showReceiptModal.value = true;
}

function closeReceipt() {
    showReceiptModal.value = false;
    if (postPaymentReceipt.value) {
        postPaymentReceipt.value = false;
        lastPaymentMethod.value = null;
        newSale();
    }
}

function compItem(index) {
    if (index !== null && index !== undefined) {
        seats.value[activeSeat.value].items[index].price = 0;
        notify('Item comped');
    }
}

function openDiscount() {
    showDiscountModal.value = true;
}

function applyDiscount(type, value) {
    discountType.value = type;
    discountValue.value = value;
    showDiscountModal.value = false;
    notify(`Discount applied: ${type === 'percent' ? value + '%' : '$' + value.toFixed(2)}`);
}

function openTableSelect() {
    showTableModal.value = true;
}

function selectTable(tableNum) {
    currentTable.value = tableNum;
    orderType.value = 'Dine In';
    showTableModal.value = false;
    notify(`Table ${tableNum} selected`);
}

function printCheck() {
    if (allSeatItems.value.length === 0) {
        notify('No items on check', 'error');
        return;
    }
    showReceiptModal.value = true;
}

function moveItemToSeat(itemIndex, targetSeatIndex) {
    const item = seats.value[activeSeat.value].items.splice(itemIndex, 1)[0];
    item.seatId = seats.value[targetSeatIndex].id;
    seats.value[targetSeatIndex].items.push(item);
    selectedOrderItem.value = null;
    notify(`Item moved to ${seats.value[targetSeatIndex].name}`);
}

// ─── Price Management ───────────────────────────────────────
function updateItemPrice(catIdx, itemIdx, newPrice) {
    editableCategories.value[catIdx].items[itemIdx].price = parseFloat(newPrice) || 0;
    notify('Price updated', 'success');
}

// ─── Employee Management ────────────────────────────────────
function addEmployee(emp) {
    employees.value.push(emp);
    notify(`Employee ${emp.name} added`, 'success');
}

function updateEmployee(id, updates) {
    const idx = employees.value.findIndex(e => e.id === id);
    if (idx !== -1) {
        employees.value[idx] = { ...employees.value[idx], ...updates };
        notify('Employee updated', 'success');
    }
}

function deleteEmployee(id) {
    if (currentEmployee.value?.id === id) {
        notify('Cannot delete the currently logged-in employee', 'error');
        return;
    }
    employees.value = employees.value.filter(e => e.id !== id);
    notify('Employee removed', 'success');
}

// ─── Keyboard shortcuts ─────────────────────────────────────
function handleKeydown(e) {    if (!isLoggedIn.value) {
        if (e.key >= '0' && e.key <= '9') pressLoginDigit(e.key);
        else if (e.key === 'Backspace') backspaceLogin();
        else if (e.key === 'Enter') submitLogin();
        return;
    }

    if (showNumpadModal.value || showPaymentModal.value || showModifierModal.value) return;

    if (e.key === 'Escape') {
        qtyMode.value = false;
        qtyBuffer.value = '';
        selectedOrderItem.value = null;
    }
    if (e.key === 'Delete' && selectedOrderItem.value !== null) {
        removeSelectedItem();
    }
}

onMounted(() => window.addEventListener('keydown', handleKeydown));
onUnmounted(() => window.removeEventListener('keydown', handleKeydown));
</script>

<template>
    <!-- ── Login Screen ─────────────────────────────────────── -->
    <div v-if="!isLoggedIn" class="h-screen w-screen bg-wolf-bg flex flex-col overflow-hidden select-none font-sans">
        <!-- Header -->
        <div class="h-14 bg-wolf-bg-light border-b border-wolf-border flex items-center justify-between px-6 shrink-0">
            <span class="text-wolf-gold font-bold text-base tracking-widest uppercase">Wolfgang's POS</span>
            <span class="text-wolf-text-muted text-sm font-mono">{{ formattedDate }} &bull; {{ formattedTime }}</span>
        </div>

        <!-- Body -->
        <div class="flex-1 flex items-center justify-center p-8">

            <!-- Numpad -->
            <div class="flex flex-col items-center gap-7">
                <p class="text-wolf-text-muted text-base font-semibold tracking-widest uppercase">Enter Employee #</p>

                <!-- Number display -->
                <div class="w-96 bg-wolf-bg-card border border-wolf-border rounded-2xl px-6 py-5 text-center">
                    <div class="text-wolf-cream font-mono text-5xl font-bold tracking-[0.3em] min-h-[4.5rem] flex items-center justify-center">
                        {{ loginInput || '\u00a0' }}
                    </div>
                    <div class="h-7 mt-2">
                        <span v-if="loginError" class="text-red-400 text-base">{{ loginError }}</span>
                        <span v-else-if="liveEmployee" class="text-wolf-gold text-base">{{ liveEmployee.name }} &bull; {{ liveEmployee.role }}</span>
                    </div>
                </div>

                <!-- Numpad grid -->
                <div class="grid grid-cols-3 gap-4 w-96">
                    <button
                        v-for="d in ['1','2','3','4','5','6','7','8','9']"
                        :key="d"
                        @click="pressLoginDigit(d)"
                        class="bg-wolf-bg-elevated hover:bg-wolf-bg-card border border-wolf-border rounded-2xl h-24 text-wolf-cream font-bold text-3xl transition-all active:scale-95 pos-btn-press"
                    >{{ d }}</button>

                    <button
                        @click="backspaceLogin"
                        class="bg-wolf-bg-elevated hover:bg-wolf-bg-card border border-wolf-border rounded-2xl h-24 text-wolf-text-secondary transition-all active:scale-95 pos-btn-press flex items-center justify-center"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-9 h-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"/><line x1="18" y1="9" x2="12" y2="15"/><line x1="12" y1="9" x2="18" y2="15"/></svg>
                    </button>
                    <button
                        @click="pressLoginDigit('0')"
                        class="bg-wolf-bg-elevated hover:bg-wolf-bg-card border border-wolf-border rounded-2xl h-24 text-wolf-cream font-bold text-3xl transition-all active:scale-95 pos-btn-press"
                    >0</button>
                    <button
                        @click="submitLogin"
                        class="bg-wolf-gold/20 hover:bg-wolf-gold/30 border border-wolf-gold/50 rounded-2xl h-24 text-wolf-gold font-bold text-xl transition-all active:scale-95 pos-btn-press"
                    >Enter</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Manager Dashboard (home for managers) ─────────── -->
    <ManagerDashboard
        v-else-if="isManager && !managerInTerminal"
        :heldOrders="heldOrders"
        :currentSeatItems="currentSeatItems"
        :selectedOrderItem="selectedOrderItem"
        :subtotal="subtotal"
        :tax="tax"
        :gratuity="gratuity"
        :discount="computedDiscount"
        :amountDue="amountDue"
        :orderType="orderType"
        :tableNumber="currentTable"
        :checkNumber="checkNumber"
        :serverName="currentEmployee?.name || serverName || ''"
        :employees="employees"
        :categories="editableCategories"
        :sessionChecks="sessionChecks"
        :sessionTotal="sessionTotal"
        :currentEmployee="currentEmployee"
        @go-to-terminal="managerInTerminal = true"
        @sign-off="signOff"
        @void-sale="newSale"
        @apply-discount="applyDiscount"
        @void-item="voidItem"
        @comp-item="compItem"
        @recall-order="recallOrder"
        @delete-held="(id) => { heldOrders = heldOrders.filter(o => o.id !== id); notify('Held order deleted'); }"
        @open-drawer="notify('Cash drawer opened', 'success')"
        @notify="notify"
        @update-price="updateItemPrice"
        @add-employee="addEmployee"
        @update-employee="updateEmployee"
        @delete-employee="deleteEmployee"
    />

    <!-- ── POS Terminal ──────────────────────────────────────── -->
    <div v-else class="h-screen w-screen bg-wolf-bg flex flex-col overflow-hidden select-none font-sans">

        <!-- Notifications Toast -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-4"
        >
            <div v-if="notifications.length" class="fixed top-12 left-1/2 -translate-x-1/2 z-[100] flex flex-col gap-1.5">
                <div
                    v-for="n in notifications"
                    :key="n.id"
                    class="px-4 py-2 rounded-lg shadow-xl text-sm font-semibold backdrop-blur-sm border"
                    :class="{
                        'bg-emerald-900/90 text-emerald-200 border-emerald-700': n.type === 'success',
                        'bg-red-900/90 text-red-200 border-red-700': n.type === 'error',
                        'bg-wolf-bg-elevated/90 text-wolf-cream border-wolf-border': n.type === 'info',
                    }"
                >
                    {{ n.message }}
                </div>
            </div>
        </Transition>

        <!-- Top Info Bar -->
        <div class="h-14 bg-wolf-bg-light border-b border-wolf-border flex items-center justify-between px-4 shrink-0">
            <div class="flex items-center gap-4">
                <span class="text-wolf-gold font-bold text-sm tracking-widest uppercase">Wolfgang's</span>
                <div class="w-px h-5 bg-wolf-border"></div>
                <span class="text-wolf-text-secondary text-xs font-medium">{{ currentEmployee?.name || serverName }}</span>
                <div v-if="currentTable" class="flex items-center gap-1.5 bg-wolf-burgundy/20 border border-wolf-burgundy/40 rounded px-2 py-0.5">
                    <span class="text-wolf-burgundy-light text-[10px] font-bold">TABLE</span>
                    <span class="text-wolf-cream text-xs font-bold">{{ currentTable }}</span>
                </div>
                <div class="flex items-center gap-1.5 bg-wolf-bg-elevated rounded px-2 py-0.5 border border-wolf-border">
                    <span class="text-wolf-text-muted text-[10px]">CHK</span>
                    <span class="text-wolf-cream-dark text-xs font-mono">#{{ checkNumber }}</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-wolf-text-muted text-xs">{{ formattedDate }}</span>
                <span class="text-wolf-cream font-mono text-sm font-semibold bg-wolf-bg-elevated px-3 py-0.5 rounded border border-wolf-border">{{ formattedTime }}</span>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Left: Category Sidebar -->
            <CategorySidebar
                :categories="editableCategories"
                :activeIndex="activeCategory"
                @select="selectCategory"
            />

            <!-- Center: Menu Grid -->
            <div class="flex-1 flex flex-col min-w-0">
                <MenuGrid
                    :items="editableCategories[activeCategory]?.items || []"
                    :categoryName="editableCategories[activeCategory]?.name || ''"
                    @add-item="addItem"
                />
                <BottomBar
                    @fire-order="fireOrder"
                    @type-in="openTypeIn"
                    @open-food="openOpenFood"
                    @open-bar="openOpenBar"
                    @print-check="printCheck"
                    @send-order="sendOrder"
                    @hold-order="holdOrder"
                    @recall="showHeldOrdersModal = true"
                    @sign-off="signOff"
                    @kitchen-mod="openModifyItem"
                    :heldCount="heldOrders.length"
                />
            </div>

            <!-- Right: Order Panel + Actions -->
            <div class="w-[400px] flex flex-col bg-wolf-bg-light border-l border-wolf-border shrink-0">
                <!-- Order Header -->
                <div class="bg-wolf-bg-card px-4 py-2.5 border-b border-wolf-border">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <h2 class="text-wolf-cream font-bold text-base">
                                {{ orderType === 'Dine In' && currentTable ? `Table ${currentTable}` : 'New Sale' }}
                            </h2>
                            <span class="text-wolf-text-muted text-[10px] bg-wolf-bg-elevated px-1.5 py-0.5 rounded border border-wolf-border font-mono">
                                #{{ orderNumber }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span v-if="totalItemCount" class="text-[10px] bg-wolf-gold/20 text-wolf-gold px-2 py-0.5 rounded-full font-bold border border-wolf-gold/30">
                                {{ totalItemCount }} items
                            </span>
                            <button
                                @click="newSale"
                                class="text-[10px] bg-wolf-burgundy/80 hover:bg-wolf-burgundy text-wolf-cream px-2.5 py-1 rounded transition-colors font-bold pos-btn-press"
                            >
                                NEW
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Seat Tabs -->
                <SeatTabs
                    :seats="seats"
                    :activeSeat="activeSeat"
                    @select="(i) => { activeSeat = i; selectedOrderItem = null; }"
                    @add-seat="addSeat"
                    @remove-seat="removeSeat"
                />

                <!-- Order Items List -->
                <OrderPanel
                    :items="currentSeatItems"
                    :selectedIndex="selectedOrderItem"
                    :seats="seats"
                    :activeSeat="activeSeat"
                    @select="selectedOrderItem = $event"
                    @move-item="moveItemToSeat"
                    @change-qty="changeItemQty"
                />

                <!-- Action Buttons -->
                <ActionButtons
                    :orderType="orderType"
                    :qtyMode="qtyMode"
                    @delivery="setOrderType('Delivery')"
                    @pickup="setOrderType('Pick Up')"
                    @take-table="openTableSelect"
                    @qty="setQtyMode"
                    @error-correct="removeSelectedItem"
                    @reorder="reorderSelected"
                    @modify="openModifyItem"
                    @discount="openDiscount"
                />

                <!-- Order Totals -->
                <OrderTotals
                    :subtotal="subtotal"
                    :tax="tax"
                    :gratuity="gratuity"
                    :discount="computedDiscount"
                    :amountDue="amountDue"
                    :orderType="orderType"
                    @settle="openPayment"
                    @manager="isManager ? (managerInTerminal = false) : (showManagerModal = true)"
                    @server="notify('Server functions')"
                />
            </div>
        </div>

        <!-- Modals -->
        <ModifierModal
            v-if="showModifierModal"
            :modifiers="modifiers"
            :currentMods="selectedOrderItem !== null ? currentSeatItems[selectedOrderItem]?.modifiers || [] : []"
            :itemName="selectedOrderItem !== null ? currentSeatItems[selectedOrderItem]?.name : ''"
            @close="showModifierModal = false"
            @apply="applyModifiers"
        />

        <NumpadModal
            v-if="showNumpadModal"
            :mode="numpadMode"
            @close="showNumpadModal = false; qtyMode = false;"
            @submit="handleNumpadSubmit"
        />

        <PaymentModal
            v-if="showPaymentModal"
            :amountDue="amountDue"
            :subtotal="subtotal"
            :tax="tax"
            @close="showPaymentModal = false"
            @pay="processPayment"
        />

        <DiscountModal
            v-if="showDiscountModal"
            :subtotal="subtotal"
            @close="showDiscountModal = false"
            @apply="applyDiscount"
        />

        <ManagerModal
            v-if="showManagerModal"
            :items="currentSeatItems"
            :selectedIndex="selectedOrderItem"
            @close="showManagerModal = false"
            @void="voidItem"
            @comp="(i) => { if (i !== null) { compItem(i); } showManagerModal = false; }"
            @open-drawer="notify('Cash drawer opened', 'success'); showManagerModal = false;"
            @reprint="printCheck(); showManagerModal = false;"
        />

        <TableModal
            v-if="showTableModal"
            @close="showTableModal = false"
            @select="selectTable"
        />

        <ReceiptModal
            v-if="showReceiptModal"
            :seats="seats"
            :subtotal="subtotal"
            :tax="tax"
            :gratuity="gratuity"
            :discount="computedDiscount"
            :amountDue="amountDue"
            :orderType="orderType"
            :tableNumber="currentTable"
            :checkNumber="checkNumber"
            :serverName="serverName"
            :paymentMethod="lastPaymentMethod"
            :afterPayment="postPaymentReceipt"
            @close="closeReceipt"
            @new-sale="closeReceipt"
        />

        <HeldOrdersModal
            v-if="showHeldOrdersModal"
            :orders="heldOrders"
            @close="showHeldOrdersModal = false"
            @recall="recallOrder"
            @delete="(id) => { heldOrders = heldOrders.filter(o => o.id !== id); notify('Held order deleted'); }"
        />
    </div>
</template>

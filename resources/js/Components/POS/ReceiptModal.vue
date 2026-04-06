<script setup>
const props = defineProps({
    seats: Array,
    subtotal: Number,
    tax: Number,
    gratuity: Number,
    discount: Number,
    amountDue: Number,
    orderType: String,
    tableNumber: Number,
    checkNumber: Number,
    serverName: String,
    paymentMethod: { type: String, default: null },
    afterPayment: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'new-sale']);

const methodLabel = {
    cash: 'Cash',
    credit: 'Credit Card',
    debit: 'Debit Card',
    gift: 'Gift Card',
};

const now = new Date();
const dateStr = now.toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' });
const timeStr = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
</script>

<template>
    <div class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4" @click.self="$emit('close')">
        <div class="bg-white rounded-xl shadow-2xl w-80 overflow-hidden">
            <!-- Receipt -->
            <div class="p-6 font-mono text-xs text-gray-800 leading-relaxed">
                <!-- Header -->
                <div class="text-center mb-4">
                    <div class="text-base font-black tracking-wider">WOLFGANG'S</div>
                    <div class="text-[10px] tracking-widest text-gray-500">STEAKHOUSE</div>
                    <div class="text-[9px] text-gray-400 mt-1">Park Ave, New York, NY 10016</div>
                    <div class="text-[9px] text-gray-400">(212) 925-0350</div>
                </div>

                <div class="border-t border-dashed border-gray-300 my-3"></div>

                <!-- Order Info -->
                <div class="flex justify-between text-[10px] text-gray-500 mb-1">
                    <span>Check #{{ checkNumber }}</span>
                    <span>{{ orderType }}</span>
                </div>
                <div class="flex justify-between text-[10px] text-gray-500 mb-1">
                    <span>Server: {{ serverName }}</span>
                    <span v-if="tableNumber">Table {{ tableNumber }}</span>
                </div>
                <div class="flex justify-between text-[10px] text-gray-500 mb-2">
                    <span>{{ dateStr }}</span>
                    <span>{{ timeStr }}</span>
                </div>

                <div class="border-t border-dashed border-gray-300 my-3"></div>

                <!-- Items by Seat -->
                <div v-for="seat in seats" :key="seat.id">
                    <div v-if="seats.length > 1" class="text-[9px] font-bold text-gray-400 uppercase mt-2 mb-1">{{ seat.name }}</div>
                    <div v-for="item in seat.items" :key="item.id" class="mb-1">
                        <div class="flex justify-between">
                            <span>
                                <span class="font-bold">{{ item.qty }}</span> {{ item.name }}
                            </span>
                            <span class="font-bold">${{ (item.price * item.qty).toFixed(2) }}</span>
                        </div>
                        <div v-for="mod in item.modifiers || []" :key="mod.name" class="text-[9px] text-gray-400 pl-4">
                            → {{ mod.name }} <span v-if="mod.price">+${{ mod.price.toFixed(2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="border-t border-dashed border-gray-300 my-3"></div>

                <!-- Totals -->
                <div class="space-y-1">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span>${{ subtotal.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tax</span>
                        <span>${{ tax.toFixed(2) }}</span>
                    </div>
                    <div v-if="gratuity > 0" class="flex justify-between">
                        <span>Gratuity</span>
                        <span>${{ gratuity.toFixed(2) }}</span>
                    </div>
                    <div v-if="discount > 0" class="flex justify-between text-red-600">
                        <span>Discount</span>
                        <span>-${{ discount.toFixed(2) }}</span>
                    </div>
                </div>

                <div class="border-t border-gray-400 my-2"></div>

                <div class="flex justify-between text-sm font-black">
                    <span>TOTAL</span>
                    <span>${{ amountDue.toFixed(2) }}</span>
                </div>

                <div v-if="paymentMethod" class="flex justify-between text-[10px] text-gray-500 mt-1">
                    <span>Payment</span>
                    <span class="font-semibold">{{ methodLabel[paymentMethod] || paymentMethod }}</span>
                </div>

                <div class="border-t border-dashed border-gray-300 my-3"></div>

                <div class="text-center text-[9px] text-gray-400">
                    <div>Thank you for dining with us!</div>
                    <div class="mt-1">wolfgangssteakhouse.net</div>
                </div>
            </div>

            <!-- Close Button -->
            <div class="bg-gray-50 px-5 py-3 border-t flex justify-center gap-2">
                <button @click="$emit('close')" class="px-6 py-2 text-xs font-bold text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Close
                </button>
                <button @click="$emit('close')" class="px-6 py-2 text-xs font-bold text-white bg-gray-800 rounded-lg hover:bg-gray-700 transition-colors">
                    Print
                </button>
                <button v-if="afterPayment" @click="$emit('new-sale')" class="px-6 py-2 text-xs font-bold text-white bg-emerald-700 rounded-lg hover:bg-emerald-600 transition-colors">
                    New Sale
                </button>
            </div>
        </div>
    </div>
</template>

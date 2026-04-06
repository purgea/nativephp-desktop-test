<script setup>
defineProps({
    categories: Array,
    activeIndex: Number,
});

defineEmits(['select']);
</script>

<template>
    <div class="w-52 bg-wolf-bg-light border-r border-wolf-border flex flex-col overflow-y-auto shrink-0">
        <button
            v-for="(category, index) in categories"
            :key="index"
            @click="$emit('select', index)"
            class="relative px-3 py-5 text-sm font-bold text-center leading-tight uppercase tracking-wider transition-all duration-150 border-b border-wolf-border/50 min-h-[68px]"
            :class="[
                activeIndex === index
                    ? 'bg-wolf-bg-card text-wolf-gold shadow-inner'
                    : 'text-wolf-text-muted hover:bg-wolf-bg-card/50 hover:text-wolf-cream-dark',
            ]"
        >
            <span class="relative z-10">{{ category.name }}</span>
            <div
                v-if="activeIndex === index"
                class="absolute right-0 top-1/2 -translate-y-1/2 w-[3px] h-7 bg-wolf-gold rounded-l"
            ></div>
            <div
                v-if="activeIndex === index"
                class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-7 bg-wolf-gold rounded-r"
            ></div>
        </button>
    </div>
</template>

<script setup>
const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  color: {
    type: String,
    required: false,
    default: 'primary',
  },
  icon: {
    type: String,
    required: true,
  },
  stats: {
    type: String,
    required: true,
  },
  change: {
    type: Number,
    required: true,
  },
  subtitle: {
    type: String,
    required: true,
  },
})

const isPositive = computed(() => Math.sign(props.change) === 1)
</script>

<template>
  <VCard>
    <VCardText class="d-flex align-center">
      <VAvatar
        v-if="props.icon"
        rounded="lg"
        variant="tonal"
        :color="props.color"
      >
        <VIcon
          :icon="props.icon"
          size="24"
        />
      </VAvatar>

      <VSpacer />

      <div
        v-if="props.change"
        :class="isPositive ? 'text-success' : 'text-error'"
        class="d-flex align-center text-base mt-n4"
      >
        <span>{{ isPositive ? `+${props.change}` : props.change }}%</span>

        <VIcon
          :icon="isPositive ? 'ri-arrow-up-s-line' : 'ri-arrow-down-s-line'"
          size="18"
        />
      </div>
    </VCardText>

    <VCardText>
      <h5 class="text-h5 mb-1">
        {{ props.stats }}
      </h5>
      <p>
        {{ props.title }}
      </p>
    </VCardText>
  </VCard>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import QuoteCotizador from '@/Components/QuoteCotizador.vue'

defineProps({
  products: {
    type: Array,
    default: () => [],
  },
  googleMapsApiKey: {
    type: String,
    default: null,
  },
})
</script>

<template>
  <AppLayout title="Cotizador">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight">
        Cotizador
      </h2>
    </template>

    <div class="py-4">
      <QuoteCotizador
        :products="products"
        :google-maps-api-key="googleMapsApiKey"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { Button } from '@/Components/shadcn/ui/button'
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandItem,
  CommandList,
} from '@/Components/shadcn/ui/command'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/shadcn/ui/card'
import { Label } from '@/Components/shadcn/ui/label'
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/shadcn/ui/popover'
import axios from 'axios'
import { Icon } from '@iconify/vue'
import { computed, ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  products: {
    type: Array,
    default: () => [],
  },
  googleMapsApiKey: {
    type: String,
    default: null,
  },
})

const productId = ref('')
const productComboboxOpen = ref(false)
const searchTerm = ref('')

const selectedProduct = computed(() =>
  props.products.find(p => String(p.id) === productId.value),
)

const displayLabel = computed(() => {
  if (!selectedProduct.value) return ''
  const p = selectedProduct.value
  return p.material?.name ? `${p.name} — ${p.material.name}` : p.name
})

const filteredProducts = computed(() => {
  if (!searchTerm.value.trim()) return props.products
  const t = searchTerm.value.toLowerCase()
  return props.products.filter(
    p =>
      p.name.toLowerCase().includes(t)
      || (p.material?.name && p.material.name.toLowerCase().includes(t)),
  )
})

function productFilter(_list, term) {
  if (!term.trim()) return props.products.map(p => String(p.id))
  const t = term.toLowerCase()
  return props.products
    .filter(
      p =>
        p.name.toLowerCase().includes(t)
        || (p.material?.name && p.material.name.toLowerCase().includes(t)),
    )
    .map(p => String(p.id))
}

function onProductSelect(id) {
  productId.value = id
  productComboboxOpen.value = false
  searchTerm.value = ''
}

function onTriggerInput(e) {
  const v = e.target.value
  if (productComboboxOpen.value) {
    searchTerm.value = v
  } else {
    productComboboxOpen.value = true
    searchTerm.value = v
  }
}

function onTriggerFocus() {
  productComboboxOpen.value = true
}

function onOpenChange(open) {
  productComboboxOpen.value = open
  if (!open) searchTerm.value = ''
}

const triggerInputValue = computed(() =>
  productComboboxOpen.value ? searchTerm.value : displayLabel.value,
)
const locationDisplay = ref('')
const locationCoordinates = ref('')
const periodo = ref('mes')
const cotizacion = ref('')
const breakdown = ref([])
const loading = ref(false)
const error = ref(null)
const locationInputRef = ref(null)
let autocomplete = null
let placesScriptLoaded = false

function loadGooglePlacesScript() {
  if (!props.googleMapsApiKey || placesScriptLoaded) return Promise.resolve()
  return new Promise((resolve) => {
    if (window.google?.maps?.places) {
      placesScriptLoaded = true
      resolve()
      return
    }
    const script = document.createElement('script')
    script.src = `https://maps.googleapis.com/maps/api/js?key=${props.googleMapsApiKey}&libraries=places&callback=__googlePlacesReady`
    script.async = true
    script.defer = true
    window.__googlePlacesReady = () => {
      placesScriptLoaded = true
      resolve()
    }
    document.head.appendChild(script)
  })
}

function initAutocomplete() {
  if (!locationInputRef.value || !window.google?.maps?.places) return
  autocomplete = new window.google.maps.places.Autocomplete(locationInputRef.value, {
    types: ['geocode'],
    fields: ['formatted_address', 'geometry'],
  })
  autocomplete.addListener('place_changed', () => {
    const place = autocomplete.getPlace()
    if (place.geometry?.location) {
      const lat = place.geometry.location.lat()
      const lng = place.geometry.location.lng()
      locationCoordinates.value = `${lat},${lng}`
      locationDisplay.value = place.formatted_address || `${lat},${lng}`
    }
  })
}

async function generateQuote() {
  const location = locationCoordinates.value || locationDisplay.value?.trim()
  if (!productId.value || !location) {
    error.value = 'Selecciona un producto y una ubicación de envío.'
    return
  }
  error.value = null
  cotizacion.value = ''
  breakdown.value = []
  loading.value = true
  try {
    const { data } = await axios.post(route('quote.generate'), {
      product_id: Number(productId.value),
      location,
      periodo: periodo.value,
    })
    if (data.type === 'success') {
      cotizacion.value = data.cotizacion
      breakdown.value = data.breakdown ?? []
    } else {
      error.value = data.message || 'No se pudo generar la cotización.'
    }
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Error al generar la cotización.'
  } finally {
    loading.value = false
  }
}

function copyToClipboard() {
  if (!cotizacion.value) return
  navigator.clipboard.writeText(cotizacion.value)
}

function onLocationBlur() {
  if (!locationCoordinates.value && locationDisplay.value?.trim()) {
    locationCoordinates.value = locationDisplay.value.trim()
  }
}

onMounted(async () => {
  if (props.googleMapsApiKey) {
    await loadGooglePlacesScript()
    initAutocomplete()
  }
})

onUnmounted(() => {
  autocomplete = null
})
</script>

<template>
  <div class="space-y-6">
    <Card>
      <CardHeader>
        <CardTitle>Generar cotización</CardTitle>
        <CardDescription>
          Elige un producto y la ubicación de envío. La cotización replica el formato legacy.
        </CardDescription>
      </CardHeader>
      <CardContent class="space-y-4">
        <div class="grid gap-2">
          <Label for="quote-product">Producto</Label>
          <Popover v-model:open="productComboboxOpen" @update:open="onOpenChange">
            <PopoverTrigger as-child>
              <div class="relative flex w-full items-center">
                <input
                  id="quote-product"
                  type="text"
                  role="combobox"
                  autocomplete="off"
                  :aria-expanded="productComboboxOpen"
                  :value="triggerInputValue"
                  placeholder="Escribe o elige un producto"
                  class="flex h-9 w-full rounded-md border border-input bg-transparent py-2 pl-3 pr-9 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                  @input="onTriggerInput"
                  @focus="onTriggerFocus"
                />
                <Icon
                  icon="lucide:chevrons-up-down"
                  class="text-muted-foreground pointer-events-none absolute right-3 h-4 w-4 shrink-0 opacity-50"
                />
              </div>
            </PopoverTrigger>
            <PopoverContent class="w-[var(--radix-popover-trigger-width)] p-0" align="start">
              <Command
                v-model="productId"
                v-model:search-term="searchTerm"
                :filter-function="productFilter"
                class="rounded-lg border-0 shadow-none"
              >
                <CommandList>
                  <CommandEmpty>Ningún producto encontrado.</CommandEmpty>
                  <CommandGroup>
                    <CommandItem
                      v-for="p in filteredProducts"
                      :key="p.id"
                      :value="String(p.id)"
                      @select="onProductSelect(String(p.id))"
                    >
                      {{ p.name }}
                      <span v-if="p.material" class="text-muted-foreground">
                        — {{ p.material.name }}
                      </span>
                    </CommandItem>
                  </CommandGroup>
                </CommandList>
              </Command>
            </PopoverContent>
          </Popover>
          <input type="hidden" name="product" :value="productId" />
        </div>

        <div class="grid gap-2">
          <Label for="quote-location">Ubicación de envío</Label>
          <input
            id="quote-location"
            ref="locationInputRef"
            v-model="locationDisplay"
            type="text"
            placeholder="Escribe una dirección (autocompletado Google Maps)"
            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
            @blur="onLocationBlur"
          />
          <p class="text-muted-foreground text-xs">
            Si no usas el autocompletado, ingresa coordenadas (lat,lng) para el cálculo de envío.
          </p>
        </div>

        <div class="grid gap-2">
          <Label>Período de pago</Label>
          <div class="flex gap-4">
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                v-model="periodo"
                type="radio"
                value="mes"
                class="rounded-full border-input"
              />
              <span>Mensual</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                v-model="periodo"
                type="radio"
                value="semana"
                class="rounded-full border-input"
              />
              <span>Semanal</span>
            </label>
          </div>
        </div>

        <div v-if="error" class="rounded-md bg-destructive/10 text-destructive text-sm p-3">
          {{ error }}
        </div>

        <Button
          type="button"
          :disabled="loading"
          @click="generateQuote"
        >
          {{ loading ? 'Generando…' : 'Generar cotización' }}
        </Button>
      </CardContent>
    </Card>

    <Card v-if="cotizacion">
      <CardHeader>
        <div class="flex items-center justify-between">
          <CardTitle>Cotización</CardTitle>
          <Button variant="outline" size="sm" @click="copyToClipboard">
            Copiar
          </Button>
        </div>
      </CardHeader>
      <CardContent class="space-y-4">
        <div v-if="breakdown.length > 0" class="rounded-md border bg-muted/30 p-4">
          <h4 class="text-muted-foreground mb-3 text-sm font-medium">
            Desglose del precio
          </h4>
          <dl class="space-y-2">
            <div
              v-for="(item, index) in breakdown"
              :key="index"
              class="flex justify-between gap-4 text-sm"
            >
              <dt class="text-muted-foreground">
                {{ item.concepto }}
              </dt>
              <dd class="font-medium tabular-nums">
                {{ item.monto }}
              </dd>
            </div>
          </dl>
        </div>
        <pre class="whitespace-pre-wrap rounded-md bg-muted p-4 text-sm">{{ cotizacion }}</pre>
      </CardContent>
    </Card>
  </div>
</template>

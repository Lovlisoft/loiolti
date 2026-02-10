<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/Components/shadcn/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/shadcn/ui/card'
import { Input } from '@/Components/shadcn/ui/input'
import { Label } from '@/Components/shadcn/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/Components/shadcn/ui/select'
import { Textarea } from '@/Components/shadcn/ui/textarea'
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  materials: { type: Array, default: () => [] },
  paymentPlans: { type: Array, default: () => [] },
  quoteProfiles: { type: Array, default: () => [] },
})

const DEFAULT_OPTION = '__default__'

const form = useForm({
  name: '',
  price: '',
  material_id: '',
  included: '',
  trips_required: 1,
  payment_plan_id: DEFAULT_OPTION,
  quote_profile_id: DEFAULT_OPTION,
})

function submit() {
  form.transform((data) => ({
    ...data,
    payment_plan_id: data.payment_plan_id === DEFAULT_OPTION ? '' : data.payment_plan_id,
    quote_profile_id: data.quote_profile_id === DEFAULT_OPTION ? '' : data.quote_profile_id,
  })).post(route('products.store'), {
    forceFormData: true,
    onSuccess: () => form.reset(),
  })
}
</script>

<template>
  <AppLayout title="Nuevo producto">
    <template #header>
      <div class="flex items-center gap-4">
        <Link
          :href="route('products.index')"
          class="text-muted-foreground hover:text-foreground"
        >
          ← Productos
        </Link>
        <h2 class="text-xl font-semibold leading-tight">
          Nuevo producto
        </h2>
      </div>
    </template>

    <div class="py-4 max-w-2xl">
      <Card>
        <CardHeader>
          <CardTitle>Crear producto</CardTitle>
          <CardDescription>
            Completa los datos del producto. Material y mensaje son obligatorios.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form
            class="space-y-4"
            @submit.prevent="submit"
          >
            <div class="grid gap-2">
              <Label for="name">Nombre</Label>
              <Input
                id="name"
                v-model="form.name"
                type="text"
                placeholder="Ej. Lápida clásica"
                :class="{ 'border-destructive': form.errors.name }"
              />
              <p
                v-if="form.errors.name"
                class="text-destructive text-sm"
              >
                {{ form.errors.name }}
              </p>
            </div>

            <div class="grid gap-2">
              <Label for="price">Precio (MXN)</Label>
              <Input
                id="price"
                v-model="form.price"
                type="number"
                step="0.01"
                min="0"
                placeholder="0.00"
                :class="{ 'border-destructive': form.errors.price }"
              />
              <p
                v-if="form.errors.price"
                class="text-destructive text-sm"
              >
                {{ form.errors.price }}
              </p>
            </div>

            <div class="grid gap-2">
              <Label for="material_id">Material</Label>
              <Select
                v-model="form.material_id"
                :class="{ 'border-destructive': form.errors.material_id }"
              >
                <SelectTrigger id="material_id">
                  <SelectValue placeholder="Selecciona material" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="m in materials"
                    :key="m.id"
                    :value="String(m.id)"
                  >
                    {{ m.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <p
                v-if="form.errors.material_id"
                class="text-destructive text-sm"
              >
                {{ form.errors.material_id }}
              </p>
            </div>

            <div class="grid gap-2">
              <Label for="quote_profile_id">Plantilla de cotización</Label>
              <p class="text-muted-foreground text-sm">
                La plantilla que se usa para generar el mensaje de cotización de este producto. Se edita en <strong>Plantillas de cotización</strong>.
              </p>
              <Select
                v-model="form.quote_profile_id"
                :class="{ 'border-destructive': form.errors.quote_profile_id }"
              >
                <SelectTrigger id="quote_profile_id">
                  <SelectValue placeholder="Por defecto (Speech 1)" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem :value="DEFAULT_OPTION">
                    Por defecto (Speech 1)
                  </SelectItem>
                  <SelectItem
                    v-for="q in quoteProfiles"
                    :key="q.id"
                    :value="String(q.id)"
                  >
                    {{ q.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <p
                v-if="form.errors.quote_profile_id"
                class="text-destructive text-sm"
              >
                {{ form.errors.quote_profile_id }}
              </p>
            </div>

            <div class="grid gap-2">
              <Label for="included">Incluye (descripción)</Label>
              <Textarea
                id="included"
                v-model="form.included"
                rows="3"
                placeholder="Ej. Inscripción, instalación..."
                :class="{ 'border-destructive': form.errors.included }"
              />
              <p
                v-if="form.errors.included"
                class="text-destructive text-sm"
              >
                {{ form.errors.included }}
              </p>
            </div>

            <div class="grid gap-2">
              <Label for="trips_required">Viajes de instalación</Label>
              <Input
                id="trips_required"
                v-model.number="form.trips_required"
                type="number"
                min="1"
                :class="{ 'border-destructive': form.errors.trips_required }"
              />
              <p
                v-if="form.errors.trips_required"
                class="text-destructive text-sm"
              >
                {{ form.errors.trips_required }}
              </p>
            </div>

            <div class="grid gap-2">
              <Label for="payment_plan_id">Plan de pago (opcional)</Label>
              <Select v-model="form.payment_plan_id">
                <SelectTrigger id="payment_plan_id">
                  <SelectValue placeholder="Ninguno" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem :value="DEFAULT_OPTION">
                    Ninguno
                  </SelectItem>
                  <SelectItem
                    v-for="p in paymentPlans"
                    :key="p.id"
                    :value="String(p.id)"
                  >
                    {{ p.payments }} pagos
                    <template v-if="p.down_payments">
                      · enganche {{ p.down_payments }}
                    </template>
                    · ${{ Number(p.amount).toLocaleString('es-MX') }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="flex gap-2 pt-2">
              <Button
                type="submit"
                :disabled="form.processing"
              >
                {{ form.processing ? 'Guardando…' : 'Crear producto' }}
              </Button>
              <Button
                type="button"
                variant="outline"
                as-child
              >
                <Link :href="route('products.index')">
                  Cancelar
                </Link>
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>

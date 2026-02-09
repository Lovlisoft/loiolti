<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/shadcn/ui/table'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/shadcn/ui/card'
import { Badge } from '@/Components/shadcn/ui/badge'

const props = defineProps({
  products: {
    type: Array,
    default: () => [],
  },
})

function formatPrice(value) {
  if (value == null) return '—'
  return new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN',
  }).format(Number(value))
}

function planLabel(plan) {
  if (!plan) return '—'
  const parts = [`${plan.payments} pagos`]
  if (plan.down_payments) parts.push(`enganche ${plan.down_payments}`)
  parts.push(formatPrice(plan.amount))
  return parts.join(' · ')
}
</script>

<template>
  <AppLayout title="Productos">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight">
        Catálogo de productos
      </h2>
    </template>

    <div class="py-4">
      <Card>
        <CardHeader>
          <CardTitle>Productos</CardTitle>
          <CardDescription>
            Listado de productos del catálogo. Total: {{ products.length }} productos.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="rounded-md border">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Nombre</TableHead>
                  <TableHead class="text-right">
                    Precio
                  </TableHead>
                  <TableHead>Material</TableHead>
                  <TableHead class="text-center">
                    Viajes
                  </TableHead>
                  <TableHead>Plan de pago</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow
                  v-for="product in products"
                  :key="product.id"
                >
                  <TableCell class="font-medium">
                    {{ product.name }}
                  </TableCell>
                  <TableCell class="text-right">
                    {{ formatPrice(product.price) }}
                  </TableCell>
                  <TableCell>
                    <Badge variant="secondary">
                      {{ product.material?.name ?? '—' }}
                    </Badge>
                  </TableCell>
                  <TableCell class="text-center">
                    {{ product.trips_required ?? '—' }}
                  </TableCell>
                  <TableCell class="text-muted-foreground text-sm">
                    {{ planLabel(product.payment_plan) }}
                  </TableCell>
                </TableRow>
                <TableRow v-if="products.length === 0">
                  <TableCell
                    colspan="5"
                    class="text-center text-muted-foreground py-8"
                  >
                    No hay productos en el catálogo.
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>

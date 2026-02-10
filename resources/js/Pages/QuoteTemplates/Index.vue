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
import { Button } from '@/Components/shadcn/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/Components/shadcn/ui/dropdown-menu'
import { Link, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

const props = defineProps({
  templates: {
    type: Array,
    default: () => [],
  },
})

const examplePlaceholder = '{{ product.name }}'

function confirmDestroy(template) {
  if (window.confirm(`¿Eliminar la plantilla "${template.name}"?`)) {
    router.delete(route('quote-templates.destroy', template.id))
  }
}
</script>

<template>
  <AppLayout title="Plantillas de cotización">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight">
        Plantillas de cotización
      </h2>
    </template>

    <div class="py-4">
      <Card>
        <CardHeader>
          <div class="flex items-center justify-between">
            <div>
              <CardTitle>Plantillas</CardTitle>
              <CardDescription>
                Textos que se usan para generar el mensaje de cotización. Usa variables como
                <code class="rounded bg-muted px-1">{{ examplePlaceholder }}</code>.
              </CardDescription>
            </div>
            <Button as-child>
              <Link :href="route('quote-templates.create')">
                Nueva plantilla
              </Link>
            </Button>
          </div>
        </CardHeader>
        <CardContent>
          <div class="rounded-md border">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Nombre</TableHead>
                  <TableHead>Slug</TableHead>
                  <TableHead class="text-center">
                    Activa
                  </TableHead>
                  <TableHead class="text-center">
                    Orden
                  </TableHead>
                  <TableHead class="w-[100px]">
                    Acciones
                  </TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow
                  v-for="t in templates"
                  :key="t.id"
                >
                  <TableCell class="font-medium">
                    {{ t.name }}
                  </TableCell>
                  <TableCell>
                    <Badge variant="secondary">
                      {{ t.slug }}
                    </Badge>
                  </TableCell>
                  <TableCell class="text-center">
                    <Icon
                      :icon="t.is_active ? 'lucide:check-circle' : 'lucide:minus-circle'"
                      :class="t.is_active ? 'text-green-600' : 'text-muted-foreground'"
                      class="h-5 w-5 inline-block"
                    />
                  </TableCell>
                  <TableCell class="text-center">
                    {{ t.sort_order }}
                  </TableCell>
                  <TableCell>
                    <DropdownMenu>
                      <DropdownMenuTrigger as-child>
                        <Button variant="ghost" size="icon">
                          <Icon icon="lucide:more-horizontal" class="h-4 w-4" />
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent align="end">
                        <DropdownMenuItem :as="Link" :href="route('quote-templates.edit', t.id)">
                          <Icon icon="lucide:pencil" class="mr-2 h-4 w-4" />
                          Editar
                        </DropdownMenuItem>
                        <DropdownMenuItem
                          class="text-destructive focus:text-destructive"
                          @click="confirmDestroy(t)"
                        >
                          <Icon icon="lucide:trash-2" class="mr-2 h-4 w-4" />
                          Eliminar
                        </DropdownMenuItem>
                      </DropdownMenuContent>
                    </DropdownMenu>
                  </TableCell>
                </TableRow>
                <TableRow v-if="templates.length === 0">
                  <TableCell colspan="5" class="text-muted-foreground text-center py-8">
                    No hay plantillas. Crea una para definir el texto de la cotización.
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

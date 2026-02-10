<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import QuoteTemplateEditor from '@/Components/QuoteTemplateEditor.vue'
import { Button } from '@/Components/shadcn/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/shadcn/ui/card'
import { Input } from '@/Components/shadcn/ui/input'
import { Label } from '@/Components/shadcn/ui/label'
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  template: {
    type: Object,
    required: true,
  },
  variables: {
    type: Array,
    default: () => [],
  },
})

const form = useForm({
  name: props.template.name ?? '',
  slug: props.template.slug ?? '',
  content: props.template.content ?? '',
  is_active: props.template.is_active ?? true,
  sort_order: props.template.sort_order ?? 0,
})

function submit() {
  form.put(route('quote-templates.update', props.template.id))
}
</script>

<template>
  <AppLayout :title="`Editar: ${template.name}`">
    <template #header>
      <div class="flex items-center gap-4">
        <Link
          :href="route('quote-templates.index')"
          class="text-muted-foreground hover:text-foreground"
        >
          ← Plantillas
        </Link>
        <h2 class="text-xl font-semibold leading-tight">
          Editar plantilla
        </h2>
      </div>
    </template>

    <div class="py-4 max-w-4xl space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>Contenido del texto de cotización</CardTitle>
          <CardDescription>
            Edita el mensaje. Usa «Insertar variable» para añadir pills; pasa el cursor sobre una pill para ver su descripción.
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
                :class="{ 'border-destructive': form.errors.name }"
              />
              <p v-if="form.errors.name" class="text-destructive text-sm">
                {{ form.errors.name }}
              </p>
            </div>

            <div class="grid gap-2">
              <Label for="slug">Identificador (slug)</Label>
              <Input
                id="slug"
                v-model="form.slug"
                type="text"
                :class="{ 'border-destructive': form.errors.slug }"
              />
              <p v-if="form.errors.slug" class="text-destructive text-sm">
                {{ form.errors.slug }}
              </p>
            </div>

            <div class="grid gap-2">
              <Label for="content">Texto de la plantilla</Label>
              <QuoteTemplateEditor
                v-model="form.content"
                :variables="variables"
                placeholder="Escribe el mensaje e inserta variables donde lo necesites…"
              />
              <p v-if="form.errors.content" class="text-destructive text-sm">
                {{ form.errors.content }}
              </p>
            </div>

            <div class="flex flex-wrap gap-6">
              <div class="flex items-center gap-2">
                <input
                  id="is_active"
                  v-model="form.is_active"
                  type="checkbox"
                  class="rounded border-input"
                />
                <Label for="is_active">Plantilla activa</Label>
              </div>
              <div class="grid gap-1">
                <Label for="sort_order">Orden</Label>
                <Input
                  id="sort_order"
                  v-model.number="form.sort_order"
                  type="number"
                  min="0"
                  class="w-24"
                />
              </div>
            </div>

            <div class="flex gap-2 pt-2">
              <Button type="submit" :disabled="form.processing">
                Guardar cambios
              </Button>
              <Button variant="outline" as-child>
                <Link :href="route('quote-templates.index')">
                  Cancelar
                </Link>
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Referencia de variables</CardTitle>
          <CardDescription>
            Descripción y origen de cada variable. En el editor, pasa el cursor sobre una pill para ver estos detalles.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="overflow-x-auto rounded-md border">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b bg-muted/50">
                  <th class="px-4 py-2 text-left font-medium">Variable</th>
                  <th class="px-4 py-2 text-left font-medium">Origen</th>
                  <th class="px-4 py-2 text-left font-medium">Descripción / uso</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="v in variables"
                  :key="v.key"
                  class="border-b last:border-0"
                >
                  <td class="px-4 py-2 font-medium">{{ v.name }}</td>
                  <td class="text-muted-foreground px-4 py-2">{{ v.source }}</td>
                  <td class="text-muted-foreground px-4 py-2">{{ v.help_text || v.description || '—' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>

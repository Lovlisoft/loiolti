<script setup>
import {
  SidebarContent,
  SidebarGroup,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/Components/shadcn/ui/sidebar'
import { Icon } from '@iconify/vue'
import { Link } from '@inertiajs/vue3'
import { inject } from 'vue'

const route = inject('route')

const navigationConfig = [
  {
    label: 'Navegación',
    items: [
      { name: 'Cotizar', icon: 'lucide:calculator', route: 'dashboard' },
      { name: 'Productos', icon: 'lucide:package', route: 'products.index' },
      { name: 'Plantillas de cotización', icon: 'lucide:file-text', route: 'quote-templates.index' },
    ],
  },
]

function renderLink(item) {
  if (item.external) {
    return {
      is: 'a',
      href: item.href || route(item.route),
      target: '_blank',
    }
  }
  return {
    is: Link,
    href: route(item.route),
  }
}
</script>

<template>
  <SidebarContent>
    <SidebarGroup v-for="(group, index) in navigationConfig" :key="index" :class="group.class">
      <SidebarGroupLabel v-if="group.label">
        {{ group.label }}
      </SidebarGroupLabel>
      <SidebarMenu>
        <SidebarMenuItem
          v-for="item in group.items"
          :key="item.name"
          :class="{ 'font-semibold text-primary bg-secondary rounded': !item.external && route().current(item.route) }"
        >
          <SidebarMenuButton as-child>
            <component v-bind="renderLink(item)" :is="item.external ? 'a' : Link" prefetch>
              <Icon :icon="item.icon" />
              {{ item.name }}
            </component>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarGroup>
  </SidebarContent>
</template>

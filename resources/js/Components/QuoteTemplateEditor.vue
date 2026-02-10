<script setup>
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/Components/shadcn/ui/dropdown-menu'
import { Button } from '@/Components/shadcn/ui/button'
import { Icon } from '@iconify/vue'
import { computed, nextTick, onMounted, ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  variables: {
    type: Array,
    default: () => [],
  },
  placeholder: {
    type: String,
    default: 'Escribe el mensaje e inserta variables donde lo necesites…',
  },
})

const emit = defineEmits(['update:modelValue'])

const editorRef = ref(null)
const isInternalChange = ref(false)
const variableByKey = computed(() => {
  const map = {}
  for (const v of props.variables) {
    map[v.key] = v
  }
  return map
})

/** Default hex by source when variable has no color. */
const DEFAULT_COLORS = {
  product: '#0ea5e9',
  calculated: '#8b5cf6',
  system: '#64748b',
}

function getPillColor(v) {
  if (v?.color && /^#[0-9A-Fa-f]{6}$/.test(v.color)) return v.color
  return DEFAULT_COLORS[v?.source] ?? '#64748b'
}

/** Dark text color for pill content so contrast is good on the tinted background. */
const PILL_TEXT_COLOR = '#0f172a'

function getPillStyle(v) {
  const hex = getPillColor(v)
  return `background-color: ${hex}26; border-left: 3px solid ${hex}; color: ${PILL_TEXT_COLOR};`
}

/**
 * Parse template string into segments: [{ type: 'text'|'variable', value?, key? }]
 */
function parseContent(content) {
  if (!content || typeof content !== 'string') return [{ type: 'text', value: '' }]
  const segments = []
  const re = /\{\{([^}]+)\}\}/g
  let lastIndex = 0
  let m
  while ((m = re.exec(content)) !== null) {
    if (m.index > lastIndex) {
      segments.push({ type: 'text', value: content.slice(lastIndex, m.index) })
    }
    segments.push({ type: 'variable', key: m[1].trim() })
    lastIndex = re.lastIndex
  }
  if (lastIndex < content.length) {
    segments.push({ type: 'text', value: content.slice(lastIndex) })
  }
  if (segments.length === 0) {
    segments.push({ type: 'text', value: '' })
  }
  return segments
}

/**
 * Serialize segments back to template string
 */
function serializeSegments(segments) {
  return segments.map(s => (s.type === 'text' ? s.value : `{{${s.key}}}`)).join('')
}

const maxExampleLength = 18

function truncateExample(str) {
  const s = String(str ?? '')
  if (s.length <= maxExampleLength) return s
  return s.slice(0, maxExampleLength) + '…'
}

/**
 * Build HTML for contenteditable from segments (escape text, pills for variables).
 * Pill shows example value + info icon; full description in title (tooltip).
 */
function buildEditorHtml(segments) {
  return segments.map((seg) => {
    if (seg.type === 'text') {
      return escapeHtml(seg.value)
    }
    const v = variableByKey.value[seg.key]
    const example = v?.example_value != null ? String(v.example_value) : '…'
    const shortExample = truncateExample(example)
    const title = v?.help_text != null ? v.help_text : (v?.name ? `${v.name}. ${v.source || ''}` : seg.key)
    const style = getPillStyle(v)
    return `<span contenteditable="false" data-var="${escapeAttr(seg.key)}" class="variable-pill" style="${escapeAttr(style)}" title="${escapeAttr(title)}" role="text"><span class="pill-example">${escapeHtml(shortExample)}</span><span class="pill-info-icon" aria-hidden="true">ⓘ</span></span>`
  }).join('')
}

function escapeHtml(str) {
  const div = document.createElement('div')
  div.textContent = str
  return div.innerHTML
}

function escapeAttr(str) {
  const div = document.createElement('div')
  div.textContent = str ?? ''
  return div.innerHTML.replace(/"/g, '&quot;')
}

/**
 * Read contenteditable DOM and serialize to template string
 */
function getSerializedContent() {
  const el = editorRef.value
  if (!el) return ''
  let out = ''
  for (const node of el.childNodes) {
    if (node.nodeType === Node.TEXT_NODE) {
      out += node.textContent
    } else if (node.nodeType === Node.ELEMENT_NODE && node.dataset?.var != null) {
      out += `{{${node.dataset.var}}}`
    } else if (node.nodeType === Node.ELEMENT_NODE) {
      out += node.textContent
    }
  }
  return out
}

function syncFromProp() {
  const el = editorRef.value
  if (!el) return
  const segments = parseContent(props.modelValue)
  el.innerHTML = buildEditorHtml(segments)
}

function onInput() {
  if (isInternalChange.value) return
  const value = getSerializedContent()
  isInternalChange.value = true
  emit('update:modelValue', value)
  nextTick(() => { isInternalChange.value = false })
}

function insertVariable(key) {
  const el = editorRef.value
  if (!el) return
  el.focus()
  const sel = window.getSelection()
  const range = sel?.getRangeAt(0)
  if (!range) return
  const v = variableByKey.value[key]
  const example = v?.example_value != null ? String(v.example_value) : '…'
  const shortExample = truncateExample(example)
  const title = v?.help_text != null ? v.help_text : (v?.name ? `${v.name}. ${v.source || ''}` : key)
  const span = document.createElement('span')
  span.contentEditable = 'false'
  span.dataset.var = key
  span.className = 'variable-pill'
  span.setAttribute('style', getPillStyle(v))
  span.setAttribute('title', title)
  span.setAttribute('role', 'text')
  const exampleSpan = document.createElement('span')
  exampleSpan.className = 'pill-example'
  exampleSpan.textContent = shortExample
  const iconSpan = document.createElement('span')
  iconSpan.className = 'pill-info-icon'
  iconSpan.setAttribute('aria-hidden', 'true')
  iconSpan.textContent = 'ⓘ'
  span.appendChild(exampleSpan)
  span.appendChild(iconSpan)
  range.insertNode(span)
  range.setStartAfter(span)
  range.collapse(true)
  sel.removeAllRanges()
  sel.addRange(range)
  onInput()
}

onMounted(() => {
  nextTick(syncFromProp)
})

watch(() => props.modelValue, (newVal) => {
  if (isInternalChange.value) return
  const current = editorRef.value ? getSerializedContent() : ''
  if (current !== newVal) {
    syncFromProp()
  }
})

function onPaste(e) {
  e.preventDefault()
  const text = e.clipboardData?.getData('text/plain') ?? ''
  document.execCommand('insertText', false, text)
  nextTick(onInput)
}
</script>

<template>
  <div class="space-y-2">
    <div class="flex flex-wrap items-center gap-2">
      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <Button type="button" variant="outline" size="sm">
            <Icon icon="lucide:plus-circle" class="mr-1.5 h-4 w-4" />
            Insertar variable
          </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="start" class="max-h-[60vh] overflow-y-auto">
          <DropdownMenuItem
            v-for="v in variables"
            :key="v.key"
            @click="insertVariable(v.key)"
          >
            <span
              class="mr-2 h-2 w-2 shrink-0 rounded-full"
              :style="{ backgroundColor: getPillColor(v) }"
              aria-hidden="true"
            />
            <span class="font-medium">{{ v.name }}</span>
            <span v-if="v.example_value" class="text-muted-foreground ml-2 text-xs">
              Ej. {{ truncateExample(v.example_value) }}
            </span>
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>
    <div
      ref="editorRef"
      role="textbox"
      contenteditable="true"
      class="editor-content min-h-[280px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
      :data-placeholder="placeholder"
      @input="onInput"
      @paste="onPaste"
    />
  </div>
</template>

<style scoped>
.editor-content {
  line-height: 2.15;
}

:deep(.editor-content .variable-pill) {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.25rem 0.5rem;
  margin: 0.125rem 0.125rem;
  border-radius: 9999px;
  font-size: 0.8125rem;
  white-space: nowrap;
  cursor: default;
  vertical-align: middle;
  line-height: 1.4;
}

:deep(.editor-content .variable-pill .pill-example) {
  font-weight: 500;
}

:deep(.editor-content .variable-pill .pill-info-icon) {
  font-size: 0.75rem;
  opacity: 0.85;
  line-height: 1;
}

.editor-content:empty::before {
  content: attr(data-placeholder);
  color: hsl(var(--muted-foreground));
}
</style>

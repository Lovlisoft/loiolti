# Relación: Producto → Plantilla de cotización (WYSIWYG)

Este documento explica cómo el **producto** se conecta con el **string de la plantilla** que se edita en el WYSIWYG (Plantillas de cotización).

## Un solo campo en el formulario

En el formulario de producto (Crear / Editar) hay **un solo campo** que define el texto de la cotización:

- **Plantilla de cotización**: dropdown con opciones "Por defecto (Speech 1)", "Speech 1 - Financiamiento 12 pagos", "Speech 2 - Descuento 10%", "Speech 3 - Promoción primer pago". La opción elegida es la plantilla que se usa para generar el mensaje de cotización de ese producto.

No hay un segundo campo "Mensaje": la lógica se simplificó a **producto → una plantilla de cotización**.

## Cadena de relaciones

```
Producto (quote_profile_id)        ← único selector en el formulario
    ↓
QuoteProfile (quote_template_id)
    ↓
QuoteTemplate (content)            ← lo que editas en el WYSIWYG (quote-templates/edit)
```

- **Product** tiene `quote_profile_id` → apunta a un **QuoteProfile** (Speech 1, 2, 3).
- **QuoteProfile** tiene `quote_template_id` → apunta a un **QuoteTemplate**.
- **QuoteTemplate** tiene `content`: ese es el texto que editas en "Plantillas de cotización" con variables `{{product.name}}`, `{{quote.abono}}`, etc.

El campo `message_id` del producto se mantiene en la base de datos por compatibilidad y se rellena automáticamente según la plantilla elegida (Speech 1 → 1, Speech 2 → 2, Speech 3 → 3). No se muestra en el formulario.

## En el código

En `QuoteGeneratorService::generateWithBreakdown()`:

1. Se toma el perfil del producto: `$profile = $product->quoteProfile` (o perfil por defecto / speech-1 si es null).
2. Se toma el contenido de la plantilla: `$template = $profile?->quoteTemplate?->content`.
3. Se reemplazan los placeholders con las variables y se devuelve ese string como cotización.

Por tanto: **el "Perfil de cotización" que eliges al crear/editar un producto es exactamente el que determina qué plantilla (la que editas en el WYSIWYG) se usa para generar el texto de la cotización de ese producto.**

## Slugs por defecto

Los seeders crean:

| Slug (QuoteTemplate / QuoteProfile) | Nombre típico del perfil |
|-----------------------------------|---------------------------|
| speech-1 | Speech 1 - Financiamiento 12 pagos |
| speech-2 | Speech 2 - Descuento 10% |
| speech-3 | Speech 3 - Promoción primer pago |

Cada uno tiene su propia **QuoteTemplate** con slug igual (speech-1, speech-2, speech-3). Al editar en "Plantillas de cotización" estás editando el `content` de esa QuoteTemplate; los productos que tengan asignado el perfil con ese `quote_template_id` usarán ese texto al cotizar.

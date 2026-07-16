# CIAC Design System v1

## Propósito

Unificar la experiencia visual de CIAC sin agregar dependencias de compilación ni alterar la lógica de negocio.

## Componentes base

### Botones

```html
<button class="ciac-btn ciac-btn--primary">Guardar</button>
<button class="ciac-btn ciac-btn--secondary">Asignar</button>
<button class="ciac-btn ciac-btn--outline">Cancelar</button>
<button class="ciac-btn ciac-btn--ghost">Ver detalle</button>
<button class="ciac-btn ciac-btn--danger">Eliminar</button>
```

Variantes auxiliares:

```html
<button class="ciac-btn ciac-btn--primary ciac-btn--sm">Acción pequeña</button>
<button class="ciac-btn ciac-btn--primary ciac-btn--block">Acción completa</button>
```

### Tarjetas

```html
<section class="ciac-card">
    <header class="ciac-card__header">
        <h2 class="ciac-card__title">Título</h2>
        <p class="ciac-card__subtitle">Descripción</p>
    </header>
    <div class="ciac-card__body">Contenido</div>
    <footer class="ciac-card__footer">Acciones</footer>
</section>
```

### Badges

```html
<span class="ciac-badge ciac-badge--neutral">Pendiente</span>
<span class="ciac-badge ciac-badge--primary">CIAC</span>
<span class="ciac-badge ciac-badge--success">Completado</span>
<span class="ciac-badge ciac-badge--warning">Por vencer</span>
<span class="ciac-badge ciac-badge--danger">Vencido</span>
<span class="ciac-badge ciac-badge--info">En proceso</span>
```

### Formularios

```html
<label class="ciac-label" for="name">Nombre</label>
<input class="ciac-field" id="name" name="name">
<select class="ciac-select" name="status"></select>
<textarea class="ciac-textarea" name="body"></textarea>
<p class="ciac-help">Texto de apoyo.</p>
<p class="ciac-error">Mensaje de validación.</p>
```

### Estados vacíos

```html
<div class="ciac-empty-state">
    <div class="ciac-empty-state__icon">📭</div>
    <h3 class="ciac-empty-state__title">Sin resultados</h3>
    <p class="ciac-empty-state__description">Todavía no existen registros para mostrar.</p>
</div>
```

### Tablas

```html
<div class="ciac-table-shell">
    <table class="ciac-table">...</table>
</div>
```

## Reglas

1. Usar clases semánticas de CIAC para componentes compartidos.
2. Tailwind puede seguir utilizándose para layout, grid y ajustes puntuales.
3. No duplicar variantes visuales si ya existe una clase equivalente.
4. Mantener transiciones en 150 ms y respetar `prefers-reduced-motion`.
5. No introducir nuevos colores principales sin actualizar las variables globales.
6. Las acciones destructivas deben usar `ciac-btn--danger` y confirmación SweetAlert.
7. Los formularios deben conservar foco visible y mensajes de error comprensibles.

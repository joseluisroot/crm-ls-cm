# CIAC Component Library v1

Biblioteca de vistas pequeñas y reutilizables para reducir HTML duplicado sin introducir dependencias ni lógica de negocio en la capa visual.

## Componentes

### `page_header.php`

Parámetros:

- `title` requerido.
- `eyebrow` opcional.
- `description` opcional.
- `actionsHtml` opcional y previamente construido por la vista llamadora.
- `class` opcional.

### `kpi_card.php`

Parámetros:

- `label` requerido.
- `value` requerido.
- `tone`: `blue`, `violet`, `amber`, `pink`, `green`, `red` o `slate`.
- `help` opcional.
- `class` opcional.

### `empty_state.php`

Parámetros:

- `title` requerido.
- `description` opcional.
- `icon` opcional.
- `actionHtml` opcional.
- `class` opcional.

### `section_header.php`

Parámetros:

- `title` requerido.
- `subtitle` opcional.
- `meta` opcional.
- `actionsHtml` opcional.

## Reglas

1. Los componentes no consultan base de datos, sesión, request ni servicios.
2. La vista llamadora conserva permisos, URLs y decisiones de negocio.
3. Todo texto simple se escapa dentro del componente.
4. `actionsHtml` se utiliza únicamente con HTML generado por vistas internas y nunca con entrada directa del usuario.
5. No crear un componente hasta que exista un patrón repetido y estable.

## Ejemplo

```php
<?= view('Modules\\Shared\\Views\\components\\kpi_card', [
    'label' => 'Casos abiertos',
    'value' => $openCases,
    'tone' => 'amber',
]) ?>
```

La adopción será incremental, comenzando por `/admin/cases` y el Dashboard PRO.

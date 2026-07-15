# CIAC Core Widgets

Esta capa permite ensamblar experiencias sin trasladar la lógica de dominio a las vistas.

## Principios

- Cada módulo sigue siendo dueño de sus datos.
- Un widget consulta servicios existentes y construye un `WidgetResult`.
- El renderer únicamente presenta la vista indicada por el resultado.
- El registry impide claves duplicadas.
- El context transporta identificadores y datos de la experiencia actual.

## Ejemplo

```php
$registry = new WidgetRegistry([
    new CitizenWidget(),
    new TimelineWidget(),
]);

$manager = new WidgetManager($registry, new WidgetRenderer());
$panels = $manager->render(
    ['citizen', 'timeline'],
    new WidgetContext(
        viewerUserId: (int) session()->get('admin_user_id'),
        workItemId: $workItemId,
        citizenId: $citizenId,
    ),
);
```

El siguiente incremento incorporará el primer widget real del Citizen Care Workspace.

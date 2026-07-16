# CIAC UI/UX Radar

## Propósito

Mantener organizadas las mejoras visuales y operativas pendientes antes de CIAC v1.0, evitando cambios aislados o inconsistentes entre módulos.

## Prioridad inmediata

### 1. `/admin/citizens`

Prioridad: muy alta.

Pendientes:

- paginación;
- DataTable con estilo CIAC;
- búsqueda instantánea;
- selector de cantidad de registros;
- ordenamiento;
- responsive;
- indicador `Mostrando X–Y de Z`;
- estado vacío consistente;
- filtros por nombre, canal, fecha y actividad;
- acciones de fila ordenadas.

### 2. `/admin/conversations`

Prioridad: muy alta.

Pendientes:

- paginación;
- DataTable con estilo CIAC;
- búsqueda por ciudadano y contenido;
- filtros por estado, canal, responsable y fecha;
- último mensaje y última actividad visibles;
- cantidad de mensajes;
- responsive;
- estado vacío consistente.

### 3. `/admin/my-cases`

Prioridad: muy alta.

Pendientes:

- convertir la vista en bandeja operativa personal;
- paginación y DataTable CIAC;
- filtros por estado, prioridad, SLA, ciudadano y fecha;
- orden por urgencia;
- indicadores visuales de SLA;
- responsable y última actividad;
- estados vacíos y responsive.

### 4. `/admin/notifications`

Prioridad: alta.

Pendientes:

- revisar paginación actual;
- DataTable cuando el volumen lo requiera;
- filtros por leído/no leído, tipo y fecha;
- marcar como leída con feedback visual;
- badges consistentes;
- estado vacío;
- acciones con SweetAlert Global v2.

## Segundo bloque

- `/admin/operations`;
- `/admin/publications`;
- `/admin/engagement`;
- `/admin/engagement/participants`;
- `/admin/cases`;
- `/admin/users`.

## Criterios comunes

Cada listado administrativo deberá revisar:

1. Encabezado y descripción.
2. Filtros útiles y persistencia.
3. Paginación real, evitando cargar volúmenes ilimitados.
4. DataTables solo donde aporte búsqueda, orden y navegación.
5. Diseño responsive.
6. Estados vacíos explicativos.
7. Badges del CIAC Design System.
8. Acciones protegidas con SweetAlert Global.
9. Prevención de doble envío.
10. Consistencia de botones, tablas y formularios.

## Orden de ejecución

1. SweetAlert Global v2.
2. Citizens.
3. Conversations.
4. My Cases.
5. Notifications.
6. Operations, Publications y Engagement.
7. Revisión responsive transversal.
8. Pulido final CIAC v1.0.

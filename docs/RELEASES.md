# Releases

## v1.0 Foundation — 2026-07

Base funcional y arquitectónica de CIAC:

- recepción y gestión de Messenger;
- ciudadanos, conversaciones y casos;
- asignaciones y notificaciones;
- analytics inicial;
- Dynamic Workflow Engine y Validator;
- Core Event Engine;
- Workflow Runtime Inspector backend y UI;
- Public Engagement Engine;
- recepción real de comentarios y reacciones de Facebook;
- Engagement Center y ranking de participación;
- Documentation Center.

## v1.1 Citizen Operations — en progreso

Objetivo de negocio: convertir interacciones relevantes en trabajo operativo organizado y trazable.

### Domain Foundation
- RFC-001 Citizen Operations Domain;
- ADR-005 Unified Work Queue aceptado;
- tabla `work_items` y catálogos extensibles;
- entidad y Value Objects de dominio;
- repositorio desacoplado de CodeIgniter Models;
- servicio idempotente de creación;
- eventos `operations.work_item.created`, `assigned` y `case_created`;
- pruebas unitarias iniciales.

### Próximo incremento
- importar comentarios pendientes como Work Items;
- Operations Queue;
- estados, prioridad, asignación y SLA;
- acciones sobre comentarios;
- conversión a caso;
- Citizen Card;
- timeline ciudadano.

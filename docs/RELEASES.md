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

### Unified Operations Queue
- adaptador idempotente para comentarios de Facebook;
- sincronización manual de comentarios pendientes;
- primera cola visual de Work Items;
- filtros por estado y prioridad;
- contexto de comentario y publicación;
- acceso a permalink cuando Meta lo proporciona;
- layout reorganizado por capacidades de producto;
- navegación visible a Operations, Engagement, Citizen Participation y Runtime Inspector.

### Operations Console
- detalle operacional por Work Item;
- asignación de responsable;
- cambio controlado de estado y prioridad;
- registro de primera respuesta;
- timestamps de apertura, resolución y cierre;
- timeline auditable desde `system_events`;
- relación visible con ciudadano y caso cuando existe;
- acceso desde la Unified Operations Queue.

### Próximo incremento
- creación automática del Work Item durante la ingestión;
- SLA y alertas de vencimiento;
- respuesta real a comentarios;
- vinculación de identidad social;
- conversión a caso;
- Citizen Card;
- timeline ciudadano.

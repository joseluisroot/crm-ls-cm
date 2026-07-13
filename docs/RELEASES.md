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

## v1.2 Citizen Domain — en progreso

### Citizen Identity Foundation
- tabla `citizen_social_identities` con unicidad por canal y external ID;
- Value Objects `IdentityChannel`, `ActorType` e `IdentityConfidence`;
- entidad `SocialIdentity`;
- repositorio de identidades desacoplado;
- DTO `IdentityRequest`;
- servicio `CitizenResolverService`;
- creación y resolución exacta de identidad;
- eventos `citizen.identity.created` y `citizen.identity.resolved`;
- pruebas unitarias de entidad y Value Objects.

### Identity to Operations Integration
- el adaptador de comentarios resuelve al autor antes de crear trabajo;
- crea un Citizen y su identidad cuando no existen;
- crea Work Items con `citizen_id` desde el primer momento;
- enlaza idempotentemente Work Items existentes que aún no tenían ciudadano;
- conserva metadata del origen en identidad y Work Item;
- publica `operations.work_item.citizen_linked`.

### Próximo incremento
- construir Citizen Timeline;
- incorporar Citizen Card en Operations;
- preparar el puente Citizen → Case.

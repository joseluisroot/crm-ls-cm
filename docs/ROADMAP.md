# Roadmap de CIAC

## v1.0 Foundation — completado

- Interaction Engine
- Context Engine
- Case Lifecycle Engine
- Notification Engine
- Assignment Engine
- Analytics Engine
- Workflow Engine
- Workflow Validator
- Core Event Engine
- Workflow Runtime Inspector
- Public Engagement Engine
- Engagement Center
- Documentation Center

## v1.1 Citizen Operations — en progreso

### Completado
- RFC-001 Citizen Operations Domain
- ADR-005 Unified Work Queue
- modelo de Work Items omnicanal
- catálogos de estado, prioridad, canal y tipo de origen
- creación idempotente por origen
- eventos `operations.work_item.*`
- repositorio y servicio de aplicación
- adaptador Facebook Comment → Work Item
- primera Unified Operations Queue
- filtros por estado y prioridad
- navegación integrada en CIAC Studio
- Operations Console por Work Item
- asignación de operador
- cambio de estado y prioridad
- registro de primera respuesta
- timeline operativo basado en Core Event Engine
- enlace idempotente Work Item → Citizen
- Citizen Card embebida en Operations

### Siguientes incrementos
- creación automática de Work Items durante la ingestión del webhook
- SLA calculado y alertas de vencimiento
- respuesta real a comentarios desde CIAC
- conversión de interacción a caso

## v1.2 Citizen Domain — en progreso

### Citizen Identity Foundation
- tabla `citizen_social_identities`
- identidad única por canal y external ID
- Value Objects de canal, tipo de actor y confianza
- entidad `SocialIdentity`
- repositorio desacoplado
- servicio `CitizenResolverService`
- eventos `citizen.identity.created` y `citizen.identity.resolved`

### Identity → Operations Integration
- resolución exacta del autor antes de crear el Work Item
- creación automática del Citizen cuando la identidad no existe
- asociación automática de `work_items.citizen_id`
- actualización idempotente de Work Items importados previamente
- evento `operations.work_item.citizen_linked`

### Citizen Timeline Foundation
- DTOs estables para Timeline
- repositorio agregador sobre Work Items y Core Event Engine
- métricas de Work Items, casos e identidades
- servicio `CitizenTimelineQueryService`
- Timeline cronológico en `/admin/citizens/{id}`
- perfil ciudadano renovado con identidades y relaciones actuales

### Citizen Card
- DTO y contrato reutilizable
- repositorio agregado de Citizen, Work Items, casos, conversaciones e identidades
- componente visual reutilizable
- integración en `/admin/operations/{id}`
- enlace directo al perfil ciudadano

### Siguientes incrementos
- incorporar comentarios, Messenger y reacciones al Timeline
- Citizen 360

## v1.2.x Publication Domain — en progreso

### Publication Profile
- listado `/admin/publications`
- perfil `/admin/publications/{id}`
- métricas de comentarios, reacciones, participantes, Work Items y casos
- comentarios vinculados con Operations
- desglose de reacciones
- ranking inicial de participantes
- acceso directo al permalink de Facebook

### Siguientes incrementos
- Publication Analytics
- Conversation Threads
- Citizen Card en participantes y comentarios
- clasificación avanzada de actores
- comportamiento de comentarios ocultos en Meta
- merge y revisión manual de identidades
- enlace y sincronización de contenido

## v1.3 AI Assisted Operations

- clasificación
- prioridad sugerida
- sentimiento y temas
- respuestas sugeridas

## v2.0 Campaign Intelligence

- Community Pulse
- impacto de campañas
- tendencias y alertas
- recomendaciones estratégicas responsables

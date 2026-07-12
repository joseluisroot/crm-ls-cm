# CIAC Architecture Book

## 1. Visión arquitectónica

CIAC sigue una arquitectura modular, orientada a eventos y organizada alrededor de capacidades de negocio.

```text
Channels
  ├─ Messenger
  ├─ Facebook Engagement
  ├─ Instagram
  ├─ WhatsApp
  └─ Future Channels
        ↓
Interaction / Engagement
        ↓
Citizen Operations
        ↓
Workflow / Assignment / Notification / Cases
        ↓
Core Event Engine
        ↓
Runtime Inspector / Analytics / Citizen Timeline
```

## 2. Capas

### Core
- Event Engine
- configuración y servicios compartidos
- observabilidad

### Business
- Citizens
- Interactions
- Engagement
- Cases
- Assignments
- Notifications
- Workflows
- Operations

### Studio
- Dashboard
- Engagement Center
- Runtime Inspector
- Citizen Operations Center
- Analytics

### Infrastructure
- Meta Graph API
- Webhooks
- futuros proveedores de mensajería e IA

## 3. Principios

- el ciudadano es el centro del dominio;
- el canal es un adaptador de entrada y salida;
- los módulos se comunican mediante eventos cuando sea apropiado;
- no se duplican motores de casos, asignaciones, notificaciones o workflows;
- toda operación relevante debe ser auditable;
- las nuevas capacidades deben integrarse con la arquitectura existente;
- se captura primero el dato externo y se enriquece después sin destruir el payload original.

## 4. Flujo actual de engagement

```text
Facebook Page
  → Page Webhook (`entry.changes`)
  → PublicEngagementProcessor
  → social_posts / social_comments / social_reactions
  → Core Event Engine
  → Engagement Center / Analytics
```

## 5. Citizen Operations Domain

Citizen Operations introduce el **Work Item** como unidad operativa agnóstica al canal.

```text
External interaction
  → origin_type + origin_id
  → Work Item
  → Assignment / Case / Workflow
  → operations.work_item.*
  → Runtime / Timeline / Analytics
```

### Límites
- Operations no conoce la API de Facebook ni payloads específicos del canal.
- El módulo de captura conserva el evento original.
- El Work Item conserva únicamente referencias, estado operativo y metadata necesaria.
- Case Lifecycle, Assignment y Workflow continúan siendo las fuentes de verdad de sus respectivos procesos.

### Componentes iniciales
- entidad `WorkItem`;
- Value Objects de estado y prioridad;
- `WorkItemRepositoryInterface`;
- `DatabaseWorkItemRepository`;
- `CitizenOperationsService`;
- `WorkItemEventPublisher`;
- catálogos extensibles;
- restricción de idempotencia por tipo e identificador de origen.

## 6. Flujo documental

Las decisiones de dominio relevantes requieren RFC y ADR. El código, roadmap, Architecture Book y notas de release deben evolucionar en el mismo Pull Request.

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
- las nuevas capacidades deben integrarse con la arquitectura existente.

## 4. Flujo actual de engagement

```text
Facebook Page
  → Page Webhook (`entry.changes`)
  → PublicEngagementProcessor
  → social_posts / social_comments / social_reactions
  → Core Event Engine
  → Engagement Center / Analytics
```

## 5. Próxima evolución

Citizen Operations introducirá Work Items como unidad operativa omnicanal. Un Work Item referenciará su origen mediante tipo e identificador, sin acoplarse exclusivamente a comentarios de Facebook.
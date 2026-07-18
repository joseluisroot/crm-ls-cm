# CIAC Architecture Baseline

## 1. Architectural style

CIAC is a modular CodeIgniter 4 application organized around functional modules. The repository combines MVC controllers and views with application services, query services, repositories, publishers and an internal event engine.

The current structure is closer to a pragmatic modular monolith than to a traditional CRUD application. This is appropriate for the present deployment model and avoids the operational complexity of distributed services.

## 2. Registered functional areas

The route map exposes the following areas:

- Auth and Authorization
- Dashboard
- Operations and Response
- Citizens and Conversations
- Cases
- Messenger
- Integration Replay
- Notifications
- Analytics and Engagement
- Publications
- Workflow Builder, Runtime Inspector and Simulator
- External webhooks
- Protected system actions

## 3. Main layers observed

### Presentation

Controllers, server-rendered views and administrative forms. Administrative routes are protected by `adminAuth`; sensitive actions additionally use permission filters.

### Application

Query and orchestration services such as:

- `IntegrationEventCaptureService`
- `IntegrationEventQueryService`
- `IntegrationEventReplayService`
- `CitizenOperationsService`
- `OperationsQueueQueryService`
- `OperationsDetailQueryService`
- `CitizenResolverService`
- `CitizenCardQueryService`
- `CitizenTimelineQueryService`
- `PublicationProfileQueryService`
- `PublicationAnalyticsService`
- `ResolvePublicationParticipantsService`

### Infrastructure

Database repositories, models and external integration adapters. Examples include `DatabaseIntegrationEventRepository`, `DatabaseWorkItemRepository`, citizen repositories and the Facebook comment adapter.

### Cross-cutting services

`Config\Services` acts as the composition root for shared services. The internal event engine provides registry, dispatcher, publishers and subscribers. Workflow execution is wrapped by an instrumented runtime service.

## 4. Principal flows

### Messenger ingestion

1. Meta invokes `webhooks/messenger`.
2. The event is captured as an integration envelope.
3. `MetaIntegrationEventProcessor` processes the payload.
4. Processing trace and result are persisted.
5. Failed or completed events are visible through Integration Replay.

### Citizen identity

1. Social participants are enriched from publication or operations data.
2. `CitizenResolverService` resolves or creates an identity.
3. Repositories persist social identity and citizen data.
4. Events can be published for downstream behavior.

### Operations

1. Facebook comments can be adapted into work items.
2. Queue query services provide operational lists.
3. Detail query services assemble the work context.
4. Assignment, status, priority and response actions modify the work item lifecycle.

### Workflow

1. Administrators design versioned workflows.
2. Versions contain nodes and transitions.
3. Published versions are executed by the runtime.
4. Runtime events are published into the event engine.
5. Runtime Inspector and Simulator provide operational visibility.

## 5. Strengths

- Clear functional modularization.
- Separation between query services and write orchestration in several core areas.
- Repository abstractions around important aggregates.
- Integration event capture and replay provide strong traceability.
- Workflow instrumentation is designed into the runtime instead of added only at the UI layer.
- Route-level authentication and permission checks are explicit.
- Global CSRF protection is enabled with narrow exclusions.

## 6. Architectural risks

### Central composition root growth

`Config\Services` contains many concrete constructions and direct `db_connect()` calls. As the platform grows, this file may become difficult to navigate and test. Service registration should eventually be grouped by bounded context or delegated to module-specific factories.

### Mixed module naming

The route map uses modules such as `Citizens`, while service namespaces include `Citizen`. This may be intentional, but inconsistent singular/plural boundaries make discovery harder and should be standardized.

### Direct database dependency in query services

Several query services receive a raw database connection. This is efficient for read models but couples them to CodeIgniter database APIs. The trade-off should be documented and query contracts should remain stable.

### Cross-module orchestration

Operations, Publication, Citizen and Integration intentionally collaborate. Without dependency rules, these relationships can evolve into circular coupling. The preferred direction is Presentation → Application → Domain/Contracts → Infrastructure, with cross-module collaboration through explicit services or events.

### Global service locator usage

Calls such as `service('publicationProfile')` are convenient but hide dependencies from constructors. New complex services should prefer explicit dependency injection where practical.

## 7. Recommended dependency rules

1. Controllers orchestrate requests and responses; they do not contain business rules.
2. Views never query models or services directly.
3. Application services may depend on repository contracts, not concrete controllers or views.
4. Infrastructure implements repositories and external adapters.
5. Cross-module writes occur through application services or events.
6. Query services may use optimized SQL, but must not mutate state.
7. New integrations must first create an auditable integration envelope.

## 8. Architectural conclusion

CIAC has a stronger baseline than a typical MVC CRM. Its modular monolith, event infrastructure, replay capability and workflow runtime provide a credible foundation for growth. The highest architectural priority is not a rewrite; it is enforcing dependency boundaries, reducing hidden service-locator dependencies and adding automated tests around the principal flows.
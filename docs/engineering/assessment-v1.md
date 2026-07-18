# CIAC Engineering Assessment v1.0

## Executive summary

CIAC has evolved beyond a conventional CRM. The current repository represents a modular platform for citizen attention with identity resolution, operations management, cases, publications, Messenger ingestion, auditable integration replay and a versioned workflow engine.

The platform has a solid architectural foundation for its current stage. Its strongest areas are modularity, security controls, workflow instrumentation and integration traceability. Its largest gaps are automated testing, production observability, verified database performance and cryptographic webhook validation.

This assessment recommends strengthening the existing modular monolith rather than introducing microservices or large rewrites.

## Assessment basis

Evidence reviewed:

- registered application routes;
- global authentication, permission and CSRF filters;
- service composition in `Config\Services`;
- Messenger webhook processing;
- secure migration and seeding controller;
- workflow, publication, operations, citizen and integration services;
- existing security readiness documentation.

Not yet measured:

- production request latency;
- database execution plans;
- table cardinality and growth rate;
- memory consumption under load;
- concurrent webhook throughput;
- automated test coverage percentage.

Scores involving these areas are therefore provisional.

## Scorecard

| Area | Score | Status | Assessment |
|---|---:|:---:|---|
| Architecture | 8/10 | Green | Modular monolith with application services, repositories and events. |
| Security | 8/10 | Green | Global CSRF, explicit permissions and protected system actions; webhook signature remains pending. |
| Maintainability | 7/10 | Yellow | Good module boundaries, but service locator use and central service registration are growing. |
| Database | 6/10 | Yellow | Query services exist, but indexes and execution plans require a dedicated audit. |
| Performance | 5/10 | Yellow | No reliable baseline or production measurements are documented. |
| Scalability | 7/10 | Yellow | Integration envelopes and modular design help; asynchronous processing strategy is not yet established. |
| Observability | 4/10 | Red | Replay and health foundations exist, but there is no unified technical observatory or alerting. |
| Automated testing | 3/10 | Red | Critical flows require regression coverage before higher scale or more channels. |
| Documentation | 7/10 | Yellow | Security documentation is strong; architecture and operating procedures are being formalized. |

**Provisional overall score: 6.6/10.**

## Principal strengths

### Modular platform boundaries

The route map separates authentication, authorization, operations, citizens, conversations, cases, Messenger, integration, notifications, analytics, publications and workflow functionality.

### Application and query services

Core modules avoid placing all behavior in controllers. Query services assemble optimized read models, while application services coordinate state-changing behavior.

### Auditable integration model

External events are captured with payload, headers, correlation data, processing trace and replay lineage. This is a major operational strength.

### Workflow capabilities

CIAC includes versioning, nodes, transitions, publication, runtime instrumentation, inspection and simulation. This creates a reusable automation layer independent of a single Messenger flow.

### Security baseline

Administrative authentication, permission filters, POST discipline and global CSRF are present. System actions require independent controls including HTTPS, header token, optional IP allowlist and explicit seeder allowlist.

## Critical and high-priority findings

### EA-001 — Validate Meta webhook signatures

**Priority:** Critical

The webhook captures `X-Hub-Signature-256`, but no effective cryptographic validation was found before processing. CSRF exclusion is necessary for external calls but does not authenticate Meta as the sender.

**Recommendation:** validate the HMAC-SHA256 signature against the raw request body using the Meta App Secret and reject missing or invalid signatures before event capture or processing.

### EA-002 — Establish automated regression tests

**Priority:** Critical

Security and business flows currently depend heavily on manual regression. Global CSRF, permissions, replay and workflow behavior require repeatable tests.

**Minimum test suites:**

- authentication and logout;
- authorization boundaries;
- webhook valid/invalid signature;
- integration capture and replay;
- citizen identity resolution;
- operations lifecycle;
- case assignment and status transitions;
- workflow validation and runtime execution.

### EA-003 — Audit database indexes and query plans

**Priority:** High

The system has read-heavy dashboards and detail profiles composed by query services. Without execution-plan evidence, database readiness cannot be certified.

**Recommendation:** inventory tables and indexes; capture representative `EXPLAIN` plans for queue, dashboard, publication profile, citizen timeline, integration event list and runtime inspector queries.

### EA-004 — Introduce production observability

**Priority:** High

Replay provides event-level evidence, but CIAC lacks a unified view of error rates, processing latency, webhook throughput, workflow failures and slow requests.

**Recommendation:** design CIAC Observatory around persisted metrics and health indicators rather than parsing raw logs in the browser.

### EA-005 — Define asynchronous processing boundaries

**Priority:** High

Webhook processing appears synchronous. As volume grows, external ingestion should acknowledge quickly and move nonessential work to a durable queue.

**Recommendation:** first measure processing time; then define which steps must remain synchronous and which can be queued with idempotency and retry controls.

## Medium-priority findings

### EA-006 — Reduce hidden dependencies

Controllers and services frequently resolve dependencies through the global service locator. This is practical but makes unit testing and dependency analysis harder.

**Recommendation:** prefer explicit constructor dependencies for new complex classes and gradually refactor only high-value services.

### EA-007 — Split service registration by context

`Config\Services` is becoming a central composition file for Integration, Event, Workflow, Operations, Citizen and Publication services.

**Recommendation:** establish module-specific service factories or clearly separated sections and naming conventions before adding more channels.

### EA-008 — Standardize module naming

Singular and plural module namespaces coexist, such as Citizen and Citizens. This increases cognitive overhead.

**Recommendation:** define a naming standard and apply it only through planned, low-risk refactors.

### EA-009 — Formalize API contracts

Runtime endpoints exist under the administrative route group. Future external or multi-channel APIs need versioning, authentication policy, response schemas and rate limiting.

## Database review plan

The next database assessment should cover:

1. schema inventory and estimated cardinality;
2. primary keys, foreign keys and indexes;
3. high-frequency filters and sorting columns;
4. composite-index candidates;
5. duplicate or overlapping indexes;
6. pagination strategy;
7. large JSON/text columns and retention;
8. replay and event retention policy;
9. query plans for critical screens;
10. backup and restore validation.

Likely growth-sensitive entities include messages, comments, publications, integration events, system events, workflow executions, notifications, operations work items and case history.

## Performance review plan

Create a reproducible baseline for:

- admin dashboard;
- operations queue and detail;
- citizen profile and timeline;
- publication profile and analytics;
- integration replay list and detail;
- workflow runtime inspector;
- Messenger webhook processing.

Record response time, database time, query count, peak memory and payload size. Optimization should begin only after this baseline exists.

## Security follow-up

Beyond webhook signatures, review:

- session cookie flags and lifetime;
- login throttling and lockout;
- security headers and CSP rollout;
- secret rotation procedures;
- audit-log retention;
- authorization tests at controller and resource level;
- rate limiting for external and sensitive endpoints;
- production error disclosure and log hygiene.

## Observability target

CIAC Observatory should eventually expose:

- overall health status;
- webhook success and failure rates;
- average and percentile processing time;
- integration replay outcomes;
- workflow execution and failure counts;
- queue age and pending work;
- recent exceptions grouped by fingerprint;
- slow requests and slow database queries;
- security-relevant rejected requests;
- deployment and schema version.

## Decision

CIAC is ready to continue toward production hardening and controlled growth, but it should not be declared ready for tenfold scale until automated tests, webhook authentication, database evidence and operational metrics are completed.

The recommended strategy is incremental strengthening through focused PRs, preserving the current modular monolith.
# CIAC Technical Roadmap

## Guiding principles

- Preserve the modular monolith while it remains operationally appropriate.
- Prioritize evidence, tests and observability before speculative optimization.
- Use small focused Pull Requests.
- Avoid large rewrites.
- Document high-impact architectural decisions before implementation.
- Treat external events as auditable, idempotent integration envelopes.

## CIAC v1.1 — Reliability and evidence

### Priority 0

1. Validate `X-Hub-Signature-256` before processing Messenger webhooks.
2. Add automated regression tests for authentication, permissions and CSRF.
3. Add integration tests for event capture, replay and workflow runtime.

### Priority 1

4. Inventory database tables, foreign keys and indexes.
5. Capture execution plans for critical query services.
6. Establish a performance baseline for principal screens and webhook processing.
7. Review session configuration, login throttling and security headers.
8. Document deployment, rollback, backup and restore procedures.

### Exit criteria

- Invalid webhook signatures are rejected.
- Critical security and lifecycle flows run in CI.
- Database recommendations are supported by execution plans.
- A repeatable performance baseline exists.
- Production runbooks are documented.

## CIAC v1.5 — Observatory and operational resilience

1. Design and implement CIAC Observatory.
2. Persist request, integration and workflow metrics.
3. Add error fingerprinting and recent-failure views.
4. Add queue-age and pending-work indicators.
5. Define retention and archival policies for events and traces.
6. Introduce alert thresholds for webhook failures and processing latency.
7. Evaluate asynchronous processing for nonessential webhook work.

### Exit criteria

- Operators can determine platform health without reading raw logs.
- Critical failures and latency degradation are visible.
- Replay, workflow and webhook metrics share consistent correlation identifiers.

## CIAC v2.0 — Multi-channel and intelligent assistance

1. Define a channel-neutral incoming-message contract.
2. Separate channel adapters from conversation and workflow orchestration.
3. Add WhatsApp and Instagram through auditable integration adapters.
4. Introduce response suggestions with human approval.
5. Add classification, priority and sentiment assistance with measurable confidence.
6. Establish model governance, prompt versioning and audit trails.
7. Define a versioned public or partner API with rate limiting and scoped credentials.

### Preconditions

- Strong automated test coverage.
- Reliable observability.
- Proven database capacity.
- Durable retry and idempotency strategy.
- Security review for every new channel and AI capability.

## Recommended immediate PR sequence

- **PR #84:** validate Meta webhook signatures.
- **PR #85:** authentication, CSRF and permission regression tests.
- **PR #86:** integration capture and replay tests.
- **PR #87:** database schema and index assessment documentation.
- **PR #88:** performance baseline instrumentation.
- **PR #89:** RFC for CIAC Observatory.

The numbering is indicative and may change based on defects discovered during deployment regression.
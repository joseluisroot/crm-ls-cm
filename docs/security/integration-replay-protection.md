# Integration Replay Protection

CIAC detects repeated Meta webhook deliveries using the combination of integration source and external event identifier.

## Protected identifiers

The Messenger webhook currently extracts the first available identifier from:

- `message.mid`;
- `postback.mid`;
- `comment_id`;
- `post_id`.

Events without an external identifier continue through the normal capture flow because they cannot be safely classified as duplicates.

## Processing order

1. Validate `X-Hub-Signature-256`.
2. Decode and validate the JSON payload.
3. Extract the external event identifier.
4. Search for an original event with the same source and external identifier.
5. When found, return `200` with status `duplicate` without capturing or processing the delivery again.
6. Otherwise, capture and process the event normally.

Returning `200` prevents Meta from retrying a delivery that CIAC has already accepted.

## Manual replay compatibility

Administrative replays are not treated as external duplicates. The lookup only considers original events where:

- `original_event_id` is `NULL`;
- `replay_attempt` is `0`.

This preserves the existing controlled replay workflow.

## Current guarantee

This layer prevents normal sequential redelivery. Atomic concurrency protection will be added separately through database-backed idempotency, so two identical requests arriving at exactly the same time cannot both claim the event.

## Logging

Duplicate deliveries record operational context using the external event identifier and the original correlation ID. Payloads and secrets are not written to the duplicate log entry.

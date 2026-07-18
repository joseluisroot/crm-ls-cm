# Meta Webhook Signature Verification

CIAC validates every POST request received at `webhooks/messenger` using the `X-Hub-Signature-256` header before capturing or processing the payload.

## Required environment variable

Configure the Meta App Secret in the deployment environment:

```dotenv
META_APP_SECRET=replace-with-the-app-secret-from-meta
```

The value must never be committed to the repository, written to logs or exposed in an error response.

## Validation flow

1. Read the exact raw HTTP request body.
2. Read `X-Hub-Signature-256`.
3. Calculate `sha256=` plus the HMAC-SHA256 digest using `META_APP_SECRET`.
4. Compare the expected and received signatures using `hash_equals()`.
5. Reject the request before event capture when validation fails.

## Responses

| Condition | Response |
|---|---:|
| App Secret is not configured | `503 Service unavailable` |
| Signature is missing, malformed or invalid | `401 Unauthorized` |
| Signature is valid but JSON is invalid | `400 Bad Request` |
| Signature and payload are valid | Continues to integration processing |

## Logging policy

Rejected signatures log only operational context:

- request IP;
- User-Agent;
- rejection reason.

The following values must not be logged:

- Meta App Secret;
- calculated HMAC;
- full webhook payload.

## Deployment checklist

1. Add `META_APP_SECRET` to the production environment.
2. Confirm that the value matches the App Secret for the Meta application sending the webhook.
3. Deploy the code only after the variable is available.
4. Send a valid webhook test from Meta.
5. Confirm that requests without a signature return `401`.
6. Review application logs for unexpected rejections.

## Rotation

When rotating the Meta App Secret, coordinate the secret update and application deployment to avoid rejecting legitimate webhook deliveries. Do not keep old secrets in source code.

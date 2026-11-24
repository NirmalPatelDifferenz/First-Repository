# Phase 2: Stripe reliability (inline execution)

## Deployment constraint and architecture decision

The production environment cannot run a continuously supervised queue worker. Phase 2 therefore does **not** depend on Laravel's database queue, Redis, Supervisor, or a long-running worker.

Stripe operations will continue to execute synchronously inside the existing HTTP request lifecycle:

1. Validate the request.
2. Persist a Stripe operation record before making an external request.
3. Call Stripe using a stable idempotency key for that operation.
4. Persist each successful Stripe resource ID and state transition.
5. Persist the local product change and mark the operation successful.
6. On an ambiguous outcome or failed compensation, retain a durable operation record that can be inspected and explicitly retried/reconciled through an application request or an operator-run command.

This design is compatible with the current deployment model, but it does not make network calls asynchronous. The HTTP request remains open while Stripe responds. Timeouts and user-perceived latency still need explicit limits and UI handling.

## Important limitation

Code that runs only during an HTTP request cannot independently continue after the request ends, nor can it guarantee automatic retries while no request is active. Automatic scheduled reconciliation would require a scheduler/cron trigger or an external worker, neither of which is assumed in this phase. Until such infrastructure is approved, recovery is initiated by a subsequent application request or an operator.

Do not describe this design as background processing.

## Operation lifecycle

Supported initial operation types:

- `create_product`
- `delete_product`

Statuses:

- `pending`: operation has been durably recorded and has not started.
- `processing`: an HTTP request is currently attempting the operation.
- `succeeded`: all required Stripe and local persistence steps completed.
- `failed`: the operation failed with a known outcome and is safe to inspect.
- `reconciliation_required`: the outcome is ambiguous or compensation failed; do not blindly create new Stripe resources.

The unique operation UUID identifies the application operation. The unique idempotency key is sent to Stripe and reused for retries of that same logical operation. A new logical user action must receive a new key.

## Persistence foundation

The `stripe_operations` table stores:

- Application operation UUID and Stripe idempotency key.
- Operation type and lifecycle status.
- Related local product ID when available.
- Stripe product and price IDs as soon as they are known.
- The minimal operation payload needed to resume or investigate the operation.
- Attempt count, safe error code/message, and timestamps.

The `product_id` column intentionally has no foreign-key constraint in this first migration. Product creation starts before a local product row exists, and operation history should remain available even if a product is later removed.

The payload must contain only the fields needed to execute/reconcile the Stripe operation. Never store API secrets, bearer tokens, card data, or other payment credentials. Error messages must be sanitized before persistence.

## Implementation sequence

1. Add the operation persistence model and schema. (This foundation step.)
2. Add operation state transitions and transaction boundaries.
3. Add Stripe idempotency-key support for product and price create calls; use distinct stable keys for each Stripe resource operation.
4. Integrate create flow so operation state and returned Stripe IDs are persisted incrementally.
5. Integrate delete flow with safe repeated deactivation and explicit reconciliation states.
6. Add an authenticated, authorized recovery path; do not expose arbitrary operation replay to untrusted clients.
7. Add tests for duplicate submissions, Stripe timeouts/ambiguous outcomes, partial success, failed compensation, repeated delete, and recovery.
8. Document production timeout and operator recovery procedures.

## Non-goals for this first step

- No queue jobs or background workers.
- No automatic retry loop inside a web request.
- No cron/scheduler assumption.
- No controller or route behavior changes.
- No change to the existing Stripe workflow until the operation lifecycle and idempotency behavior are implemented and tested.

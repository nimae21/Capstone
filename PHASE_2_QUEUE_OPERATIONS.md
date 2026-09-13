# Phase 2 queue and Redis operations

## Deployment order

1. Back up PostgreSQL and deploy the application code with Redis still disabled.
2. Run `php artisan migrate --force`. The background operation tables must exist before web traffic reaches this version.
3. Add one private Railway Redis service. Reference its `REDIS_URL` from the web, worker, and scheduler services; do not copy a public URL.
4. Give all three Laravel services the same application, database, mail, PayMongo, Firebase, and Redis environment. Keep `APP_KEY` identical so encrypted sessions and encrypted queue payloads work across processes.
5. Switch the shared drivers to Redis, restart the web service, then start the worker and scheduler commands below.
6. Run `php artisan queue:health --max-age=120` after two scheduler cycles. A nonzero exit reports a missing or stale worker. Monitor this command externally because an application scheduler cannot report its own absence.

The web service uses its existing start command. The worker service uses:

```text
php artisan queue:work redis --queue=background --sleep=1 --tries=5 --timeout=90 --max-time=3600
```

The scheduler service uses:

```text
php artisan schedule:work
```

The scheduler redispatches missed durable operations and recovers pending mobile push deliveries every minute. It also queues the worker heartbeat every minute and prunes failed jobs using `QUEUE_FAILED_RETENTION_HOURS`.

## Railway environment

Set these values on every Laravel service:

```dotenv
REDIS_CLIENT=predis
REDIS_URL=${{Redis.REDIS_URL}}
CACHE_STORE=redis
CACHE_LIMITER=redis_rate_limit
SESSION_DRIVER=redis
SESSION_CONNECTION=session
QUEUE_CONNECTION=redis
QUEUE_AFTER_COMMIT=true
QUEUE_FAILED_DRIVER=database-uuids
REDIS_QUEUE_CONNECTION=default
REDIS_CACHE_CONNECTION=cache
REDIS_RATE_LIMIT_CONNECTION=rate_limit
REDIS_CACHE_LOCK_CONNECTION=lock
REDIS_QUEUE=background
BACKGROUND_QUEUE=background
REDIS_QUEUE_RETRY_AFTER=120
REDIS_QUEUE_BLOCK_FOR=5
```

Queue, cache, session, rate-limit, and lock data use separate connection names, database numbers, and prefixes. Keep the distinct prefixes when a provider exposes only Redis database zero. The full optional tuning list is in `.env.example`.

## Local fallback

Redis is opt-in. Local development can retain:

```dotenv
CACHE_STORE=database
CACHE_LIMITER=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

Run `php artisan queue:work database --queue=background` and `php artisan schedule:work`. Automated tests force array cache/session stores and the synchronous queue, so they do not connect to Redis.

## Activity aggregation rollback

The aggregation migration refuses to roll back while any `activity_count` is greater than one. A rollback must be a planned maintenance operation:

1. Stop web writes, queue workers, and the scheduler, and take a database backup.
2. Run `php artisan activities:prepare-aggregation-rollback --max-copies=10000 --batch=500`.
3. Repeat the command only when it exits with failure and reports remaining aggregates. Each invocation creates at most `max-copies` rows, each transaction inserts at most `batch` rows, and lock/deadlock retries are limited to three.
4. Verify `select count(*) from user_activities where activity_count > 1` returns zero.
5. Roll back the aggregation migration, then deploy code that does not read the aggregation columns.

This procedure preserves the legacy one-row-per-signal representation without an unbounded migration transaction. Size the database for the expanded history before starting. If expansion is impractical, restore the backup and keep the aggregation migration applied.

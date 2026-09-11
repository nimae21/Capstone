# Achilles production security audit

Audit date: 2026-09-11  
Scope: Laravel 12 web/API application, PostgreSQL, PayMongo, POS, governance, uploads, reports, activity logs, and the Python recommendation service.

## Executive assessment

The application already had ownership checks, CSRF protection, a locked FIFO stock service, server-side PayMongo confirmation, active-account middleware, and a two-role governance workflow. This pass preserved those designs and closed the confirmed gaps.

| Severity | Finding | Resolution |
|---|---|---|
| Critical | Two database credentials had been committed in .env.example. | Redacted from the tracked file. Rotate and revoke both credentials and remove them from Git history. |
| High | Locked PHP packages had published security advisories. | Updated Laravel to 12.61.1, CommonMark to 2.10.1, and affected Symfony packages to patched 7.4 releases. Composer audit is clean. |
| High | POS totals trusted browser-supplied prices. | Client prices are prohibited. The service reads active variants and current stock prices, calculates totals, and deducts FIFO stock inside the existing transaction. |
| High | A POS request could be replayed into another order. | Added a unique UUID request ID, PostgreSQL advisory lock, payload comparison, and idempotent return of the original sale. |
| High | The recommendation user-ID endpoint had no service authentication. | Added a fail-closed shared key, constant-time comparison, bounded IDs and limits, and a Laravel client header. Keep the service private on Railway. |
| Medium | Signed PayMongo payloads had no freshness window. | Added a configurable 300-second timestamp tolerance, raw-body HMAC verification, constant-time comparison, and a 1 MiB body limit. |
| Medium | PayMongo create requests had no provider idempotency key. | Initial and retry checkout-session requests now send stable, attempt-specific Idempotency-Key headers. |
| Medium | CORS allowed any origin, method, and header. | Replaced wildcards with an exact environment allowlist and narrow method/header lists. |
| Medium | Browser responses lacked baseline hardening headers. | Added CSP frame restrictions, clickjacking, MIME-sniffing, referrer, permissions, HSTS, and sensitive-page no-store headers. |
| Medium | Mobile Sanctum tokens had no expiration. | Default lifetime is seven days and tokens use an achilles_ scanning prefix. Logout and suspension still revoke tokens. |
| Medium | Checkout used the cart's older stored price. | Checkout now verifies active products/variants, bounds quantity, and snapshots current server stock prices into the order. |
| Medium | Some failures exposed raw database/provider exception text. | Unexpected failures are reported internally and return generic messages. Business-rule messages remain actionable. |
| Low | Password reset, search, reports, checkout, POS, and API endpoints needed stronger limits. | Added named or route-specific throttles and bounded input. |
| Low | Privileged and token-bearing models had broad mass assignment. | Removed role and is_active from User fillable data; privileged writes are explicit. Governance, invitation, registration, and push models list exact fields. |
| Low | Category changes did not invalidate catalog filters. | Category save/delete now invalidates the same bounded cache as brands and shoe types. |

## Controls confirmed

- Customer order, address, cart, retry, checkout-return, and cancellation paths enforce ownership.
- Browser state changes use POST, PUT, PATCH, or DELETE with Laravel CSRF protection. Only the signed PayMongo webhook is exempt.
- Admin, Super Admin, and customer middleware boundaries remain. Suspended accounts are blocked and their API tokens are revoked.
- Governance requests remain audit records. Self-approval, replay, and approval for a suspended requester are rejected.
- SQL fragments use bound parameters or fixed aggregate expressions. No user input is concatenated into SQL.
- Blade output is escaped; no raw Blade output was found.
- Product uploads retain image validation, approved MIME/extensions, 5 MiB per file, ten files per request, generated names, and staged approval storage.
- Online payment completion still requires a signed paid resource with PHP currency and amount matching. Browser returns cannot mark orders paid.
- Payment confirmation and POS retain transactions, row locks, and FIFO deduction. POS completes immediately; online orders still move paid, shipped, completed.
- Reports count completed online and POS orders.
- Logs omit credentials, device tokens, raw provider bodies, passwords, and token hashes.

## Required Railway configuration

Set these on the Laravel service, replacing placeholders. Do not regenerate an existing production APP_KEY because that invalidates encrypted data.

    APP_ENV=production
    APP_DEBUG=false
    APP_URL=https://shop.example.com
    APP_KEY=<keep-current-production-key>
    LOG_CHANNEL=stack
    LOG_STACK=stderr
    LOG_LEVEL=warning

    DB_CONNECTION=pgsql
    DB_HOST=<private-database-host>
    DB_PORT=5432
    DB_DATABASE=<database>
    DB_USERNAME=<least-privilege-user>
    # Set DB_PASSWORD in Railway's secret store; do not commit it.
    DB_PASSWORD=

    SESSION_DRIVER=database
    SESSION_ENCRYPT=true
    SESSION_SECURE_COOKIE=true
    SESSION_HTTP_ONLY=true
    SESSION_SAME_SITE=lax
    CACHE_STORE=database
    QUEUE_CONNECTION=database

    CORS_ALLOWED_ORIGINS=https://shop.example.com
    TRUSTED_HOSTS=shop.example.com,<railway-healthcheck-host-if-required>
    SANCTUM_EXPIRATION=10080
    SANCTUM_TOKEN_PREFIX=achilles_

    PAYMONGO_SECRET_KEY=<live-secret>
    PAYMONGO_PUBLIC_KEY=<live-public-key>
    PAYMONGO_WEBHOOK_SECRET=<live-endpoint-signing-secret>
    PAYMONGO_WEBHOOK_TOLERANCE=300

    RECOMMENDATION_SERVICE_URL=http://recommendation-service.railway.internal:5000
    RECOMMENDATION_SERVICE_KEY=<same-random-32-byte-or-longer-secret-on-both-services>

Use a real SMTP or transactional mail provider. MAIL_MAILER=log is unsafe in production because verification and reset links enter logs. Set all SUPABASE_STORAGE variables. Store Firebase credentials outside the repository and public directory; keep mobile push disabled until configured.

Deploy in this order:

1. Take a database snapshot and verify health.
2. Deploy and run php artisan migrate --force. The new migration is additive and does not reset or delete data.
3. Run php artisan optimize.
4. Use /up as the Railway health endpoint.
5. Run php artisan queue:work --sleep=3 --tries=3 --timeout=90 --max-time=3600 as a restartable worker.
6. Run php artisan schedule:work separately for push delivery and registration pruning.
7. Deploy recommendations in the same environment, set the shared key on both services, use internal HTTP, and remove its public domain.

Railway private networking: https://docs.railway.com/networking/private-networking/how-it-works

## Required PayMongo configuration

- Create separate test and live webhooks and store the matching signing secret per environment.
- Register only https://shop.example.com/webhooks/paymongo.
- Subscribe to the payment and refund events used by this application.
- Do not apply a Cloudflare challenge, cache, redirect, or body transformation to the webhook.
- Keep clocks synchronized because signatures outside the configured tolerance are rejected.
- Monitor PayMongo delivery logs and re-deliver failures after outages.

Official guidance for signatures, timestamps, and idempotency:
https://docs.paymongo.com/docs/developer-tools-webhook-setup-management
https://docs.paymongo.com/docs/developer-tools-best-practices-1

## Cloudflare baseline

1. Proxy the production hostname and choose SSL/TLS Full (strict) after confirming Railway has a matching certificate.
2. Enable Always Use HTTPS. Enable Cloudflare HSTS only after HTTPS and all included subdomains are verified.
3. Enable the managed WAF rules and bot protection available on the plan.
4. Create IP-based rate rules for POST /login, /register, /password/email, /checkout/place-order, /admin/pos/sale, and for /search, /api/*, and /admin/reports*. Start in log mode, review traffic, then challenge or block.
5. Exempt exactly POST /webhooks/paymongo from bot challenges and caching while retaining DDoS and application signature checks.
6. Bypass cache for /admin*, /governance*, /api*, /checkout*, /orders*, /cart*, /login*, /register*, /password*, and /webhooks*. Cache only versioned static assets.
7. Remove unused Railway public hostnames and keep TRUSTED_HOSTS minimal.

Official Cloudflare references:
https://developers.cloudflare.com/ssl/origin-configuration/ssl-modes/full-strict/
https://developers.cloudflare.com/waf/rate-limiting-rules/

## Credential incident response

The old database secrets are compromised even if the repository was private.

1. Create new least-privilege database credentials.
2. Update Railway and local secret stores, deploy and verify, then revoke the old credentials.
3. Review database authentication/audit logs from first exposure through revocation.
4. Put exact old values in a replacement file outside the repository and run git filter-repo --replace-text <absolute-replacement-file> --force.
5. Coordinate the rewrite, force-push affected branches and tags, invalidate old clones, and ask collaborators to clone again.
6. Enable repository secret scanning and push protection.

History rewriting is disruptive and was deliberately not executed.

## Backup and recovery

Enable all three Railway PostgreSQL layers:

- Scheduled daily, weekly, and monthly volume snapshots.
- Point-in-time recovery before an incident.
- Automated encrypted pg_dump --format=custom --no-owner exports outside the database service/project failure boundary.

Keep at least 30 daily and 12 monthly logical dumps unless policy requires longer. Quarterly, restore into a disposable database, compare critical table counts and recent orders, payments, and movements, record recovery time and point, then destroy the drill database. Railway's current guide:
https://docs.railway.com/guides/postgres-backups-restores

Back up Supabase product objects separately or enable bucket versioning/lifecycle protection. Database dumps do not contain image bytes.

## Verification completed

- Composer audit: no known security advisories.
- npm production dependency audit: zero vulnerabilities.
- Laravel unit and feature suite: 123 passed, 2 database-heavy rendering cases intentionally skipped, 863 assertions.
- Governance suite: 15 passed, 205 assertions.
- POS suite: 5 passed, 67 assertions.
- Browser JavaScript suite: 18 passed.
- Recommendation service: 5 passed; Python compilation succeeded.
- Production Vite build and Laravel config, route, and Blade cache compilation succeeded.
## Residual risks and operating checks

- Committed credentials remain recoverable until rotation and history cleanup finish.
- Cloudflare cannot protect a directly reachable Railway origin. Remove unnecessary public hostnames and enforce origin controls.
- CSP remains compatible with current CDN fonts, icons, maps, and payment redirects. Self-host those assets before adopting a strict source CSP.
- Existing foreign keys were preserved. Audit live data before adding stricter CHECK or one-row uniqueness constraints so legacy order, payment, stock, and audit history is not lost.
- Monitor authentication failures, 419/429/5xx rates, PayMongo delivery/refund states, queue depth, scheduler heartbeat, backup age, and recommendation 401/503 rates.
- Perform a controlled low-value live payment/refund smoke test after deployment. Automated tests use fakes.
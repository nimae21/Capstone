# Connected repository audit — Phase 1 checkpoint

Date: 2026-09-13. This is a partial Phase 1 checkpoint, not completion of the five-phase request. No production deployment or database migration was performed. No files were deleted.

## Implemented root-cause fixes

- Web login used an email-plus-IP bucket. API login had the same account-bucket weakness. Independent account (5/minute) and IP (30/minute) limits now stop IP rotation and password spraying. Successful web login clears the account bucket but does not reset the IP bucket. Blocked requests do not reach failed-auth audit writes.
- All proxy addresses were trusted. `TRUSTED_PROXIES` now explicitly supplies comma-separated ingress IPs/CIDRs; empty means no trusted proxy. Set this to verified infrastructure addresses before deployment. `TRUSTED_HOSTS` remains separate.
- Browser CSP only constrained framing. It additionally blocks object embedding and restricts base URLs. This is not yet a complete script/style policy.
- POS names, sizes and colors were interpolated into HTML, and gallery URLs into inline event handlers. These now use DOM creation, textContent, and event listeners, retaining existing CSS classes and controls.
- Refund requests held order/payment locks across HTTP. Requests now use the existing durable idempotency key outside that transaction, then recheck locked state. A webhook outcome that arrives during HTTP cannot be overwritten by a late error.
- Cancellation and retired-session verification also held locks across HTTP. Provider verification now precedes the short locked state transition. Concurrent session replacement rejects the stale transition for retry.
- Mobile token/profile storage used plaintext Preferences. A local Capacitor plugin encrypts both with AES-256-GCM and a non-exportable AndroidKeyStore key, with per-field authenticated data. Legacy values are deleted only after encryption succeeds. Subsequent reads use memory; concurrent restore is deduplicated and serialized against logout. Browser sessions are memory-only.
- Mobile screen snapshots persisted personal data. Screen caches are now bounded, memory-only, account-scoped, and cleared on signout. Startup deletes old plaintext snapshots without loading their contents, outside the rendering critical path.
- Compatible Laravel frontend security updates were applied through npm audit fix; no forced major upgrades.

## Changed files

Laravel:
- `.env.example`, `bootstrap/app.php`
- `app/Http/Controllers/Auth/LoginController.php`
- `app/Http/Middleware/SecurityHeaders.php`
- `app/Providers/AppServiceProvider.php`
- `app/Services/OrderService.php`
- `resources/views/admin/pos/index.blade.php`
- `resources/views/product/show.blade.php`
- `tests/Feature/IndependentLoginLimitsTest.php`
- `tests/Feature/PayMongoLifecycleTest.php`
- `tests/Feature/SecurityHardeningTest.php`
- `tests/Frontend/safe-dom.test.mjs`
- `package-lock.json`

Mobile:
- `android/app/src/main/java/shop/achilleswearyourweakness/admin/MainActivity.java`
- `android/app/src/main/java/shop/achilleswearyourweakness/admin/SecureSessionPlugin.java`
- `src/services/secure-storage.ts`, `src/services/api.ts`
- `src/services/screen-cache.ts`, `src/main.ts`, `src/router/index.ts`
- `tests/unit/secure-storage.spec.ts`, `tests/unit/api.spec.ts`, `tests/unit/performance.spec.ts`

Recommendation service: inspected and baseline-tested; no source changes.

## Verification

- Laravel Pest: 174 passed, 2 skipped, 1,252 assertions, using SQLite test transactions. This does not establish PostgreSQL multi-process concurrency behavior.
- Laravel Pint: passed on changed PHP files.
- DOM regression tests: 2 passed, exercising malicious values and quantity/gallery event behavior. A DOM double rejects any HTML parsing; browser layout QA remains.
- Laravel production Vite build: passed after compatible dependency updates (Vite 7.3.6); DOM tests also passed again.
- Composer locked audit: no advisories. Full Laravel npm audit after compatible fixes: 0 vulnerabilities.
- Mobile: 51 unit tests passed; ESLint passed; TypeScript and production Vite build passed. Existing Ionic CSS, large chunk, legacy-plugin and Vite config warnings remain.
- Android native Java compilation: passed with Android Studio's bundled JDK. The system Java 8 cannot build this project. Use a process-local JAVA_HOME pointing to the bundled JDK or a compatible installed JDK.
- No device/emulator is connected. Encryption roundtrip across process death, device key invalidation, app upgrade migration and push delivery still require device verification.
- Mobile runtime-only npm audit: 0 vulnerabilities. Full audit: 13 (5 moderate, 8 high), in Cypress/ESLint/Capacitor CLI dependency trees. Compatible npm audit fix did not resolve them. Forced major upgrades were not applied.
- Cypress E2E: could not start; cached Cypress 13.17.0 executable fails with `Invalid or incompatible cached data (cachedDataRejected)`.
- Recommendation service: 5 unittest security tests passed; pip check passed. A Python vulnerability audit was not performed.
- No EXPLAIN ANALYZE, production data cleanup, index migration, or N+1 profiling was performed in this checkpoint. Existing lifecycle/RBAC/API regression tests pass; this is not a proof of all contracts or races.

## Required follow-up before Phase 1 is complete

1. Refactor `OrderService::retryCheckout` using a durable checkout operation/outbox and recovery path. Its expiry/session-creation network calls still run inside a transaction. Do not claim all network/lock coupling is fixed.
2. Finish strict Laravel/WebView CSP, inline asset extraction, asset pinning/SRI, and browser/device compatibility checks. The legacy Vite feature detector imports a data URL and injects inline bootstraps; a strict policy needs tested handling of those scripts.
3. Confirm deployment PostgreSQL transport support and introduce tested TLS/connect/statement timeout settings. TLS enforcement has not changed.
4. Supply the actual ingress proxy IPs/CIDRs for `TRUSTED_PROXIES`. No wildcard was substituted for missing deployment information.
5. Register/obtain the intended Firebase Android client before changing `io.ionic.starter`. The existing application ID and Firebase configuration are unchanged.
6. Complete the remaining dynamic-DOM/inline-code review and authentication audit-write aggregation. The inactive Breeze LoginRequest was inspected but not modified; the active routes use Laravel UI LoginController.
7. Repair/upgrade the Cypress toolchain, resolve remaining development dependency advisories, and perform device validation.

Phases 2–5 and cleanup remain unimplemented, apart from the security-driven memory caching/token reuse changes described above. No new worker service is required by this checkpoint; a future durable checkout outbox will require a worker/reconciliation schedule. Keep existing workers and webhook signature configuration intact.

Android reference: https://developer.android.com/reference/android/security/keystore/KeyGenParameterSpec
# Achilles registration and governance

Implementation is in `caps`, the Laravel backend. The checked-out project declares Laravel 12 (not 11), and the configured live database is PostgreSQL (not MySQL). The live `users` schema was inspected read-only: `role` is an existing varchar and `is_active` is an existing boolean. Both are reused. No existing schema or data was reset, deleted, or migrated during implementation.

## Activate

From `C:\xampp\htdocs\dashboard\caps`, review and run the single additive migration:

```powershell
php artisan migrate --path=database/migrations/2026_09_11_000001_create_governance_tables.php --pretend
php artisan migrate --path=database/migrations/2026_09_11_000001_create_governance_tables.php
php artisan view:clear
php artisan route:clear
npm run build
```

This creates only `pending_registrations`, `admin_invitations`, and `approval_requests`. It does not change existing tables. Rollback intentionally throws an exception to preserve pending and audit records; use a reviewed forward migration for future changes. Do not use database reset or fresh-migration commands.

Assign your existing account manually, replacing the example email:

```sql
UPDATE users SET role = 'super_admin' WHERE email = 'your-existing-email@example.com';
```

This does not create an account or change its password. Use an existing active account, sign out, and sign in again. Super Admin login goes to `/governance/approvals`. Regular Admins retain their operational dashboard. The mobile API remains operational-Admin-only.

## Configuration

Keep the existing `APP_KEY`, database settings, and Supabase storage configuration. Pending registration payloads are encrypted with `APP_KEY`, with an already-hashed password inside; do not regenerate the key.

Set `APP_URL` to the externally reachable HTTPS origin used by email recipients. Configure your existing mail provider, for example:

```dotenv
APP_URL=https://your-achilles-domain.example
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_FROM_ADDRESS=verified-sender@example.com
MAIL_FROM_NAME=Achilles
```

The existing Resend configuration is also supported (`MAIL_MAILER=resend` and its existing provider key). The `log` mailer is only useful for local development; it does not deliver email. After changing environment configuration, run `php artisan config:clear` and rebuild the configuration cache if your deployment uses one.

Use a persistent, shared production cache such as the existing database cache or Redis so rate limits persist across requests/servers. Do not use the test-only array cache in production. Preserve your trusted reverse-proxy configuration and ensure clients cannot inject trusted forwarded IP headers at the public ingress.

Run Laravel's scheduler every minute in production (`php artisan schedule:run`); on a Windows development machine use:

```powershell
php artisan schedule:work
```

The hourly `registrations:prune` command removes expired temporary registrations only. It can also be run manually. Emails are sent synchronously, with a retry message on delivery failure. Approval processing does not require a queue worker.

## Behavior and limits

- Customer registration stores a normalized unique pending email, all existing personal fields, terms acceptance, and a hashed password. It sends a random 64-character token whose SHA-256 hash is stored. Links expire after 60 minutes; resubmission rotates the token and replaces the pending data. Verification creates a verified `user` in a transaction and deletes only that completed temporary registration. Already-registered emails are rejected. A live Admin invitation and a pending customer registration cannot intentionally claim the same email; `users.email` uniqueness is the final database constraint against concurrent account creation.
- Registration permits 3 attempts/minute/IP, 10/hour/IP, and 3/10 minutes/normalized email. Web login retains the scaffold's 5 failed attempts/minute using normalized email plus IP. API login permits 5 attempts/minute/email+IP and 30/minute/IP. These reduce abuse and unverified account pollution; they are not DDoS protection.
- Super Admin invitations expire after 48 hours. The invited email is fixed server-side; recipients choose their own personal details and password. Resending replaces the old token. The inviter must still be an active Super Admin when the invitation is accepted. Existing accounts cannot be invited or promoted through public account setup.
- Existing catalog creation routes now stage categories, brands, shoe types, products, variants, stock additions, and standalone product images. Validation and normalization run before staging and again on approval. The existing operational update/archive/order/POS behavior remains available only to normal Admins. Product quick-add responses use HTTP 202 with `pending`, `request_id`, and `message`; an unapproved option cannot be selected as a live category/brand/type.
- Images are staged on the existing Supabase disk under `products/pending`. No live image, product, variant, or stock row exists until approval. Approved image rows reference those staged files. Rejected proposal images are retained for audit/review; they are not attached to the live catalog. Image uploads are capped at 10 files of 5 MB each per request.
- Reviews use a transaction per request and a database lock on the oldest retained audit request to serialize competing approvals. A request cannot be approved twice or by its requester. Stock creation and the corresponding inbound stock movement commit together. Duplicate/stale proposals stay pending with a per-request error so the reviewer can reject them.
- Queue pages support 25/50/100 rows. Bulk review accepts at most 100 explicitly selected IDs, handled in groups of 25, with one transaction per item. There is no select-all-across-the-database endpoint. Review a 1,000-item queue page by page. This avoids one unbounded synchronous operation.
- Account management changes `is_active`, retains users and order history, and logs changes through the existing User activity logging. Suspension revokes mobile tokens, blocks subsequent web session access, and rejects login. Super Admins cannot change their own status or suspend another Super Admin through this interface. Normal Admins cannot manage accounts.
- Guests can browse catalog pages, details, search, filters, and sorting. Cart, checkout, profile, addresses, and orders remain authenticated routes. Protected guest links/buttons open an accessible native dialog with Login / Register / Cancel and Escape dismissal.

## Routes and authorization

| Route | Access / behavior |
| --- | --- |
| `GET /register` | Existing registration form |
| `POST /register` | Guest, rate-limited pending registration |
| `GET /registration/verify/{token}` | Expiring, single-use verification; rate-limited |
| `GET, POST /admin-invitations/{token}` | Guest invitation setup; POST rate-limited |
| `GET /governance/approvals` | Super Admin approval queue |
| `POST /governance/approvals/review` | Super Admin single/bulk review, capped at 100 IDs |
| `GET /governance/accounts` | Super Admin account list (`admin.users.index`) |
| `PATCH /governance/accounts/{user}/status` | Super Admin suspension/restoration |
| `GET, POST /governance/invitations` | Super Admin invitation form/send |
| Existing catalog browsing URLs | Public, formerly inside authenticated customer group |
| Existing catalog creation URLs | Admin-only, now create approval requests |
| `POST /api/login` | Rate-limited active operational Admin login |

Legacy `/admin/users` account editing, promotion, and direct password-based Admin creation routes are no longer registered. Their old controllers/views are retained as unused files; they are not a parallel public registration path.

`ActiveAccount` is appended to web middleware and explicitly follows Sanctum authentication in API routes. `SuperAdmin` guards governance routes. `AdminMiddleware` keeps normal Admin operations and allows Super Admin GET operational views only, excluding edit forms and POS. Server-side middleware and `ApprovalService` enforce permissions independently of button visibility. No parallel policy/role framework was introduced.

## Test

```powershell
php vendor/phpunit/phpunit/phpunit -c phpunit-governance.xml
```

This dedicated configuration forces SQLite `:memory:`, array cache/session/mail and fake image storage. Setup checks the database settings before applying migration `up()` methods to the new in-memory database; it never calls a reset/fresh-migration command. No actual email or storage upload is made by the tests.

Manual checks after activation:

1. Register a new customer with middle name/suffix and terms checked. Confirm no `users` row exists before clicking email. Verify, log in, and retry the same link (410). Re-register a pending email to invalidate its old link; try an expired link and an existing user's email.
2. Submit registration four times quickly; confirm a wait message. Submit five bad web logins, then another; confirm throttling. Repeat for `/api/login` and confirm HTTP 429.
3. As Super Admin, invite a fresh email. Open the message logged out, choose a password, and submit. Tampering with a posted email must not change the invitation address. Retry/expire the link. Try the same invitation endpoint as a regular Admin (403).
4. Submit each addition type as an Admin. Verify the live catalog/stock tables are unchanged. As Super Admin inspect details and images, approve, and confirm creation. Reject another with a reason. Select several requests, then Select All on Current Page; approve/reject selected and change page size. Try a duplicate proposal, a replay, a request whose parent was archived, and a request whose Admin was suspended.
5. Keep an Admin/customer signed in in another browser; suspend that account. Their next authenticated request must be blocked, API tokens revoked, and login rejected. Restore and log in again. Self-suspension and regular-Admin account-management attempts must fail.
6. Logged out, browse home, collections, product detail, search, filters, and sorting. Click Cart/Add to Cart; test all three dialog actions and Escape. Direct protected URL requests must redirect to login. Confirm normal Admin edits/orders/POS still work and Super Admin mutation requests return 403.

## File inventory

Created:

- `database/migrations/2026_09_11_000001_create_governance_tables.php`
- `app/Models/{PendingRegistration,AdminInvitation,ApprovalRequest}.php`
- `app/Http/Controllers/Auth/PendingRegistrationController.php`
- `app/Http/Controllers/Admin/{InvitationController,GovernanceController}.php`
- `app/Http/Middleware/{ActiveAccount,SuperAdmin}.php`
- `app/Services/ApprovalService.php`, `app/Services/AccountEmail.php`
- `app/Notifications/AccountLinkNotification.php`
- `resources/views/admin/governance/{styles,queue,accounts,invite}.blade.php`
- `resources/views/auth/invitation.blade.php`
- `resources/views/emails/account-link.blade.php`
- `resources/views/partials/guest-auth-prompt.blade.php`
- `tests/Governance/GovernanceTest.php`, `phpunit-governance.xml`, this guide

Modified:

- `app/Http/Controllers/Admin/{CategoryController,BrandController,ShoeTypeController,ProductController,ProductVariantController,StockController,ProductImageController}.php`
- `app/Http/Controllers/Auth/LoginController.php`, `app/Http/Controllers/Api/AuthController.php`
- `app/Http/Middleware/{AdminMiddleware,IsUser}.php`
- `app/Providers/AppServiceProvider.php`, `bootstrap/app.php`
- `routes/{web,api,console}.php`
- `resources/views/layouts/{app,pages,admin}.blade.php`
- `resources/views/auth/login.blade.php`, `resources/views/admin/products/index.blade.php`, `resources/views/guest/index.blade.php`
- `tests/Feature/ResponsivePagesTest.php` (removes intentionally retired Admin account pages from operational page expectations)

The separate mobile client and recommendation service are unchanged. Live provider email delivery, browser interaction, and concurrent PostgreSQL/MySQL transactions require deployment-environment smoke testing; SQLite tests do not establish production database locking performance.

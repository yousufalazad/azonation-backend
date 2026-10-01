# Azonation — backend (frozen)

> **This repo is frozen.** New work happens in `../../azonation-api` (Laravel) and
> `../../azonation-app` (Vue). Use this repo only as the reference when moving
> features (roadmap §2 parity rule). Fix something here only if it blocks the move.

Laravel 11 (PHP 8.2) + MariaDB/MySQL (XAMPP) + Sanctum + spatie/laravel-permission (teams).
Frontend is the sibling repo `../azonation-frontend` (Vue). **Read
`../azonation-frontend/docs/ROADMAP.md` first** (decisions, modules, pricing, build order)
and the newest note in `../azonation-frontend/docs/handoff/`.

## Run and check

- API: http://localhost:8000 (XAMPP). Frontend: http://localhost:5173.
- `php artisan migrate` is safe (migrations and the live DB were synced 2026-09-30). New
  schema changes always go in a new migration — never edit the DB by hand.
- `php -l <file>` after edits; `php artisan route:list --path=api/<x>` to check routes.
- Branch: `security/tenant-authorization` (not merged to `master` yet).
- Local test logins: `storage/app/local-test-accounts.txt` (git-ignored). Never commit it.

## Security rules (tenant isolation)

- Every organisation endpoint works on the **current organisation**, never `Auth::id()`
  alone. Use the trait `App\Http\Concerns\ResolvesCurrentOrg`: `orgIdOrFail()`,
  `owned(Model::class)`, `ownedVia(...)`, `ensureOwnedParent(...)`. Personal data:
  `OwnsPersonalRecords`.
- Permissions: `org.permission:<module>.<action>` middleware (reads `X-Org-Id`);
  `org.owner` for owner-only modules; `superadmin` middleware. Routes under the
  `SuperAdmin\` namespace are guarded by the loop at the end of `routes/api.php`.
  Route audit: `docs/security/route-audit.md`.
- Controllers that call `$this->middleware()` must extend `Illuminate\Routing\Controller`.
- Validate input with explicit field lists; never mass-assign request data; return
  `App\Support\ErrorDetail::for($e)` instead of raw exception messages.
- Azonation never holds organisations' money (see roadmap §4).

## Database facts

- `users` has no `name` column: organisations use `org_name`, people use `first_name` /
  `last_name`. `users.type`: individual | organisation | superadmin | guest | pending.
- Many `is_active` columns are enum('0','1') — compare with the strings `'1'` / `'0'`.
- Privacy setup id 1 = Public, 2 = Private (default new records to Private).
- Membership renewal fees are stored in minor units (÷100).
- No stored procedures (roadmap §2).

## Shared services

- Billing: `App\Services\Billing\BillingService` (monthly bills → draft invoices → publish →
  recordPayment). Schedule in `routes/console.php` (needs cron `schedule:run`).
  Pricing is moving to member bands — roadmap §5.
- Families: `App\Services\MemberFamilies` (only current members' shared data).
- Public plans for the Pricing page: `Common\PublicPlanController` (`/api/public/plans`).

## Do not

- Do not add backup copies of files (`* copy.php`, dated names): they re-declare live classes.
  Git keeps history.
- Do not touch the shop (`Ecommerce` controllers) until its roadmap step.

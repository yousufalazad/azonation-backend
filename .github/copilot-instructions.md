# Copilot Instructions for Azonation Backend

## Project Overview
**Azonation** is a Laravel 11 API-driven platform serving both individual users and organizations with features for membership management, events, projects, committees, e-commerce, financial accounts, and payment processing across multiple global gateways.

## Architecture Patterns

### Domain-Driven Directory Structure
Controllers are organized by domain boundaries (`app/Http/Controllers/`):
- **Auth/** - User registration, login, OAuth (Google/social), token management via Sanctum
- **Common/** - Shared cross-domain: Address, PhoneNumber, Notification, Referral, UserCountry
- **Individual/** - Individual user profiles and personal features
- **Org/** - Organization-specific features split into subdomains:
  - `Membership/` - Member lifecycle (OrgMember, MembershipType, MembershipTermination, OrgMembershipRenewal)
  - `Event/`, `Project/`, `Meeting/` - Activity management with attendances, guests, summaries
  - `Committee/`, `Founder/` - Governance structures
  - `Asset/`, `Accounts/` - Resource and financial tracking
  - `OfficeDocument/`, `StrategicPlan/`, `YearPlan/` - Planning artifacts
- **Ecommerce/** - Product catalogs, cart, orders, attributes (nested Category/SubCategory hierarchy)
- **PaymentGateway/** - Multi-gateway abstraction (Stripe, PayPal, Razorpay, SSL Commerz, Alipay, WeChat Pay, 2Checkout, AuthorizeNet, Square, Paytm, BKash, Tap, manual payments)
- **SuperAdmin/** - Admin settings (Countries, Currencies, Designations, AttendanceTypes, etc.)

### Database Schema
**User Model** (`app/Models/User.php`):
- Core authentication with Sanctum tokens (`HasApiTokens`, `Notifiable`)
- Fields: `azon_id`, `type` (individual|organisation), `email_verified_at`, `oauth_provider`, `registration_completed`
- Relationships: ProfileImage, Address, PhoneNumber, UserCountry, ManagementSubscription, StorageSubscription

**File/Image Pattern** (ubiquitous across domain models):
Every major entity has parallel models for media:
- `EventImage`/`EventFile`, `ProjectImage`/`ProjectFile`, `MeetingImage`/`MeetingFile`
- `StrategicPlanImage`/`StrategicPlanFile`, `SuccessStoryImage`/`SuccessStoryFile`, etc.
- Stored via `Storage::disk('public')` with timestamp-prefixed filenames: `YmdHis_originalname.ext`
- Models use `$hidden = ['created_at', 'updated_at']` pattern

**Organization Models** (`app/Models/Org*`):
- `OrgProfile` - Org description, mission, vision, values
- `OrgMember` - Members linked to org with status/type tracking
- `OrgMembershipRenewal` - Renewal dates and lifecycle
- `OrgIndependentMember` - Non-member participants
- `OrgAccount` - Financial accounts per org

**Payment Models** (per gateway):
- Gateway-specific: `StripePayment`, `RazorpayPayment`, etc. each with unique fields
- Central `Payment` model tracks transactions across all gateways
- `PaymentWebhookLogs` for audit trail
- `GatewayCredential` stores encrypted API keys per region/gateway

### API Response Format
Controllers use consistent response structure via `success()` helper:
```php
return response()->json([
    'status' => 'success',
    'message' => $message,
    'data' => $data
], $status);
```

### Subscription Model
- **ManagementSubscription** - Org access to platform (required for org type at registration)
- **StorageSubscription** - Per-org storage limits with daily billing tracking
- **ManagementPackage/StoragePackage** - Pricing tiers
- Models auto-created at registration: `AuthController@register()` creates `ManagementSubscription` for orgs

## Critical Developer Workflows

### Testing
Run tests with PHPUnit:
```bash
php artisan test                    # All tests
php artisan test tests/Feature      # Feature tests only
php artisan test tests/Unit         # Unit tests only
php artisan test --filter=TestName  # Specific test
```
Test environment configured in `phpunit.xml` uses:
- In-memory session, array cache, sync queue
- Test-specific `.env` settings: `APP_ENV=testing`, `MAIL_MAILER=array`

### Database Migrations
~160+ migrations in `database/migrations/` with clear naming:
- `create_*_table.php` for new entities
- Sequential timestamps (e.g., `2025_01_11_112140_create_founder_profile_images_table.php`)
- Run: `php artisan migrate` (auto-discovers in laravel)

### Mail & Queues
- Mail classes in `app/Mail/` (IndividualUserRegisteredMail, OrgUserRegisteredMail, PasswordResetCodeMail)
- Jobs in `app/Jobs/` (e.g., SendWelcomeEmail)
- Queue config in `config/queue.php`, currently set to `sync` for immediate execution
- Mail sent via `Mail::send()` during registration flows

### Authentication & Authorization
- **Sanctum** for API tokens: `User` has `HasApiTokens` trait
- **OAuth2** via Socialite: Google login stores `google_id`, `oauth_provider`, `oauth_refresh_token`
- **Session Auth** for web routes (see `routes/web.php`)
- No explicit role/permission system visible—assume role checking via `User::type` field

### Frontend Integration
- **Vite** configured for asset bundling (`vite.config.js`)
- **CORS** in `config/cors.php`
- **Frontend URL** configurable: `config/app.php` sets `frontend_url` from `.env` (default: `http://localhost:5173`)

## Project-Specific Patterns

### User Registration Flow
(`AuthController@register`):
1. Validate input (email uniqueness, password strength)
2. Hash password, create User with `registration_completed = true`
3. Create **UserCountry** association (tracks geographic context)
4. Auto-create **ManagementSubscription** for org type (mandatory)
5. Auto-create **StorageSubscription** for org type (with daily billing)
6. Send type-specific welcome emails (IndividualUserRegisteredMail, OrgUserRegisteredMail)
7. Handle optional referral codes for ReferralReward tracking

### Referral System
- **ReferralCode** - Unique codes issued to users
- **Referral** - Links referrer to referred user
- **ReferralReward** - Tracks earned rewards
- Referrals checked at registration if `referral` param provided

### Billing Architecture
- **ManagementAndStorageBilling** - Monthly bill combining both subscriptions
- **EverydayStorageBilling** - Daily storage usage tracking
- **EverydayMemberCountAndBilling** - Daily member count metrics
- Billing tied to **RegionCurrency** for multi-currency support

### Payment Gateway Integration
Multi-gateway abstraction pattern:
- **PaymentGatewayController** - Lists available gateways
- **GatewayCredential** - Per-region credentials (encrypted API keys)
- **RegionGatewayDefault** - Region → preferred gateway mapping
- Webhook handlers in respective controllers (e.g., `StripePaymentController@webhook()`)
- All transactions logged to **Payment** model + gateway-specific models

### Membership Lifecycle (Org-specific)
- **MembershipType** - Membership categories
- **OrgMembershipType** - Org-customized types
- **MembershipStatus** - Approved, Pending, Terminated, etc.
- **OrgMembershipStatusHistory** / **OrgMembershipStatusLog** - Audit trail
- **MembershipRenewalCycle** - Annual renewal periods
- **OrgMembershipRenewal** - Tracks renewal dates
- **OrgMembershipRenewalPrice** - Configurable per org/cycle
- **MembershipTermination** - Exits with reason tracking

### Event/Project/Meeting Pattern
Parallel structure for three activity types with consistent features:
- **Entities**: Event, Project, Meeting
- **Attendance**: EventAttendance, ProjectAttendance, MeetingAttendance
- **Guests**: EventGuestAttendance, ProjectGuestAttendance, MeetingGuestAttendance
- **Dignitaries**: EventDignitary, ProjectDignitary, MeetingDignitary
- **Summaries**: EventSummary, ProjectSummary + images/files
- **Images/Files**: Paired models for each (EventImage/EventFile, etc.)
- Attendance types configurable via SuperAdmin (AttendanceType model)

### File/Image Management
Standard pattern across all features:
```php
// In controllers:
$request->validate(['image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:20048']);
$timestamp = Carbon::now()->format('YmdHis');
$newFileName = $timestamp . '_' . $originalName . '.' . $extension;
$path = $image->storeAs('feature/subfolder', $newFileName, 'public');
// Store path in model and return Storage::url($path)
```
Disk: `public` (see `config/filesystems.php`)

## Integration Points & Dependencies

### External Services
- **Stripe API** - Payment processing
- **PayPal, Razorpay, SSL Commerz, Square** - Multi-gateway support
- **Social OAuth** - Google via Socialite
- **Regional Tax Rates** - Multi-region/currency tax calculation
- **Email** - Transactional via Mail facade (configurable SMTP/Mailgun)

### Key External Packages
- `laravel/sanctum` - API authentication
- `laravel/socialite` - OAuth2 integration
- `stripe/stripe-php` - Stripe SDK (also PayPal, Razorpay, etc.)
- `guzzlehttp/guzzle` - HTTP client for gateway APIs
- `laravel/tinker` - REPL for quick debugging

### Configuration Files
- `config/app.php` - App name, timezone, frontend URL
- `config/database.php` - DB connection (likely MySQL)
- `config/auth.php` - Sanctum guards, User provider
- `config/mail.php` - Email driver (SMTP/array for testing)
- `config/queue.php` - Queue connection (sync for now)
- `config/cors.php` - Cross-origin policy

## Common Commands

```bash
php artisan migrate              # Run pending migrations
php artisan migrate:fresh        # Wipe & migrate (dev only)
php artisan tinker              # Interactive REPL
php artisan cache:clear         # Clear cache
php artisan config:cache        # Cache config
php artisan route:list          # Show all routes
php artisan test                # Run PHPUnit
composer install                # Install dependencies
npm install && npm run build    # Build Vite assets
```

## Critical Notes for New Contributors

1. **Always use Eloquent relationships** - Models define relations; use `$model->relation()->get()` not manual joins
2. **Validate early** - All controller methods start with `$request->validate()`
3. **File storage pattern** - Always timestamp-prefix files to avoid collisions
4. **Hidden fields** - Models hide timestamps by default; expose only needed data
5. **User context** - Most features require `Auth::id()` or `Auth::user()`; check auth in controllers
6. **Region/currency context** - Track **UserCountry** and **RegionCurrency** for multi-region support
7. **Org vs Individual** - Check `User::type` early; org features require org context
8. **Billing impact** - Storage/member changes trigger **EverydayStorageBilling** or **EverydayMemberCountAndBilling**
9. **Payment webhooks** - Implement idempotency checks (duplicate webhook handling)
10. **Test with fixtures** - Use factories in `database/factories/` for test data

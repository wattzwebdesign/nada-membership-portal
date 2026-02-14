# NADA Platform — Complete Technical Specification

> **Version:** 1.0
> **Date:** February 13, 2026
> **Client:** National Acupuncture Detoxification Association (NADA)
> **Deliverable:** Full membership platform replacing existing WordPress/Voxel setup

---

## Table of Contents

1. [Project Overview & Goals](#1-project-overview--goals)
2. [Tech Stack](#2-tech-stack)
3. [User Roles & Permissions](#3-user-roles--permissions)
4. [Database Schema](#4-database-schema)
5. [Stripe Integration & Migration](#5-stripe-integration--migration)
6. [Membership Plans & Pricing](#6-membership-plans--pricing)
7. [Discount Approval Workflow](#7-discount-approval-workflow)
8. [Certificate System](#8-certificate-system)
9. [Training Management](#9-training-management)
10. [Clinicals Submission](#10-clinicals-submission)
11. [Member/Trainer Portal (Blade + Livewire)](#11-membertrainer-portal-blade--livewire)
12. [Admin Panel (Filament)](#12-admin-panel-filament)
13. [API Routes & Endpoints](#13-api-routes--endpoints)
14. [UI/UX Flows](#14-uiux-flows)
15. [Migration Strategy](#15-migration-strategy)
16. [Email Notifications](#16-email-notifications)
17. [Stripe Webhooks](#17-stripe-webhooks)
18. [Verification & Testing](#18-verification--testing)

---

## 1. Project Overview & Goals

### Organization Background

The National Acupuncture Detoxification Association (NADA) is a professional organization that certifies Acupuncture Detox Specialists. Members complete training programs led by Registered Trainers, submit clinical documentation, and receive certificates upon successful completion. NADA currently manages ~1,300 active memberships through Stripe with a WordPress/Voxel front-end that has outgrown its purpose.

### Core Problem

The current WordPress/Voxel platform cannot adequately handle:
- Complex membership plan management across multiple Stripe products
- Trainer-hosted training workflows with payment splitting
- Certificate generation tied to membership expiration
- Discount approval workflows for students and seniors
- Trainer payout management via Stripe Connect
- Mobile-friendly member/trainer self-service

### Objectives

1. **Migrate** all ~1,300 existing Stripe subscriptions and certificate records without disruption
2. **Centralize** membership management, training, certificates, and billing into a single Laravel platform
3. **Enable** trainers to host paid/free trainings with Stripe Connect payouts
4. **Automate** certificate generation tied to training completion and membership status
5. **Streamline** discount approval workflows via email-based approve/deny
6. **Deliver** a mobile-first, responsive member and trainer portal
7. **Provide** a full-featured Filament admin panel for NADA staff

---

## 2. Tech Stack

| Layer | Technology |
|---|---|
| **Backend Framework** | Laravel 11+ (PHP 8.2+) |
| **Database** | MySQL 8.0+ |
| **Admin Panel** | Filament 3.x |
| **Member/Trainer Portal** | Blade templates + Livewire 3.x |
| **CSS Framework** | Tailwind CSS 3.x (mobile-first) |
| **Payments** | Stripe PHP SDK + Stripe Connect |
| **PDF Generation** | DomPDF (via `barryvdh/laravel-dompdf`) with Browsershot fallback |
| **File Storage** | Laravel Filesystem (local or S3-compatible) |
| **Email** | Laravel Mail (SMTP / Mailgun / SES — configurable) |
| **Authentication** | Laravel Breeze or Fortify (session-based) |
| **Queue** | Laravel Queue (database or Redis driver) |
| **Hosting** | Client's own server |

### Key Packages

```
composer require laravel/framework
composer require filament/filament:"^3.0"
composer require livewire/livewire:"^3.0"
composer require stripe/stripe-php
composer require barryvdh/laravel-dompdf
composer require maatwebsite/excel  # for CSV/Excel imports during migration
composer require spatie/laravel-medialibrary  # for file uploads
composer require spatie/laravel-permission  # for role/permission management
```

---

## 3. User Roles & Permissions

### Roles

| Role | Description |
|---|---|
| **member** | Standard NADA member. Can view dashboard, manage billing, download certificates, register for trainings, submit clinicals. |
| **registered_trainer** | Elevated member. Has all member capabilities PLUS can create/manage trainings, mark attendees as complete, view payout reports, connect Stripe account. |
| **admin** | NADA staff. Full access to Filament admin panel. Manages all users, plans, subscriptions, discount requests, payout configs, certificates, trainings. |

### Permission Matrix

| Permission | Member | Trainer | Admin |
|---|---|---|---|
| View own dashboard | ✅ | ✅ | ✅ |
| Manage own billing | ✅ | ✅ | ✅ |
| Download own certificates | ✅ | ✅ | ✅ |
| Register for trainings | ✅ | ✅ | ✅ |
| Submit clinicals | ✅ | ✅ | ✅ |
| Request discount | ✅ | ✅ | ✅ |
| Create/edit trainings | ❌ | ✅ | ✅ |
| Mark training attendees complete | ❌ | ✅ | ✅ |
| View training attendee lists | ❌ | ✅ | ✅ |
| View payout/money reports | ❌ | ✅ | ✅ |
| Connect Stripe account | ❌ | ✅ | ✅ |
| Access Filament admin | ❌ | ❌ | ✅ |
| Approve/deny discount requests | ❌ | ❌ | ✅ |
| Manage all users | ❌ | ❌ | ✅ |
| Configure payout percentages | ❌ | ❌ | ✅ |
| Issue/revoke certificates | ❌ | ❌ | ✅ |
| Manage plans/prices | ❌ | ❌ | ✅ |

### Role Upgrade Workflow (Member → Registered Trainer)

1. Member navigates to "Upgrade to Registered Trainer" in their portal
2. Member fills out trainer application form (credentials, experience, etc.)
3. Application is submitted and status set to `pending`
4. Admin receives email notification of new trainer application
5. Admin reviews in Filament panel → approves or denies
6. **On approval:** User's role is updated to `registered_trainer`, member is switched to a Registered Trainer plan in Stripe, and trainer-specific portal sections become visible
7. **On denial:** Member is notified via email with optional reason
8. Member's existing certificate codes and history are preserved through the upgrade

---

## 4. Database Schema

### 4.1 `users`

The central user table. All roles (member, trainer, admin) are stored here.

```
users
├── id                          BIGINT UNSIGNED, PK, AUTO_INCREMENT
├── first_name                  VARCHAR(255), NOT NULL
├── last_name                   VARCHAR(255), NOT NULL
├── email                       VARCHAR(255), UNIQUE, NOT NULL
├── email_verified_at           TIMESTAMP, NULLABLE
├── password                    VARCHAR(255), NOT NULL
├── phone                       VARCHAR(50), NULLABLE
├── address_line_1              VARCHAR(255), NULLABLE
├── address_line_2              VARCHAR(255), NULLABLE
├── city                        VARCHAR(255), NULLABLE
├── state                       VARCHAR(255), NULLABLE
├── zip                         VARCHAR(20), NULLABLE
├── country                     VARCHAR(2), DEFAULT 'US'
├── stripe_customer_id          VARCHAR(255), NULLABLE, INDEX
├── discount_type               ENUM('none','student','senior'), DEFAULT 'none'
├── discount_approved           BOOLEAN, DEFAULT FALSE
├── discount_approved_at        TIMESTAMP, NULLABLE
├── discount_approved_by        BIGINT UNSIGNED, NULLABLE, FK → users.id
├── trainer_application_status  ENUM('none','pending','approved','denied'), DEFAULT 'none'
├── trainer_approved_at         TIMESTAMP, NULLABLE
├── trainer_approved_by         BIGINT UNSIGNED, NULLABLE, FK → users.id
├── profile_photo_path          VARCHAR(255), NULLABLE
├── remember_token              VARCHAR(100), NULLABLE
├── created_at                  TIMESTAMP
├── updated_at                  TIMESTAMP
├── deleted_at                  TIMESTAMP, NULLABLE (soft deletes)
```

**Indexes:** `email`, `stripe_customer_id`, `discount_type`, `trainer_application_status`

**Notes:** Roles are managed via Spatie `model_has_roles` pivot table. A user can have `member`, `registered_trainer`, and/or `admin` roles.

---

### 4.2 `plans`

Local representation of Stripe Products + Prices. Each row = one purchasable plan.

```
plans
├── id                      BIGINT UNSIGNED, PK, AUTO_INCREMENT
├── name                    VARCHAR(255), NOT NULL
│                           e.g. "NADA Membership — 1 Year", "Registered Trainer — 2 Year"
├── slug                    VARCHAR(255), UNIQUE, NOT NULL
├── description             TEXT, NULLABLE
├── stripe_product_id       VARCHAR(255), NOT NULL, INDEX
│                           e.g. "prod_XXXXX"
├── stripe_price_id         VARCHAR(255), UNIQUE, NOT NULL, INDEX
│                           e.g. "price_1PNONJDPaQVax0KGn0t246Uo"
├── price_cents             INTEGER UNSIGNED, NOT NULL
│                           e.g. 10000 = $100.00
├── currency                VARCHAR(3), DEFAULT 'usd'
├── billing_interval        ENUM('month','year'), DEFAULT 'year'
├── billing_interval_count  TINYINT UNSIGNED, DEFAULT 1
│                           1 = yearly, 2 = every 2 years, 3 = every 3 years
├── plan_type               ENUM('membership','trainer','senior','student','comped'), NOT NULL
├── role_required           ENUM('member','registered_trainer'), NULLABLE
│                           If set, only users with this role can subscribe
├── discount_required       ENUM('student','senior'), NULLABLE
│                           If set, only approved discount users see this plan
├── is_visible              BOOLEAN, DEFAULT TRUE
│                           FALSE for comped/manually-assigned plans
├── is_active               BOOLEAN, DEFAULT TRUE
│                           FALSE to hide from new signups while honoring existing
├── sort_order              INTEGER, DEFAULT 0
├── created_at              TIMESTAMP
├── updated_at              TIMESTAMP
```

**Indexes:** `stripe_product_id`, `stripe_price_id`, `plan_type`, `is_visible`, `is_active`

---

### 4.3 `subscriptions`

Tracks each user's Stripe subscription locally.

```
subscriptions
├── id                          BIGINT UNSIGNED, PK, AUTO_INCREMENT
├── user_id                     BIGINT UNSIGNED, NOT NULL, FK → users.id, INDEX
├── plan_id                     BIGINT UNSIGNED, NOT NULL, FK → plans.id, INDEX
├── stripe_subscription_id      VARCHAR(255), UNIQUE, NOT NULL, INDEX
├── stripe_price_id             VARCHAR(255), NOT NULL, INDEX
├── status                      ENUM('active','past_due','canceled','incomplete',
│                                    'incomplete_expired','trialing','unpaid','paused'),
│                               NOT NULL, DEFAULT 'active'
├── current_period_start        TIMESTAMP, NULLABLE
├── current_period_end          TIMESTAMP, NULLABLE
│                               This is the membership expiration date
├── cancel_at_period_end        BOOLEAN, DEFAULT FALSE
├── canceled_at                 TIMESTAMP, NULLABLE
├── trial_ends_at               TIMESTAMP, NULLABLE
├── metadata                    JSON, NULLABLE
│                               Store any extra Stripe metadata
├── created_at                  TIMESTAMP
├── updated_at                  TIMESTAMP
```

**Indexes:** `user_id`, `plan_id`, `stripe_subscription_id`, `stripe_price_id`, `status`

**Business Rules:**
- A user should have at most ONE active subscription at a time
- `current_period_end` determines certificate expiration date
- When status changes to `canceled`, certificates should show as expired

---

### 4.4 `certificates`

Each certificate issued to a member after training completion.

```
certificates
├── id                      BIGINT UNSIGNED, PK, AUTO_INCREMENT
├── user_id                 BIGINT UNSIGNED, NOT NULL, FK → users.id, INDEX
├── certificate_code        VARCHAR(100), UNIQUE, NOT NULL, INDEX
│                           Existing codes from migration MUST be preserved
├── training_id             BIGINT UNSIGNED, NULLABLE, FK → trainings.id
│                           The training that triggered this certificate
├── issued_by               BIGINT UNSIGNED, NULLABLE, FK → users.id
│                           The trainer or admin who issued it
├── date_issued             DATE, NOT NULL
├── expiration_date         DATE, NULLABLE
│                           Synced from subscription.current_period_end
├── status                  ENUM('active','expired','revoked'), DEFAULT 'active'
├── pdf_path                VARCHAR(255), NULLABLE
│                           Path to generated PDF file
├── metadata                JSON, NULLABLE
│                           For any legacy data from migration
├── created_at              TIMESTAMP
├── updated_at              TIMESTAMP
```

**Indexes:** `user_id`, `certificate_code`, `training_id`, `status`

**Business Rules:**
- `certificate_code` is the NADA ID# shown on the certificate
- Existing certificate codes from the WordPress system MUST be imported as-is
- New certificates get auto-generated codes (format: `NADA-YYYYMMDD-XXXXX` or similar unique pattern)
- `expiration_date` is updated whenever the user's subscription renews
- Status automatically becomes `expired` when `expiration_date` passes
- Admin can manually `revoke` a certificate

---

### 4.5 `trainings`

Trainings created and managed by Registered Trainers.

```
trainings
├── id                      BIGINT UNSIGNED, PK, AUTO_INCREMENT
├── trainer_id              BIGINT UNSIGNED, NOT NULL, FK → users.id, INDEX
│                           The trainer who created/hosts this training
├── title                   VARCHAR(255), NOT NULL
├── description             TEXT, NULLABLE
├── type                    ENUM('in_person','virtual','hybrid'), NOT NULL
├── location_name           VARCHAR(255), NULLABLE
│                           For in-person/hybrid: venue name
├── location_address        VARCHAR(500), NULLABLE
├── virtual_link            VARCHAR(500), NULLABLE
│                           For virtual/hybrid: Zoom/meeting link
├── start_date              DATETIME, NOT NULL
├── end_date                DATETIME, NOT NULL
├── timezone                VARCHAR(50), DEFAULT 'America/New_York'
├── max_attendees           INTEGER UNSIGNED, NULLABLE
│                           NULL = unlimited
├── is_paid                 BOOLEAN, DEFAULT FALSE
├── price_cents             INTEGER UNSIGNED, DEFAULT 0
│                           In cents. 0 = free (sponsored)
├── currency                VARCHAR(3), DEFAULT 'usd'
├── stripe_price_id         VARCHAR(255), NULLABLE
│                           If paid, the Stripe Price for registration
├── status                  ENUM('draft','published','canceled','completed'), DEFAULT 'draft'
├── created_at              TIMESTAMP
├── updated_at              TIMESTAMP
├── deleted_at              TIMESTAMP, NULLABLE (soft deletes)
```

**Indexes:** `trainer_id`, `status`, `start_date`, `is_paid`

---

### 4.6 `training_registrations`

Pivot table linking members to trainings they've registered for.

```
training_registrations
├── id                      BIGINT UNSIGNED, PK, AUTO_INCREMENT
├── training_id             BIGINT UNSIGNED, NOT NULL, FK → trainings.id, INDEX
├── user_id                 BIGINT UNSIGNED, NOT NULL, FK → users.id, INDEX
├── status                  ENUM('registered','attended','completed','no_show','canceled'),
│                           DEFAULT 'registered'
├── completed_at            TIMESTAMP, NULLABLE
│                           Set when trainer marks as complete
├── marked_complete_by      BIGINT UNSIGNED, NULLABLE, FK → users.id
├── stripe_payment_intent_id VARCHAR(255), NULLABLE
│                           If training was paid
├── amount_paid_cents       INTEGER UNSIGNED, DEFAULT 0
├── certificate_id          BIGINT UNSIGNED, NULLABLE, FK → certificates.id
│                           Linked once certificate is generated
├── created_at              TIMESTAMP
├── updated_at              TIMESTAMP

UNIQUE INDEX: (training_id, user_id)
```

---

### 4.7 `clinicals`

Clinical submissions from members/trainees.

```
clinicals
├── id                      BIGINT UNSIGNED, PK, AUTO_INCREMENT
├── user_id                 BIGINT UNSIGNED, NULLABLE, FK → users.id
│                           If submitted by a logged-in member
├── first_name              VARCHAR(255), NOT NULL
├── last_name               VARCHAR(255), NOT NULL
├── email                   VARCHAR(255), NOT NULL
├── estimated_training_date DATE, NULLABLE
├── trainer_id              BIGINT UNSIGNED, NOT NULL, FK → users.id, INDEX
│                           Selected trainer from dropdown
├── status                  ENUM('submitted','under_review','approved','rejected'),
│                           DEFAULT 'submitted'
├── reviewed_by             BIGINT UNSIGNED, NULLABLE, FK → users.id
├── reviewed_at             TIMESTAMP, NULLABLE
├── notes                   TEXT, NULLABLE
├── created_at              TIMESTAMP
├── updated_at              TIMESTAMP
```

**File uploads** are handled via Spatie Media Library attached to the `clinicals` model:
- Treatment log files: PDF, images (JPG, PNG), Word docs (DOC, DOCX)
- Max file size: 10MB per file
- Multiple files allowed per submission

---

### 4.8 `discount_requests`

Tracks student/senior discount requests and their approval status.

```
discount_requests
├── id                      BIGINT UNSIGNED, PK, AUTO_INCREMENT
├── user_id                 BIGINT UNSIGNED, NOT NULL, FK → users.id, INDEX
├── discount_type           ENUM('student','senior'), NOT NULL
├── status                  ENUM('pending','approved','denied'), DEFAULT 'pending'
├── proof_description       TEXT, NULLABLE
│                           User's explanation / proof details
├── admin_notes             TEXT, NULLABLE
├── reviewed_by             BIGINT UNSIGNED, NULLABLE, FK → users.id
├── reviewed_at             TIMESTAMP, NULLABLE
├── approval_token          VARCHAR(255), UNIQUE, NULLABLE
│                           Unique token for email-based approve/deny links
├── token_expires_at        TIMESTAMP, NULLABLE
├── created_at              TIMESTAMP
├── updated_at              TIMESTAMP
```

**File uploads** via Spatie Media Library: student ID, senior documentation, etc.

---

### 4.9 `payout_settings`

Global and per-trainer payout configuration for Stripe Connect.

```
payout_settings
├── id                      BIGINT UNSIGNED, PK, AUTO_INCREMENT
├── trainer_id              BIGINT UNSIGNED, NULLABLE, FK → users.id, UNIQUE
│                           NULL = global default settings
├── platform_percentage     DECIMAL(5,2), NOT NULL, DEFAULT 20.00
│                           Platform's cut (e.g., 20.00 = 20%)
├── trainer_percentage      DECIMAL(5,2), NOT NULL, DEFAULT 80.00
│                           Trainer's payout (e.g., 80.00 = 80%)
├── is_active               BOOLEAN, DEFAULT TRUE
├── notes                   TEXT, NULLABLE
├── created_at              TIMESTAMP
├── updated_at              TIMESTAMP
```

**Business Rules:**
- One row with `trainer_id = NULL` is the global default
- Per-trainer rows override the global default for that specific trainer
- `platform_percentage + trainer_percentage` MUST equal 100.00

---

### 4.10 `stripe_accounts`

Stores Stripe Connect account details for trainers.

```
stripe_accounts
├── id                          BIGINT UNSIGNED, PK, AUTO_INCREMENT
├── user_id                     BIGINT UNSIGNED, NOT NULL, FK → users.id, UNIQUE
├── stripe_connect_account_id   VARCHAR(255), UNIQUE, NOT NULL
│                               e.g. "acct_XXXXX"
├── onboarding_complete         BOOLEAN, DEFAULT FALSE
├── charges_enabled             BOOLEAN, DEFAULT FALSE
├── payouts_enabled             BOOLEAN, DEFAULT FALSE
├── default_currency            VARCHAR(3), DEFAULT 'usd'
├── details_submitted           BOOLEAN, DEFAULT FALSE
├── created_at                  TIMESTAMP
├── updated_at                  TIMESTAMP
```

---

### 4.11 `invoices`

Cached Stripe invoice records for displaying in the portal.

```
invoices
├── id                      BIGINT UNSIGNED, PK, AUTO_INCREMENT
├── user_id                 BIGINT UNSIGNED, NOT NULL, FK → users.id, INDEX
├── stripe_invoice_id       VARCHAR(255), UNIQUE, NOT NULL, INDEX
├── stripe_subscription_id  VARCHAR(255), NULLABLE, INDEX
├── number                  VARCHAR(255), NULLABLE
│                           Stripe invoice number (e.g. "CJZWITIQ-0001")
├── status                  VARCHAR(50), NOT NULL
│                           draft, open, paid, uncollectible, void
├── amount_due_cents        INTEGER, NOT NULL
├── amount_paid_cents       INTEGER, NOT NULL
├── currency                VARCHAR(3), DEFAULT 'usd'
├── period_start            TIMESTAMP, NULLABLE
├── period_end              TIMESTAMP, NULLABLE
├── paid_at                 TIMESTAMP, NULLABLE
├── hosted_invoice_url      VARCHAR(500), NULLABLE
├── invoice_pdf_url         VARCHAR(500), NULLABLE
├── created_at              TIMESTAMP
├── updated_at              TIMESTAMP
```

---

### 4.12 `trainer_applications`

Dedicated table for member-to-trainer upgrade applications.

```
trainer_applications
├── id                      BIGINT UNSIGNED, PK, AUTO_INCREMENT
├── user_id                 BIGINT UNSIGNED, NOT NULL, FK → users.id, INDEX
├── credentials             TEXT, NULLABLE
├── experience_description  TEXT, NULLABLE
├── license_number          VARCHAR(255), NULLABLE
├── status                  ENUM('pending','approved','denied'), DEFAULT 'pending'
├── reviewed_by             BIGINT UNSIGNED, NULLABLE, FK → users.id
├── reviewed_at             TIMESTAMP, NULLABLE
├── admin_notes             TEXT, NULLABLE
├── created_at              TIMESTAMP
├── updated_at              TIMESTAMP
```

---

### 4.13 `activity_log` (optional, via Spatie Activity Log)

```
activity_log
├── id                      BIGINT UNSIGNED, PK, AUTO_INCREMENT
├── log_name                VARCHAR(255), NULLABLE
├── description             TEXT, NOT NULL
├── subject_type            VARCHAR(255), NULLABLE
├── subject_id              BIGINT UNSIGNED, NULLABLE
├── causer_type             VARCHAR(255), NULLABLE
├── causer_id               BIGINT UNSIGNED, NULLABLE
├── properties              JSON, NULLABLE
├── created_at              TIMESTAMP
├── updated_at              TIMESTAMP
```

---

### Entity Relationship Summary

```
users ─┬── has many ──→ subscriptions
       ├── has many ──→ certificates
       ├── has many ──→ trainings (as trainer)
       ├── has many ──→ training_registrations (as attendee)
       ├── has many ──→ clinicals (as submitter)
       ├── has many ──→ discount_requests
       ├── has one  ──→ stripe_accounts
       ├── has one  ──→ payout_settings (per-trainer override)
       ├── has many ──→ invoices
       └── has many ──→ trainer_applications

plans ──── has many ──→ subscriptions

trainings ─┬── belongs to ──→ users (trainer)
            └── has many    ──→ training_registrations

training_registrations ─┬── belongs to ──→ trainings
                        ├── belongs to ──→ users (attendee)
                        └── has one    ──→ certificates

clinicals ──── belongs to ──→ users (trainer selected)
```

---

## 5. Stripe Integration & Migration

### 5.1 Existing Stripe Structure

Based on the current Stripe dashboard:

| Stripe Product | # of Price Objects | Product Type |
|---|---|---|
| Registered Trainer | 7 prices | Trainer membership plans |
| NADA Membership | 9 prices | Standard membership plans |
| Senior ADS Subscription Fee | 3 prices | Senior discount plans |
| Comped NADA Membership | 1 price ($0.00) | Admin-assigned free membership |
| NADA Membership (duplicate) | 2 prices | Legacy/secondary membership |
| **Total** | **~22 prices** | |

**Active subscriptions:** ~1,300 recurring subscriptions across all products.

### 5.2 Stripe Data Stored Locally

For each user:
- `users.stripe_customer_id` → Stripe Customer ID (e.g., `cus_XXXXX`)

For each plan:
- `plans.stripe_product_id` → Stripe Product ID (e.g., `prod_XXXXX`)
- `plans.stripe_price_id` → Stripe Price ID (e.g., `price_XXXXX`)

For each subscription:
- `subscriptions.stripe_subscription_id` → Stripe Subscription ID (e.g., `sub_XXXXX`)
- `subscriptions.stripe_price_id` → The price the user is subscribed to

For each trainer (Stripe Connect):
- `stripe_accounts.stripe_connect_account_id` → Connect Account ID (e.g., `acct_XXXXX`)

### 5.3 Stripe API Operations

#### Subscription Management (Member-Facing)

| Operation | Stripe API Call | Notes |
|---|---|---|
| New subscription | `Subscription::create()` | After user selects plan |
| Update payment method | `PaymentMethod::attach()` + `Customer::update()` | Update default payment method |
| Switch plan | `Subscription::update()` with new `price` | Prorate by default |
| Cancel (end of period) | `Subscription::update(['cancel_at_period_end' => true])` | Access continues until period end |
| Cancel immediately | `Subscription::cancel()` | Immediate termination |
| Reactivate | `Subscription::update(['cancel_at_period_end' => false])` | If within current period |
| View invoices | `Invoice::all(['customer' => $customerId])` | Paginated list |

#### Stripe Connect (Trainer Payouts)

| Operation | Stripe API Call | Notes |
|---|---|---|
| Create Connect account | `Account::create(['type' => 'express'])` | Express accounts for simplicity |
| Generate onboarding link | `AccountLink::create()` | Redirect trainer to Stripe onboarding |
| Check account status | `Account::retrieve()` | Verify `charges_enabled`, `payouts_enabled` |
| Create payment for training | `PaymentIntent::create()` with `transfer_data` | Split payment between platform and trainer |
| Create transfer | `Transfer::create()` | For manual/deferred payouts |
| View trainer balance | `Balance::retrieve()` on connected account | Show in trainer dashboard |

#### Payout Split Logic

```
When a member pays for a training:
1. PaymentIntent is created with application_fee_amount
2. application_fee_amount = total * (platform_percentage / 100)
3. Remaining amount goes to trainer's connected account
4. Uses per-trainer payout_settings if exists, else global default
```

### 5.4 Stripe Configuration

```env
STRIPE_KEY=pk_live_XXXXX
STRIPE_SECRET=sk_live_XXXXX
STRIPE_WEBHOOK_SECRET=whsec_XXXXX
STRIPE_CONNECT_WEBHOOK_SECRET=whsec_XXXXX
```

---

## 6. Membership Plans & Pricing

### 6.1 Plan Categories

#### Standard Membership Plans (`plan_type: membership`)
- NADA Membership — 1 Year
- NADA Membership — 2 Year
- NADA Membership — 3 Year

#### Student Discount Plans (`plan_type: student`, `discount_required: student`)
- NADA Student Membership — 1 Year
- NADA Student Membership — 2 Year
- NADA Student Membership — 3 Year

#### Senior Discount Plans (`plan_type: senior`, `discount_required: senior`)
- Senior ADS Subscription — 1 Year
- Senior ADS Subscription — 2 Year
- Senior ADS Subscription — 3 Year

#### Registered Trainer Plans (`plan_type: trainer`, `role_required: registered_trainer`)
- Registered Trainer — 1 Year
- Registered Trainer — 2 Year
- Registered Trainer — 3 Year
- (Plus additional price variations from existing Stripe data)

#### Comped Membership (`plan_type: comped`, `is_visible: false`)
- Comped NADA Membership — $0.00
- Admin-assigned only, not visible on pricing page

### 6.2 Plan Visibility Rules

```
Pricing page logic (pseudocode):

FOR each plan WHERE is_active = TRUE AND is_visible = TRUE:
    IF plan.discount_required IS NULL AND plan.role_required IS NULL:
        → Show to everyone (standard plans)
    ELSE IF plan.discount_required = 'student':
        → Show ONLY if user.discount_type = 'student' AND user.discount_approved = TRUE
    ELSE IF plan.discount_required = 'senior':
        → Show ONLY if user.discount_type = 'senior' AND user.discount_approved = TRUE
    ELSE IF plan.role_required = 'registered_trainer':
        → Show ONLY if user has 'registered_trainer' role
```

### 6.3 Plan Switching Rules

- Members can switch between plans of the same type at any time
- Switching from membership → trainer requires admin approval first
- Stripe handles proration automatically
- Downgrade takes effect at period end; upgrade is immediate

---

## 7. Discount Approval Workflow

### 7.1 Request Flow

```
1. Member clicks "Request Student/Senior Discount" in portal
2. Member fills out discount request form:
   - Discount type: Student or Senior (radio)
   - Proof/documentation description (textarea)
   - Upload supporting documentation (file upload — student ID, senior ID, etc.)
3. Form submitted → discount_requests record created (status: pending)
4. Email sent to configured admin email (NADA_DISCOUNT_ADMIN_EMAIL env var)
5. Email contains:
   - Member name and email
   - Discount type requested
   - Proof description
   - Links to view uploaded documents
   - Two action buttons:
     ✅ APPROVE — links to /admin/discount-requests/{id}/approve?token={approval_token}
     ❌ DENY — links to /admin/discount-requests/{id}/deny?token={approval_token}
6. Admin clicks Approve or Deny directly from email
```

### 7.2 Approval Action

```
On Approve:
1. discount_requests.status → 'approved'
2. discount_requests.reviewed_by → admin user ID (from token lookup)
3. discount_requests.reviewed_at → now()
4. users.discount_type → request.discount_type
5. users.discount_approved → TRUE
6. users.discount_approved_at → now()
7. Email sent to member: "Your discount has been approved!"
8. Member now sees discounted plans on pricing page
```

### 7.3 Denial Action

```
On Deny:
1. discount_requests.status → 'denied'
2. discount_requests.reviewed_by → admin user ID
3. discount_requests.reviewed_at → now()
4. No changes to user discount fields
5. Email sent to member: "Your discount request was not approved."
   Include optional admin notes if provided
```

### 7.4 Token Security

- `approval_token` is a random 64-character string generated on request creation
- Tokens expire after 30 days (`token_expires_at`)
- Tokens are single-use — consumed on first click
- Expired/used tokens redirect to Filament admin with a notice to review manually

---

## 8. Certificate System

### 8.1 Certificate Data

Each certificate displays:
- **Organization name:** "NATIONAL ACUPUNCTURE DETOXIFICATION ASSOCIATION"
- **Recipient name:** Member's full name (first_name + last_name)
- **Description:** "has successfully completed all training and satisfied competencies for all Acupuncture Detox Specialist with the National Acupuncture Detoxification Association"
- **Date Issued:** The date the certificate was generated
- **NADA ID#:** The unique `certificate_code`
- **Expiration Date:** Derived from the member's subscription `current_period_end`
- **Signatures:** President and Vice President signature images
- **NADA Logo:** Organization logo in footer

### 8.2 Certificate HTML Template

The certificate is rendered from a Blade template based on the existing design. Key structural elements:

```blade
{{-- resources/views/certificates/template.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NADA Certificate — {{ $certificate->user->full_name }}</title>
    <style>
        /* Landscape orientation for PDF */
        @page { size: landscape; margin: 0; }
        body { margin: 0; padding: 0; width: 100%; height: 100%; }

        /* Left and right ribbon decorations */
        .left-ribbon { position: absolute; top: 0; left: 0; width: 360px; z-index: -10; }
        .right-ribbon { position: absolute; bottom: 0; right: 0; width: 240px; z-index: -10; }

        /* Header: Organization name */
        .header h1 {
            font-size: 58px; font-weight: 400; color: #313131;
            text-align: center; letter-spacing: 1.5px;
            padding-top: 8%; line-height: 49px; margin: 0;
        }

        /* Subheader */
        .sub-header h3 {
            text-align: center; color: #374269;
            letter-spacing: 1.5px; padding-top: 15px; margin: 0;
        }

        /* Recipient name */
        .recipient-name { text-align: center; height: 165px; position: relative; }
        .recipient-name h1 {
            margin: 0; position: relative; top: 30px; font-size: 42px;
        }
        .recipient-name img {
            z-index: -10; position: absolute;
            left: 50%; transform: translateX(-50%); bottom: 0;
        }

        /* Description text */
        .description { position: relative; top: -50px; }
        .description p {
            text-align: center; color: #374269;
            margin: 5px 0; font-size: 24px;
        }

        /* Signature images */
        .signatures { position: relative; width: 100%; top: -40px; }
        .signatures img { width: 310px; }
        .signatures img:first-child { float: left; margin-left: 10%; margin-top: 4px; }
        .signatures img:last-child { float: right; margin-right: 10%; }

        /* Certificate metadata (bottom-left) */
        .cert-meta { position: absolute; bottom: 20px; left: 20px; font-size: 22px; }
        .cert-meta p { margin: 15px 0; color: #d39c27; }
        .cert-meta p span { color: #000; }

        /* Footer logo */
        .footer {
            position: absolute; width: 100%; bottom: 40px;
            text-align: center; display: block; left: 0;
        }
        .footer img { width: 260px; margin: auto; }
    </style>
</head>
<body>
    <img class="left-ribbon" src="{{ public_path('images/certificates/left-ribbon.png') }}" />
    <img class="right-ribbon" src="{{ public_path('images/certificates/right-ribbon.png') }}" />

    <div class="header">
        <h1>NATIONAL ACUPUNCTURE<br>DETOXIFICATION ASSOCIATION</h1>
    </div>
    <div class="sub-header">
        <h3>THE FOLLOWING CERTIFICATE IS<br>GIVEN TO</h3>
    </div>
    <div class="recipient-name">
        <h1>{{ $certificate->user->full_name }}</h1>
        <img src="{{ public_path('images/certificates/middle-line.png') }}" />
    </div>
    <div class="description">
        <p>has successfully completed all training and satisfied competencies for all</p>
        <p>Acupuncture Detox Specialist with the National Acupuncture</p>
        <p>Detoxification Association</p>
    </div>
    <div class="signatures">
        <img src="{{ public_path('images/certificates/president.png') }}" />
        <img src="{{ public_path('images/certificates/vice-president.png') }}" />
    </div>
    <div class="footer">
        <img src="{{ public_path('images/certificates/nada-logo.png') }}" />
    </div>
    <div class="cert-meta">
        <p>Date Issued: <span>{{ $certificate->date_issued->format('F j, Y') }}</span></p>
        <p>NADA ID# <span>{{ $certificate->certificate_code }}</span></p>
        <p>Expiration Date: <span>{{ $certificate->expiration_date->format('F j, Y') }}</span></p>
    </div>
</body>
</html>
```

### 8.3 PDF Generation

```php
// App\Services\CertificateService

public function generatePdf(Certificate $certificate): string
{
    $html = view('certificates.template', [
        'certificate' => $certificate,
    ])->render();

    $pdf = Pdf::loadHTML($html)
        ->setPaper('letter', 'landscape')
        ->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
        ]);

    // Filename: "FirstName_LastName_NADA_Certificate.pdf"
    $filename = str_replace(' ', '_', $certificate->user->full_name)
        . '_NADA_Certificate.pdf';

    $path = "certificates/{$certificate->id}/{$filename}";
    Storage::put($path, $pdf->output());

    $certificate->update(['pdf_path' => $path]);

    return $path;
}
```

### 8.4 Certificate Generation Trigger

```
Training completion → Certificate eligibility:
1. Trainer marks member as "completed" in training_registrations
2. System checks: Does user have an active subscription?
3. If YES → Certificate is generated automatically:
   a. New certificate_code is generated (or preserved from migration)
   b. expiration_date = user's subscription.current_period_end
   c. PDF is generated and stored
   d. certificate_id is linked to the training_registration
   e. Email sent to member: "Your certificate is ready!"
4. If NO → Member is notified they need an active membership to receive certificate
```

### 8.5 Certificate Expiration Sync

When a subscription renews (webhook: `invoice.paid`):
1. Find all active certificates for that user
2. Update `expiration_date` to new `current_period_end`
3. Regenerate PDF with updated expiration date

When a subscription is canceled or lapses:
1. Find all active certificates for that user
2. Update `status` to `expired`
3. Certificate code remains — reactivation restores the certificate

### 8.6 Public Certificate Verification

**Public endpoint** (no authentication required):

```
GET /verify/{certificate_code}
```

Returns a public page showing:
- Certificate holder name
- NADA ID# (certificate code)
- Date issued
- Expiration date
- Status: **Active** ✅ or **Expired** ❌ or **Revoked** 🚫

This page uses a simple Blade view — no login required. It does NOT expose email, phone, or other private member data.

---

## 9. Training Management

### 9.1 Training Creation (Trainer)

Trainers create trainings from their portal dashboard:

**Required fields:**
- Title
- Type: In-Person, Virtual, or Hybrid
- Start date/time
- End date/time
- Timezone

**Conditional fields:**
- If In-Person or Hybrid: Location name, Location address
- If Virtual or Hybrid: Virtual meeting link
- If paid: Price (in dollars, stored as cents)

**Optional fields:**
- Description
- Max attendees (leave blank for unlimited)

**Status workflow:**
```
Draft → Published → Completed
                 → Canceled
```

- Only Published trainings are visible to members for registration
- Trainer can edit Draft and Published trainings
- Once Completed or Canceled, training is locked

### 9.2 Training Registration (Member)

```
1. Member browses published trainings in portal
2. Member clicks "Register" on a training
3. IF training is free:
   → training_registrations record created (status: registered)
   → Email confirmation sent to member
4. IF training is paid:
   → Stripe Checkout / PaymentIntent flow
   → On successful payment:
     - training_registrations record created (status: registered)
     - Payment split between platform and trainer (per payout_settings)
     - Email confirmation sent to member
5. IF training is full (max_attendees reached):
   → "Training Full" message shown, registration disabled
```

### 9.3 Training Completion (Trainer)

```
1. Trainer views their training's attendee list
2. Trainer can mark individual attendees as:
   - Attended (showed up)
   - Completed (satisfied all requirements)
   - No Show (didn't attend)
3. When marked "Completed":
   → training_registrations.status = 'completed'
   → training_registrations.completed_at = now()
   → Certificate generation triggered (see Section 8.4)
4. Trainer can also bulk-mark multiple attendees
```

### 9.4 Trainer Attendee List View

Trainers see for each of their trainings:
- Total registered count
- Attendee list with: Name, Email, Registration date, Status, Payment status
- Searchable and sortable
- Export to CSV option
- Ability to mark completion status per attendee

---

## 10. Clinicals Submission

### 10.1 Submission Form

Available to all logged-in members. Fields:

| Field | Type | Required | Notes |
|---|---|---|---|
| First Name | text input | ✅ | Pre-filled from user profile |
| Last Name | text input | ✅ | Pre-filled from user profile |
| Email | email input | ✅ | Pre-filled from user profile |
| Estimated Training Date | date picker | ✅ | Future or past dates allowed |
| Trainer | select dropdown | ✅ | Populated from all users with `registered_trainer` role |
| Treatment Log | file upload | ✅ | PDF, JPG, PNG, DOC, DOCX — max 10MB per file, multiple files |
| Additional Notes | textarea | ❌ | Optional notes |

### 10.2 Submission Flow

```
1. Member fills out clinicals form
2. Files uploaded via Spatie Media Library (stored in configured disk)
3. clinicals record created (status: submitted)
4. Notification sent to selected trainer (optional email)
5. Admin can see all clinicals in Filament panel
6. Admin/trainer reviews → changes status to approved/rejected
7. Member is notified of outcome
```

---

## 11. Member/Trainer Portal (Blade + Livewire)

### 11.1 Layout

- **Responsive sidebar navigation** (collapses to hamburger on mobile)
- **Top bar:** User name, avatar, notification bell, logout
- **Mobile-first:** All views designed for 375px+ width first, enhanced for desktop
- **Tailwind CSS** utility-first styling matching existing NADA brand colors

### 11.2 Member Dashboard

```
/dashboard
├── Membership Status Card
│   ├── Current plan name
│   ├── Status badge (Active, Past Due, Canceled)
│   ├── Renewal date (current_period_end)
│   └── Quick action: Manage Billing →
│
├── Certificates Card
│   ├── Count of active certificates
│   ├── List: certificate code, issued date, expiration, download button
│   └── Quick action: View All →
│
├── Upcoming Trainings Card
│   ├── Next registered training (date, title, type badge)
│   └── Quick action: Browse Trainings →
│
└── Quick Actions
    ├── Download Certificate (if available)
    ├── Register for Training
    ├── Submit Clinicals
    └── Request Discount
```

### 11.3 Portal Navigation (Member)

```
Dashboard
Membership
├── Current Plan
├── Change Plan
├── Billing & Payment Method
└── Invoice History
Certificates
├── My Certificates
└── Download
Trainings
├── Browse Trainings
├── My Registrations
└── Training History
Clinicals
└── Submit Clinicals
Account
├── Profile Settings
├── Request Discount
└── Upgrade to Trainer
```

### 11.4 Portal Navigation (Registered Trainer — Additional Sections)

```
All Member sections PLUS:
Trainer Dashboard
├── Upcoming Trainings (my hosted)
├── Recent Completions
└── Earnings Summary
My Trainings
├── Create Training
├── Manage Trainings
├── Attendee Lists
└── Mark Completions
Payouts
├── Connect Stripe Account
├── Payout History
├── Earnings Reports (filterable by date range)
└── Per-Training Breakdown
```

### 11.5 Key Livewire Components

| Component | Description |
|---|---|
| `PlanSelector` | Displays available plans based on user's role/discount. Handles plan switching with Stripe. |
| `BillingManager` | Update payment method, view current card, cancel/reactivate subscription. |
| `InvoiceHistory` | Paginated list of Stripe invoices with download links. |
| `CertificateList` | Lists user's certificates with status, expiration, and download buttons. |
| `TrainingBrowser` | Searchable, filterable list of published trainings with register buttons. |
| `TrainingRegistration` | Handles registration flow including Stripe payment for paid trainings. |
| `ClinicalsForm` | Multi-step form with file upload for clinical submissions. |
| `DiscountRequestForm` | Form for requesting student/senior discount with document upload. |
| `TrainerTrainingManager` | CRUD for trainer's own trainings (Livewire-based). |
| `AttendeeList` | Trainer view of training attendees with completion marking. |
| `PayoutDashboard` | Trainer's earnings summary, per-training breakdown, date filtering. |
| `StripeConnectOnboarding` | Handles Stripe Connect account setup flow for trainers. |
| `CertificateVerification` | Public component for certificate code lookup (no auth). |

### 11.6 Mobile-First Design Principles

- Touch targets: minimum 44x44px
- Stack layouts vertically on mobile, side-by-side on tablet+
- Collapsible sidebar with hamburger menu on mobile
- Bottom-anchored CTAs on mobile form views
- Card-based layout for dashboard widgets
- Responsive data tables with horizontal scroll or card-view on mobile
- Large, readable fonts (16px base minimum)
- No hover-only interactions — all actions accessible via tap

---

## 12. Admin Panel (Filament)

### 12.1 Resources (CRUD)

| Resource | Description | Key Features |
|---|---|---|
| `UserResource` | Manage all users | Search, filter by role/status, view subscription, impersonate, role assignment |
| `PlanResource` | Manage plans/pricing | Map to Stripe products/prices, set visibility rules, sort order |
| `SubscriptionResource` | View/manage subscriptions | Status, linked user/plan, cancel, date fields. Mostly read-only (Stripe is source of truth) |
| `CertificateResource` | Manage certificates | Issue, revoke, view code, regenerate PDF, link to user |
| `TrainingResource` | Oversee all trainings | View all trainers' trainings, attendee counts, status |
| `TrainingRegistrationResource` | View registrations | Linked training/user, payment status, completion status |
| `ClinicalResource` | Review clinical submissions | View files, change status, assign reviewer |
| `DiscountRequestResource` | Approve/deny discounts | Quick approve/deny actions, view proof documents |
| `PayoutSettingResource` | Configure payouts | Global default + per-trainer overrides |
| `StripeAccountResource` | View Connect accounts | Onboarding status, charges/payouts enabled |
| `InvoiceResource` | View invoice history | Search by user, status, date range |
| `TrainerApplicationResource` | Review trainer apps | Approve/deny with role assignment |

### 12.2 Dashboard Widgets

```
Filament Admin Dashboard
├── Total Members (active subscriptions count)
├── New Members This Month
├── Revenue This Month (from Stripe)
├── Pending Discount Requests (count + link)
├── Pending Trainer Applications (count + link)
├── Upcoming Trainings (count)
├── Recent Certificates Issued
└── Expiring Memberships (next 30 days)
```

### 12.3 Custom Admin Actions

| Action | Context | Description |
|---|---|---|
| Approve Discount | DiscountRequestResource | One-click approve, updates user flags |
| Deny Discount | DiscountRequestResource | One-click deny, sends notification |
| Approve Trainer | TrainerApplicationResource | Assigns trainer role, notifies user |
| Deny Trainer | TrainerApplicationResource | Denies application, notifies user |
| Issue Certificate | UserResource | Manually issue certificate to a member |
| Revoke Certificate | CertificateResource | Mark certificate as revoked |
| Regenerate PDF | CertificateResource | Re-render certificate PDF |
| Assign Comped Plan | UserResource | Assign the $0 comped membership to a user |
| Impersonate User | UserResource | Log in as this user to debug |
| Sync from Stripe | SubscriptionResource | Pull latest subscription data from Stripe API |
| Export to CSV | All Resources | Export filtered data to CSV |

### 12.4 Filament Configuration

```php
// app/Providers/Filament/AdminPanelProvider.php

->brandName('NADA Admin')
->brandLogo(asset('images/nada-logo.png'))
->path('admin')
->login()
->colors([
    'primary' => '#374269',  // NADA brand blue
    'danger' => Color::Red,
])
->discoverResources(in: app_path('Filament/Resources'))
->discoverPages(in: app_path('Filament/Pages'))
->discoverWidgets(in: app_path('Filament/Widgets'))
->middleware([...])
->authMiddleware([Authenticate::class])
```

---

## 13. API Routes & Endpoints

### 13.1 Authentication Routes

```
POST    /register                       Auth\RegisterController@store
POST    /login                          Auth\LoginController@store
POST    /logout                         Auth\LogoutController@store
POST    /forgot-password                Auth\ForgotPasswordController@store
POST    /reset-password                 Auth\ResetPasswordController@store
GET     /email/verify/{id}/{hash}       Auth\VerifyEmailController@__invoke
POST    /email/verification-notification Auth\EmailVerificationController@store
```

### 13.2 Member Portal Routes (auth middleware)

```
GET     /dashboard                              DashboardController@index

# Membership / Billing
GET     /membership                             MembershipController@index
GET     /membership/plans                       MembershipController@plans
POST    /membership/subscribe                   MembershipController@subscribe
PUT     /membership/switch-plan                 MembershipController@switchPlan
POST    /membership/cancel                      MembershipController@cancel
POST    /membership/reactivate                  MembershipController@reactivate
GET     /membership/billing                     BillingController@index
POST    /membership/billing/payment-method      BillingController@updatePaymentMethod
GET     /membership/invoices                    InvoiceController@index
GET     /membership/invoices/{invoice}/download  InvoiceController@download

# Certificates
GET     /certificates                           CertificateController@index
GET     /certificates/{certificate}/download    CertificateController@download

# Trainings
GET     /trainings                              TrainingController@index
GET     /trainings/{training}                   TrainingController@show
POST    /trainings/{training}/register          TrainingRegistrationController@store
DELETE  /trainings/{training}/cancel-registration TrainingRegistrationController@destroy
GET     /trainings/my-registrations             TrainingRegistrationController@index

# Clinicals
GET     /clinicals/submit                       ClinicalController@create
POST    /clinicals                              ClinicalController@store
GET     /clinicals/history                      ClinicalController@index

# Discount Request
GET     /discount/request                       DiscountRequestController@create
POST    /discount/request                       DiscountRequestController@store
GET     /discount/status                        DiscountRequestController@status

# Account / Profile
GET     /account                                AccountController@edit
PUT     /account                                AccountController@update
GET     /account/upgrade-to-trainer             TrainerApplicationController@create
POST    /account/upgrade-to-trainer             TrainerApplicationController@store
```

### 13.3 Trainer Portal Routes (auth + registered_trainer middleware)

```
# Trainer Dashboard
GET     /trainer/dashboard                      Trainer\DashboardController@index

# Training Management
GET     /trainer/trainings                      Trainer\TrainingController@index
GET     /trainer/trainings/create               Trainer\TrainingController@create
POST    /trainer/trainings                      Trainer\TrainingController@store
GET     /trainer/trainings/{training}/edit       Trainer\TrainingController@edit
PUT     /trainer/trainings/{training}            Trainer\TrainingController@update
DELETE  /trainer/trainings/{training}            Trainer\TrainingController@destroy
POST    /trainer/trainings/{training}/publish    Trainer\TrainingController@publish
POST    /trainer/trainings/{training}/cancel     Trainer\TrainingController@cancel
POST    /trainer/trainings/{training}/complete   Trainer\TrainingController@markComplete

# Attendees
GET     /trainer/trainings/{training}/attendees          Trainer\AttendeeController@index
POST    /trainer/trainings/{training}/attendees/{reg}/complete  Trainer\AttendeeController@markComplete
POST    /trainer/trainings/{training}/attendees/bulk-complete   Trainer\AttendeeController@bulkComplete
GET     /trainer/trainings/{training}/attendees/export   Trainer\AttendeeController@export

# Payouts
GET     /trainer/payouts                        Trainer\PayoutController@index
GET     /trainer/payouts/connect                Trainer\PayoutController@connectStripe
GET     /trainer/payouts/connect/callback       Trainer\PayoutController@connectCallback
GET     /trainer/payouts/reports                Trainer\PayoutController@reports
```

### 13.4 Public Routes (no auth)

```
GET     /verify/{certificate_code}              PublicCertificateController@verify
GET     /pricing                                PublicPricingController@index
```

### 13.5 Admin Discount Approval Routes (token-based, no auth required)

```
GET     /admin/discount-requests/{id}/approve   DiscountApprovalController@approve
        ?token={approval_token}

GET     /admin/discount-requests/{id}/deny      DiscountApprovalController@deny
        ?token={approval_token}
```

### 13.6 Stripe Webhook Routes

```
POST    /webhooks/stripe                        StripeWebhookController@handle
POST    /webhooks/stripe-connect                StripeConnectWebhookController@handle
```

### 13.7 Livewire Endpoints (auto-registered)

All Livewire components communicate via Laravel's built-in Livewire HTTP endpoint:
```
POST    /livewire/update                        Livewire internal
```

---

## 14. UI/UX Flows

### 14.1 New Member Signup Flow

```
1. User visits /pricing
2. Pricing page displays available plans (standard plans visible to all)
3. User clicks "Sign Up" on desired plan
4. User is redirected to /register with selected plan_id in session/query
5. Registration form: first name, last name, email, password, confirm password
6. On submit → user account created, redirected to Stripe Checkout for payment
7. Stripe Checkout completes → webhook fires → subscription created locally
8. User redirected to /dashboard with success message
9. Dashboard shows active membership status
```

### 14.2 Existing Member Login & Dashboard

```
1. User visits /login
2. Enters email + password
3. Redirected to /dashboard
4. Dashboard shows:
   - Membership status card (plan, status, renewal date)
   - Certificates card (if any)
   - Upcoming trainings card (if registered)
   - Quick action buttons
```

### 14.3 Training Registration Flow

```
1. Member navigates to /trainings
2. Sees list of published trainings (filterable by date, type, paid/free)
3. Clicks on a training to see details (description, date, location, price, spots remaining)
4. Clicks "Register"
5. IF free training:
   → Confirmation modal: "Register for {training title}?"
   → On confirm: registration created, confirmation email sent
   → Redirect to /trainings/my-registrations with success
6. IF paid training:
   → Stripe payment flow (inline Elements or Checkout Session)
   → On payment success: registration created, payment split via Connect
   → Confirmation email sent
   → Redirect to /trainings/my-registrations with success
```

### 14.4 Certificate Download Flow

```
1. Member navigates to /certificates
2. Sees table of their certificates:
   | Certificate Code | Date Issued | Expiration | Status | Action |
3. Clicks "Download" button
4. IF PDF exists: immediate download
5. IF PDF not generated yet: generated on-the-fly, then downloaded
6. PDF filename: "FirstName_LastName_NADA_Certificate.pdf"
7. PDF is landscape orientation with full certificate design
```

### 14.5 Discount Request Flow

```
1. Member navigates to /discount/request
2. Selects discount type: Student or Senior
3. Fills out proof/documentation description
4. Uploads supporting documentation (student ID, etc.)
5. Submits form
6. Sees confirmation: "Your request has been submitted. You'll be notified by email."
7. Can check status at /discount/status
8. Once approved: pricing page (/pricing or /membership/plans) shows discounted plans
```

### 14.6 Trainer Payout Setup Flow

```
1. Trainer navigates to /trainer/payouts/connect
2. IF not connected:
   → Sees "Connect Your Stripe Account" button
   → Clicks button → redirected to Stripe Express onboarding
   → Completes onboarding → redirected back to /trainer/payouts/connect/callback
   → System verifies account → stores stripe_connect_account_id
   → Shows success: "Your Stripe account is connected!"
3. IF already connected:
   → Shows account status (charges enabled, payouts enabled)
   → Shows earnings summary and payout history
4. Trainer can view:
   - Total earnings (all time, this month, this year)
   - Per-training breakdown (training name, # paid attendees, total revenue, platform fee, trainer payout)
   - Date range filtering
```

### 14.7 Trainer Training Management Flow

```
1. Trainer navigates to /trainer/trainings
2. Sees list of their trainings with status badges
3. Clicks "Create New Training"
4. Fills out training form:
   - Title, description
   - Type selector (in-person/virtual/hybrid)
   - Conditional fields appear based on type
   - Date/time picker
   - Price toggle (free or paid with amount)
   - Max attendees (optional)
5. Saves as Draft
6. Can preview, edit, then Publish
7. Published training appears in member training browser
8. After training date, trainer marks attendees as complete
9. Completion triggers certificate generation for each completed attendee
```

---

## 15. Migration Strategy

### 15.1 Phase 1: Stripe Audit

**Script:** `php artisan nada:stripe-audit`

```
Actions:
1. Fetch ALL Stripe Products via API
   → Log: product ID, name, active status
2. For each Product, fetch ALL Prices
   → Log: price ID, unit_amount, currency, recurring interval, active status
3. Fetch ALL active Subscriptions (paginated)
   → Log: subscription ID, customer ID, price ID, status, current_period_start, current_period_end
4. Fetch ALL Customers (paginated)
   → Log: customer ID, email, name, created date
5. Generate audit report:
   - Total products: X
   - Total prices: X (active: Y, inactive: Z)
   - Total subscriptions: X (by status breakdown)
   - Total customers: X
   - Price-to-subscription mapping (which prices have active subs)
   - Orphaned customers (no active subscription)
   - Duplicate product identification (e.g., two "NADA Membership" products)
6. Output: JSON + human-readable report to storage/app/migration/stripe-audit.json
```

### 15.2 Phase 2: Plan Mapping

**Script:** `php artisan nada:map-plans`

```
Actions:
1. Read stripe-audit.json
2. For each active Stripe Price with at least one subscription:
   → Create a `plans` record mapping:
     - stripe_product_id
     - stripe_price_id
     - price_cents (from Stripe unit_amount)
     - billing_interval + billing_interval_count (from Stripe recurring)
     - plan_type (inferred from product name: membership/trainer/senior/comped)
     - is_visible (TRUE for public plans, FALSE for comped/legacy)
     - is_active (TRUE for current plans, FALSE for deprecated-but-honored)
3. Admin reviews mappings and adjusts plan names, types, visibility
4. Output: plans table populated
```

### 15.3 Phase 3: Customer & Subscription Import

**Script:** `php artisan nada:import-subscriptions`

```
Actions:
1. Fetch all Stripe Customers with active subscriptions
2. For each Customer:
   a. CREATE user record (or match existing by email):
      - first_name, last_name: parsed from Stripe customer name
      - email: from Stripe customer email
      - stripe_customer_id: Stripe customer ID
      - password: generate random, force password reset on first login
      - role: 'member' (or 'registered_trainer' if on a trainer plan)
   b. For each active Subscription on this Customer:
      - Match stripe_price_id to local plans table
      - CREATE subscriptions record:
        - stripe_subscription_id
        - stripe_price_id
        - plan_id (from local plans)
        - status
        - current_period_start, current_period_end
3. Log: imported count, skipped count (already exists), error count
4. Output: users + subscriptions tables populated
```

### 15.4 Phase 4: Certificate Import

**Script:** `php artisan nada:import-certificates`

**Input:** CSV file exported from existing WordPress/Voxel system with columns:
- member_email
- certificate_code (EXISTING — must be preserved)
- date_issued
- expiration_date

```
Actions:
1. Read CSV file
2. For each row:
   a. Find user by email
   b. CREATE certificates record:
      - user_id: matched user
      - certificate_code: EXACT value from CSV (preserved)
      - date_issued: from CSV
      - expiration_date: from CSV (or sync from subscription.current_period_end)
      - status: 'active' if expiration_date > now(), else 'expired'
   c. Generate PDF for each imported certificate
3. Log: imported count, not-found-by-email count, duplicate code count
4. Output: certificates table populated with all historical codes preserved
```

### 15.5 Phase 5: Validation

**Script:** `php artisan nada:validate-migration`

```
Checks:
1. Every Stripe subscription has a matching local subscription record
2. Every local subscription has a valid plan_id
3. Every user with a subscription has a stripe_customer_id
4. All imported certificate_codes are unique
5. Certificate expiration dates align with subscription current_period_end
6. User counts match: Stripe customers with subs = local users with subs
7. Plan mapping: every active Stripe Price is mapped to a local plan
8. Role assignments: users on trainer plans have registered_trainer role
9. No orphaned records (subscriptions without users, certificates without users)
```

### 15.6 Rollback Plan

```
IF migration fails or data is incorrect:
1. The migration scripts are idempotent — re-runnable
2. Each import script logs all created record IDs
3. Rollback script: php artisan nada:rollback-migration
   → Deletes all records created during import (by logged IDs)
   → Does NOT touch Stripe data (Stripe remains source of truth)
4. Stripe subscriptions are NEVER modified during migration
   → Platform only reads and maps; no writes to Stripe during import
5. Once validated, switch DNS to new platform
6. Keep old WordPress site accessible (read-only) for 30 days post-migration
```

---

## 16. Email Notifications

### 16.1 Email List

| Email | Trigger | Recipient | Template |
|---|---|---|---|
| Welcome | User registers | New user | `emails.welcome` |
| Email Verification | User registers | New user | `emails.verify-email` |
| Password Reset | User requests reset | User | `emails.reset-password` |
| Subscription Confirmed | New subscription created | User | `emails.subscription-confirmed` |
| Subscription Renewed | Invoice paid (renewal) | User | `emails.subscription-renewed` |
| Subscription Canceled | Subscription canceled | User | `emails.subscription-canceled` |
| Payment Failed | Invoice payment fails | User | `emails.payment-failed` |
| Payment Method Updated | Payment method changed | User | `emails.payment-method-updated` |
| Certificate Ready | Certificate generated | User | `emails.certificate-ready` |
| Training Registration | Member registers for training | Member + Trainer | `emails.training-registered` |
| Training Reminder | 24 hours before training | Registered members | `emails.training-reminder` |
| Training Completed | Trainer marks completion | Member | `emails.training-completed` |
| Training Canceled | Trainer cancels training | Registered members | `emails.training-canceled` |
| Discount Requested | Member submits request | Admin email | `emails.discount-requested` |
| Discount Approved | Admin approves | Member | `emails.discount-approved` |
| Discount Denied | Admin denies | Member | `emails.discount-denied` |
| Trainer Application Submitted | Member applies | Admin email | `emails.trainer-application-submitted` |
| Trainer Application Approved | Admin approves | Member | `emails.trainer-application-approved` |
| Trainer Application Denied | Admin denies | Member | `emails.trainer-application-denied` |
| Clinical Submitted | Member submits | Admin + Selected trainer | `emails.clinical-submitted` |
| Payout Received | Stripe Connect payout | Trainer | `emails.payout-received` |

### 16.2 Email Configuration

```env
MAIL_MAILER=smtp
MAIL_FROM_ADDRESS=noreply@acudetox.com
MAIL_FROM_NAME="NADA - National Acupuncture Detoxification Association"
NADA_ADMIN_EMAIL=admin@acudetox.com
NADA_DISCOUNT_ADMIN_EMAIL=discounts@acudetox.com
```

### 16.3 Email Design

- All emails use a consistent NADA-branded template
- Logo header, content body, footer with contact info
- Mobile-responsive HTML emails (MJML or Blade-based)
- Plain text fallback for all emails

---

## 17. Stripe Webhooks

### 17.1 Webhook Endpoints

```
POST /webhooks/stripe           → Main Stripe account webhooks
POST /webhooks/stripe-connect   → Stripe Connect account webhooks
```

### 17.2 Handled Events

#### Main Account Webhooks

| Event | Handler Action |
|---|---|
| `customer.subscription.created` | Create/update local subscription record |
| `customer.subscription.updated` | Update local subscription status, period dates |
| `customer.subscription.deleted` | Mark local subscription as canceled |
| `invoice.paid` | Update subscription period, sync certificate expiration dates, create invoice record |
| `invoice.payment_failed` | Update subscription status to past_due, send payment failed email |
| `customer.updated` | Sync customer data (email, name) if changed in Stripe |
| `payment_method.attached` | Log payment method update |
| `checkout.session.completed` | Handle new subscription from Checkout, handle training registration payments |

#### Connect Account Webhooks

| Event | Handler Action |
|---|---|
| `account.updated` | Update stripe_accounts record (charges_enabled, payouts_enabled, etc.) |
| `payout.paid` | Log payout, optionally notify trainer |
| `payout.failed` | Log failure, notify admin |

### 17.3 Webhook Signature Verification

```php
// All webhooks verify Stripe signature before processing
Stripe\Webhook::constructEvent(
    $payload,
    $sigHeader,
    config('services.stripe.webhook_secret')
);
```

### 17.4 Webhook Processing

- All webhooks are processed via Laravel Queue (async)
- Retry logic: 3 attempts with exponential backoff
- Failed webhooks logged to `failed_jobs` table
- Idempotent handlers — same event processed twice produces same result

---

## 18. Verification & Testing

### 18.1 Feature Testing Checklist

#### Authentication
- [ ] User can register with email/password
- [ ] User receives verification email
- [ ] User can log in / log out
- [ ] Password reset flow works end-to-end

#### Membership & Billing
- [ ] New user can select a plan and subscribe via Stripe
- [ ] Subscription is created locally after Stripe webhook
- [ ] User sees correct plan and status on dashboard
- [ ] User can update payment method
- [ ] User can switch plans (proration works)
- [ ] User can cancel subscription (end of period)
- [ ] User can reactivate a canceled subscription
- [ ] Invoice history displays correctly
- [ ] Invoice PDF downloads work

#### Plans & Visibility
- [ ] Standard plans visible to all users
- [ ] Student/senior plans hidden from unapproved users
- [ ] Student/senior plans visible after discount approval
- [ ] Trainer plans only visible to users with trainer role
- [ ] Comped plans not visible on pricing page
- [ ] Admin can assign comped plan to a user

#### Discount Workflow
- [ ] Member can submit student discount request with file upload
- [ ] Member can submit senior discount request with file upload
- [ ] Admin receives email with approve/deny links
- [ ] Clicking approve link updates user's discount flags
- [ ] Clicking deny link sends denial email
- [ ] Approved member sees discount plans
- [ ] Token expires after 30 days
- [ ] Used token cannot be reused

#### Certificates
- [ ] Certificate generated when trainer marks completion
- [ ] Certificate PDF renders in landscape with correct data
- [ ] Certificate filename includes member name
- [ ] Certificate code is unique
- [ ] Certificate expiration matches subscription period end
- [ ] Expiration updates on subscription renewal
- [ ] Public verification endpoint works for valid code
- [ ] Public verification shows expired status for expired cert
- [ ] Migrated certificate codes are preserved

#### Trainings
- [ ] Trainer can create a draft training
- [ ] Trainer can publish a training
- [ ] Published training visible in member browser
- [ ] Member can register for a free training
- [ ] Member can register for a paid training (Stripe payment)
- [ ] Training respects max attendees limit
- [ ] Trainer sees attendee list
- [ ] Trainer can mark attendees as completed
- [ ] Bulk completion marking works
- [ ] Attendee export to CSV works
- [ ] Training cancellation notifies registered members

#### Clinicals
- [ ] Member can submit clinical form
- [ ] File uploads work (PDF, images, DOC/DOCX)
- [ ] Selected trainer is stored correctly
- [ ] Admin can review clinicals in Filament

#### Trainer Upgrade
- [ ] Member can submit trainer application
- [ ] Admin receives notification
- [ ] Admin can approve in Filament → role updated
- [ ] Admin can deny in Filament → email sent
- [ ] After approval, trainer portal sections visible

#### Stripe Connect
- [ ] Trainer can initiate Stripe Connect onboarding
- [ ] Onboarding callback correctly stores account info
- [ ] Training payment split works (platform fee + trainer payout)
- [ ] Trainer sees earnings reports
- [ ] Per-training breakdown is accurate

#### Admin Panel (Filament)
- [ ] All resources (Users, Plans, Subscriptions, etc.) load correctly
- [ ] CRUD operations work for each resource
- [ ] Dashboard widgets show correct counts
- [ ] Quick actions (approve discount, approve trainer) work
- [ ] CSV export works for all resources
- [ ] Impersonate user works

### 18.2 Migration Validation Checklist

- [ ] Stripe audit script runs without errors
- [ ] All Stripe Products and Prices are captured
- [ ] Plan mapping covers all active prices
- [ ] All ~1,300 subscriptions imported
- [ ] User count matches Stripe customer count
- [ ] All existing certificate codes preserved (spot-check 50 random)
- [ ] Certificate PDFs generate correctly for imported certs
- [ ] Subscription status matches Stripe status for all records
- [ ] Public verification works for migrated certificates
- [ ] No orphaned records in any table
- [ ] Re-running import script is idempotent (no duplicates)

### 18.3 Stripe Webhook Testing

```
Using Stripe CLI for local testing:

# Listen to all events
stripe listen --forward-to localhost:8000/webhooks/stripe

# Trigger specific events
stripe trigger customer.subscription.created
stripe trigger invoice.paid
stripe trigger invoice.payment_failed
stripe trigger customer.subscription.deleted
stripe trigger checkout.session.completed

# For Connect events
stripe listen --forward-to localhost:8000/webhooks/stripe-connect \
  --forward-connect-to localhost:8000/webhooks/stripe-connect
stripe trigger account.updated
```

### 18.4 Testing Environment

```env
# Use Stripe test mode keys
STRIPE_KEY=pk_test_XXXXX
STRIPE_SECRET=sk_test_XXXXX
STRIPE_WEBHOOK_SECRET=whsec_test_XXXXX

# Test card numbers
# 4242424242424242 — Succeeds
# 4000000000000002 — Declined
# 4000000000003220 — Requires authentication (3D Secure)
```

---

## Appendix A: Environment Variables

```env
# Application
APP_NAME="NADA Platform"
APP_ENV=production
APP_KEY=base64:XXXXX
APP_DEBUG=false
APP_URL=https://acudetox.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nada_platform
DB_USERNAME=nada
DB_PASSWORD=XXXXX

# Stripe
STRIPE_KEY=pk_live_XXXXX
STRIPE_SECRET=sk_live_XXXXX
STRIPE_WEBHOOK_SECRET=whsec_XXXXX
STRIPE_CONNECT_WEBHOOK_SECRET=whsec_XXXXX

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=XXXXX
MAIL_PASSWORD=XXXXX
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@acudetox.com
MAIL_FROM_NAME="NADA"

# NADA-Specific
NADA_ADMIN_EMAIL=admin@acudetox.com
NADA_DISCOUNT_ADMIN_EMAIL=discounts@acudetox.com

# Queue
QUEUE_CONNECTION=database

# Filesystem
FILESYSTEM_DISK=local

# Certificate Code Format
NADA_CERT_CODE_PREFIX=NADA
```

---

## Appendix B: Certificate Asset Files Required

The following image assets must be migrated from the existing WordPress site and placed in `public/images/certificates/`:

| File | Source URL (existing) | Description |
|---|---|---|
| `left-ribbon.png` | `staging.acudetox.com/wp-content/uploads/2024/11/left-ribbon.png` | Left decorative ribbon |
| `right-ribbon.png` | `staging.acudetox.com/wp-content/uploads/2024/11/right-ribbon.png` | Right decorative ribbon |
| `middle-line.png` | `staging.acudetox.com/wp-content/uploads/2024/11/middle-line.png` | Decorative line under name |
| `president.png` | `staging.acudetox.com/wp-content/uploads/2024/11/president.png` | President signature |
| `vice-president.png` | `staging.acudetox.com/wp-content/uploads/2024/12/vice-president.png` | Vice President signature |
| `nada-logo.png` | `staging.acudetox.com/wp-content/uploads/2024/11/nada.png` | NADA logo for footer |

---

## Appendix C: Suggested Directory Structure

```
nada-platform/
├── app/
│   ├── Enums/
│   │   ├── DiscountType.php
│   │   ├── PlanType.php
│   │   ├── SubscriptionStatus.php
│   │   ├── TrainingType.php
│   │   ├── TrainingStatus.php
│   │   └── RegistrationStatus.php
│   ├── Filament/
│   │   ├── Pages/
│   │   │   └── Dashboard.php
│   │   ├── Resources/
│   │   │   ├── UserResource.php
│   │   │   ├── PlanResource.php
│   │   │   ├── SubscriptionResource.php
│   │   │   ├── CertificateResource.php
│   │   │   ├── TrainingResource.php
│   │   │   ├── TrainingRegistrationResource.php
│   │   │   ├── ClinicalResource.php
│   │   │   ├── DiscountRequestResource.php
│   │   │   ├── PayoutSettingResource.php
│   │   │   ├── StripeAccountResource.php
│   │   │   ├── InvoiceResource.php
│   │   │   └── TrainerApplicationResource.php
│   │   └── Widgets/
│   │       ├── TotalMembersWidget.php
│   │       ├── NewMembersWidget.php
│   │       ├── RevenueWidget.php
│   │       ├── PendingDiscountsWidget.php
│   │       ├── PendingTrainerAppsWidget.php
│   │       └── ExpiringMembershipsWidget.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   ├── DashboardController.php
│   │   │   ├── MembershipController.php
│   │   │   ├── BillingController.php
│   │   │   ├── InvoiceController.php
│   │   │   ├── CertificateController.php
│   │   │   ├── TrainingController.php
│   │   │   ├── TrainingRegistrationController.php
│   │   │   ├── ClinicalController.php
│   │   │   ├── DiscountRequestController.php
│   │   │   ├── DiscountApprovalController.php
│   │   │   ├── AccountController.php
│   │   │   ├── TrainerApplicationController.php
│   │   │   ├── PublicCertificateController.php
│   │   │   ├── PublicPricingController.php
│   │   │   ├── StripeWebhookController.php
│   │   │   ├── StripeConnectWebhookController.php
│   │   │   └── Trainer/
│   │   │       ├── DashboardController.php
│   │   │       ├── TrainingController.php
│   │   │       ├── AttendeeController.php
│   │   │       └── PayoutController.php
│   │   ├── Livewire/
│   │   │   ├── PlanSelector.php
│   │   │   ├── BillingManager.php
│   │   │   ├── InvoiceHistory.php
│   │   │   ├── CertificateList.php
│   │   │   ├── TrainingBrowser.php
│   │   │   ├── TrainingRegistration.php
│   │   │   ├── ClinicalsForm.php
│   │   │   ├── DiscountRequestForm.php
│   │   │   ├── TrainerTrainingManager.php
│   │   │   ├── AttendeeList.php
│   │   │   ├── PayoutDashboard.php
│   │   │   ├── StripeConnectOnboarding.php
│   │   │   └── CertificateVerification.php
│   │   └── Middleware/
│   │       └── EnsureIsTrainer.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Plan.php
│   │   ├── Subscription.php
│   │   ├── Certificate.php
│   │   ├── Training.php
│   │   ├── TrainingRegistration.php
│   │   ├── Clinical.php
│   │   ├── DiscountRequest.php
│   │   ├── PayoutSetting.php
│   │   ├── StripeAccount.php
│   │   ├── Invoice.php
│   │   └── TrainerApplication.php
│   ├── Notifications/
│   │   ├── WelcomeNotification.php
│   │   ├── SubscriptionConfirmedNotification.php
│   │   ├── SubscriptionRenewedNotification.php
│   │   ├── SubscriptionCanceledNotification.php
│   │   ├── PaymentFailedNotification.php
│   │   ├── CertificateReadyNotification.php
│   │   ├── TrainingRegisteredNotification.php
│   │   ├── TrainingReminderNotification.php
│   │   ├── TrainingCompletedNotification.php
│   │   ├── TrainingCanceledNotification.php
│   │   ├── DiscountRequestedNotification.php
│   │   ├── DiscountApprovedNotification.php
│   │   ├── DiscountDeniedNotification.php
│   │   ├── TrainerApplicationSubmittedNotification.php
│   │   ├── TrainerApplicationApprovedNotification.php
│   │   ├── TrainerApplicationDeniedNotification.php
│   │   ├── ClinicalSubmittedNotification.php
│   │   └── PayoutReceivedNotification.php
│   ├── Services/
│   │   ├── StripeService.php
│   │   ├── StripeConnectService.php
│   │   ├── CertificateService.php
│   │   ├── SubscriptionService.php
│   │   └── PayoutService.php
│   └── Console/
│       └── Commands/
│           ├── StripeAudit.php
│           ├── MapPlans.php
│           ├── ImportSubscriptions.php
│           ├── ImportCertificates.php
│           ├── ValidateMigration.php
│           ├── RollbackMigration.php
│           └── SyncCertificateExpirations.php
├── database/
│   └── migrations/
│       ├── create_plans_table.php
│       ├── create_subscriptions_table.php
│       ├── create_certificates_table.php
│       ├── create_trainings_table.php
│       ├── create_training_registrations_table.php
│       ├── create_clinicals_table.php
│       ├── create_discount_requests_table.php
│       ├── create_payout_settings_table.php
│       ├── create_stripe_accounts_table.php
│       ├── create_invoices_table.php
│       └── create_trainer_applications_table.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php          (portal layout)
│       │   └── public.blade.php       (public pages layout)
│       ├── dashboard.blade.php
│       ├── membership/
│       ├── certificates/
│       │   ├── index.blade.php
│       │   └── template.blade.php     (PDF template)
│       ├── trainings/
│       ├── clinicals/
│       ├── discount/
│       ├── account/
│       ├── trainer/
│       ├── public/
│       │   ├── pricing.blade.php
│       │   └── verify.blade.php
│       ├── emails/
│       │   └── (all email templates)
│       └── livewire/
│           └── (all Livewire component views)
├── public/
│   └── images/
│       └── certificates/
│           ├── left-ribbon.png
│           ├── right-ribbon.png
│           ├── middle-line.png
│           ├── president.png
│           ├── vice-president.png
│           └── nada-logo.png
├── routes/
│   ├── web.php
│   ├── auth.php
│   └── webhooks.php
└── storage/
    └── app/
        ├── certificates/          (generated PDFs)
        ├── clinicals/             (uploaded treatment logs)
        ├── discount-requests/     (uploaded proof documents)
        └── migration/             (audit reports, import logs)
```

---

## Appendix D: Scheduled Tasks

```php
// app/Console/Kernel.php

$schedule->command('nada:sync-certificate-expirations')
    ->daily()
    ->description('Sync certificate expiration dates with subscription periods');

$schedule->command('nada:send-training-reminders')
    ->dailyAt('09:00')
    ->description('Send 24-hour training reminders');

$schedule->command('nada:expire-discount-tokens')
    ->daily()
    ->description('Expire discount approval tokens older than 30 days');

$schedule->command('nada:check-expiring-memberships')
    ->weekly()
    ->description('Notify admin of memberships expiring in next 30 days');
```

---

*End of specification. This document contains everything needed to build the complete NADA platform using Laravel, Filament, Blade + Livewire, and Stripe.*

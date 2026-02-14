# NADA Data Import Specification

> **Version:** 1.0
> **Date:** February 14, 2026
> **Purpose:** Define all data import commands needed to migrate from WordPress/Voxel to the new Laravel platform

---

## Existing Import Commands

| Command | Description | Status |
|---------|-------------|--------|
| `nada:import-subscriptions` | Import Stripe subscriptions and link to users | Built |
| `nada:import-certificates` | Import existing certificates from old platform | Built |

---

## Planned Import Commands

### 1. `nada:import-stripe-accounts` — Import Stripe Connected Accounts

**Priority:** High
**Source:** Stripe Connect API (`Account::all()`)
**Target:** `stripe_accounts` table

#### Context
NADA has ~120 existing Stripe Connected accounts (Express type) for trainers. These accounts are already connected to the NADA platform account (`acct_1PBnlrDPaQVax0KG`). The new portal needs `stripe_accounts` records linking each connected account to the correct trainer `user`.

#### Logic
1. Paginate through all connected accounts via `Stripe\Account::all(['limit' => 100])`
2. For each connected account:
   - Extract `id` (e.g., `acct_xxx`), `email`, `charges_enabled`, `payouts_enabled`, `details_submitted`
   - Match to a `users` record by email (case-insensitive)
   - Skip if no matching user or user doesn't have `registered_trainer` role
   - Skip if a `stripe_accounts` record already exists for that user
   - Create `StripeAccount` record:
     ```php
     StripeAccount::create([
         'user_id' => $user->id,
         'stripe_connect_account_id' => $account->id,
         'charges_enabled' => $account->charges_enabled,
         'payouts_enabled' => $account->payouts_enabled,
         'details_submitted' => $account->details_submitted,
         'onboarding_complete' => $account->charges_enabled && $account->payouts_enabled,
     ]);
     ```
3. Log results: matched, skipped (no user), skipped (not trainer), skipped (already linked), errors

#### Options/Flags
- `--dry-run` — Report what would be imported without writing to DB
- `--force` — Overwrite existing `stripe_accounts` records (re-sync from Stripe)

#### Edge Cases
- Trainer email in Stripe doesn't match portal email (log for manual review)
- Multiple Stripe accounts for the same email (log warning, link the most recent enabled one)
- Connected account with `restricted` status — still import, the `charges_enabled`/`payouts_enabled` flags will reflect the restriction

---

### 2. `nada:import-users` — Import Users from WordPress/Voxel

**Priority:** High
**Source:** WordPress database export or CSV
**Target:** `users` table

#### Context
Import existing members and trainers from the WordPress/Voxel platform. Must preserve email addresses for Stripe subscription and connected account matching.

#### Logic
1. Read source data (CSV or direct DB connection)
2. For each user:
   - Create `User` record with name, email, phone, address fields
   - Assign appropriate role (`member` or `registered_trainer`)
   - Generate a temporary password and queue welcome email with password reset link
3. Skip duplicates by email

#### Fields to Import
- Name (first, last)
- Email
- Phone
- Address (street, city, state, zip)
- Role/membership type
- WordPress user ID (store in metadata for cross-referencing)

---

### 3. `nada:import-trainings` — Import Historical Trainings

**Priority:** Medium
**Source:** WordPress/Voxel database or CSV
**Target:** `trainings` table

#### Context
Import past training records so trainers see their history and attendees have records linked.

#### Logic
1. Read source data
2. For each training:
   - Match trainer by email/WordPress ID
   - Create `Training` record with title, dates, type, location, status
   - Import attendee registrations and link to users

---

### 4. `nada:import-payout-settings` — Import Trainer Payout Percentages

**Priority:** Low
**Source:** Admin-provided CSV or config
**Target:** `payout_settings` table

#### Context
Some trainers may have custom platform fee percentages. Import these so payouts calculate correctly from day one.

---

## Import Order (Recommended)

Run imports in this sequence to satisfy foreign key dependencies:

1. `nada:import-users` — Users must exist first
2. `nada:import-subscriptions` — Links to users, needs Stripe customer IDs
3. `nada:import-stripe-accounts` — Links to users with trainer role
4. `nada:import-certificates` — Links to users
5. `nada:import-trainings` — Links to trainer users and attendee users
6. `nada:import-payout-settings` — Links to trainer users

---

## General Import Conventions

All import commands should follow these patterns (consistent with existing commands):

- **Namespace:** `App\Console\Commands`
- **Prefix:** `nada:import-*`
- **Common flags:**
  - `--dry-run` — Preview without writing
  - `--force` — Overwrite existing records
  - `--verbose` — Show per-record detail
- **Output:** Summary table at end (imported, skipped, errors)
- **Logging:** Write detailed log to `storage/logs/import-{type}-{date}.log`
- **Idempotent:** Safe to run multiple times; skips already-imported records by default
- **Transactions:** Wrap bulk operations in DB transactions with chunking

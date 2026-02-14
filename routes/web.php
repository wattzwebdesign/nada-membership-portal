<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\ClinicalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscountApprovalController;
use App\Http\Controllers\DiscountRequestController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicCertificateController;
use App\Http\Controllers\PublicPricingController;
use App\Http\Controllers\TrainerApplicationController;
use App\Http\Controllers\NdaController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\TrainingRegistrationController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', function () {
    return view('welcome');
});

Route::get('/pricing', [PublicPricingController::class, 'index'])->name('public.pricing');
Route::get('/verify/{certificate_code?}', [PublicCertificateController::class, 'verify'])->name('public.verify');

// Admin Discount Approval (token-based, no auth required — links sent via email)
Route::get('/admin/discount-requests/{token}/approve', [DiscountApprovalController::class, 'approve'])->name('discount.approve');
Route::get('/admin/discount-requests/{token}/deny', [DiscountApprovalController::class, 'deny'])->name('discount.deny');

// NDA Agreement (auth required, but before verified/nda middleware)
Route::middleware(['auth'])->group(function () {
    Route::get('/nda', [NdaController::class, 'show'])->name('nda.show');
    Route::post('/nda', [NdaController::class, 'accept'])->name('nda.accept');
});

// Authenticated Member Routes
Route::middleware(['auth', 'verified', 'nda'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Membership & Billing
    Route::get('/membership', [MembershipController::class, 'index'])->name('membership.index');
    Route::get('/membership/plans', [MembershipController::class, 'plans'])->name('membership.plans');
    Route::post('/membership/subscribe', [MembershipController::class, 'subscribe'])->name('membership.subscribe');
    Route::put('/membership/switch-plan', [MembershipController::class, 'switchPlan'])->name('membership.switch-plan');
    Route::post('/membership/cancel', [MembershipController::class, 'cancel'])->name('membership.cancel');
    Route::post('/membership/reactivate', [MembershipController::class, 'reactivate'])->name('membership.reactivate');
    Route::get('/membership/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('/membership/billing/payment-method', [BillingController::class, 'updatePaymentMethod'])->name('billing.update-payment-method');
    Route::get('/membership/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/membership/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');

    // Certificates
    Route::get('/certificates', [CertificateController::class, 'index'])->name('certificates.index');
    Route::get('/certificates/{certificate}/download', [CertificateController::class, 'download'])->name('certificates.download');

    // Trainings
    Route::get('/trainings', [TrainingController::class, 'index'])->name('trainings.index');
    Route::get('/trainings/my-registrations', [TrainingRegistrationController::class, 'index'])->name('trainings.my-registrations');
    Route::get('/trainings/{training}', [TrainingController::class, 'show'])->name('trainings.show');
    Route::post('/trainings/{training}/register', [TrainingRegistrationController::class, 'store'])->name('trainings.register');
    Route::delete('/trainings/{training}/cancel-registration', [TrainingRegistrationController::class, 'destroy'])->name('trainings.cancel-registration');

    // Clinicals
    Route::get('/clinicals/submit', [ClinicalController::class, 'create'])->name('clinicals.create');
    Route::post('/clinicals', [ClinicalController::class, 'store'])->name('clinicals.store');
    Route::get('/clinicals/history', [ClinicalController::class, 'index'])->name('clinicals.index');

    // Discount Request
    Route::get('/discount/request', [DiscountRequestController::class, 'create'])->name('discount.request.create');
    Route::post('/discount/request', [DiscountRequestController::class, 'store'])->name('discount.request.store');
    Route::get('/discount/status', [DiscountRequestController::class, 'status'])->name('discount.request.status');

    // Account / Profile
    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('/account', [AccountController::class, 'update'])->name('account.update');
    Route::get('/account/upgrade-to-trainer', [TrainerApplicationController::class, 'create'])->name('trainer-application.create');
    Route::post('/account/upgrade-to-trainer', [TrainerApplicationController::class, 'store'])->name('trainer-application.store');
});

// Trainer Routes
Route::middleware(['auth', 'verified', 'nda', 'trainer'])->prefix('trainer')->name('trainer.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Trainer\DashboardController::class, 'index'])->name('dashboard');

    // Training Management
    Route::get('/trainings', [App\Http\Controllers\Trainer\TrainingController::class, 'index'])->name('trainings.index');
    Route::get('/trainings/create', [App\Http\Controllers\Trainer\TrainingController::class, 'create'])->name('trainings.create');
    Route::post('/trainings', [App\Http\Controllers\Trainer\TrainingController::class, 'store'])->name('trainings.store');
    Route::get('/trainings/{training}/edit', [App\Http\Controllers\Trainer\TrainingController::class, 'edit'])->name('trainings.edit');
    Route::put('/trainings/{training}', [App\Http\Controllers\Trainer\TrainingController::class, 'update'])->name('trainings.update');
    Route::delete('/trainings/{training}', [App\Http\Controllers\Trainer\TrainingController::class, 'destroy'])->name('trainings.destroy');
    Route::post('/trainings/{training}/publish', [App\Http\Controllers\Trainer\TrainingController::class, 'publish'])->name('trainings.publish');
    Route::post('/trainings/{training}/cancel', [App\Http\Controllers\Trainer\TrainingController::class, 'cancel'])->name('trainings.cancel');
    Route::post('/trainings/{training}/complete', [App\Http\Controllers\Trainer\TrainingController::class, 'markComplete'])->name('trainings.complete');

    // Attendees
    Route::get('/trainings/{training}/attendees', [App\Http\Controllers\Trainer\AttendeeController::class, 'index'])->name('attendees.index');
    Route::post('/trainings/{training}/attendees/{registration}/complete', [App\Http\Controllers\Trainer\AttendeeController::class, 'markComplete'])->name('attendees.complete');
    Route::post('/trainings/{training}/attendees/bulk-complete', [App\Http\Controllers\Trainer\AttendeeController::class, 'bulkComplete'])->name('attendees.bulk-complete');
    Route::get('/trainings/{training}/attendees/export', [App\Http\Controllers\Trainer\AttendeeController::class, 'export'])->name('attendees.export');

    // Payouts
    Route::get('/payouts', [App\Http\Controllers\Trainer\PayoutController::class, 'index'])->name('payouts.index');
    Route::get('/payouts/connect', [App\Http\Controllers\Trainer\PayoutController::class, 'connectStripe'])->name('payouts.connect');
    Route::get('/payouts/connect/callback', [App\Http\Controllers\Trainer\PayoutController::class, 'connectCallback'])->name('payouts.connect.callback');
    Route::get('/payouts/reports', [App\Http\Controllers\Trainer\PayoutController::class, 'reports'])->name('payouts.reports');
});

require __DIR__.'/auth.php';

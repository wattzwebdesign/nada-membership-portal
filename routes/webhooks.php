<?php

use App\Http\Controllers\StripeConnectWebhookController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle'])->name('webhooks.stripe');
Route::post('/webhooks/stripe-connect', [StripeConnectWebhookController::class, 'handle'])->name('webhooks.stripe-connect');

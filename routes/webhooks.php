<?php

use App\Http\Controllers\StripeConnectWebhookController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\WalletPassController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle'])->name('webhooks.stripe');
Route::post('/webhooks/stripe-connect', [StripeConnectWebhookController::class, 'handle'])->name('webhooks.stripe-connect');

// Apple Wallet Web Service Callbacks
Route::post('/api/wallet/apple/v1/devices/{deviceLibraryId}/registrations/{passTypeId}/{serialNumber}', [WalletPassController::class, 'registerDevice']);
Route::delete('/api/wallet/apple/v1/devices/{deviceLibraryId}/registrations/{passTypeId}/{serialNumber}', [WalletPassController::class, 'unregisterDevice']);
Route::get('/api/wallet/apple/v1/devices/{deviceLibraryId}/registrations/{passTypeId}', [WalletPassController::class, 'getUpdatedSerials']);
Route::get('/api/wallet/apple/v1/passes/{passTypeId}/{serialNumber}', [WalletPassController::class, 'getLatestPass']);
Route::post('/api/wallet/apple/v1/log', [WalletPassController::class, 'logErrors']);

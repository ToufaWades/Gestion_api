<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompteController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionMongoController;

// Register v1 routes via a closure and mount both under the production
// domain (API_HOST) and locally. routes/api.php is already served under the
// /api prefix by Laravel, so we register routes under /api/v1/...
// Use shared v1 routes so we can mount them in multiple places (domain + local prefix)
// The actual route definitions live in routes/v1_routes.php
// Note: routes defined there use the full paths starting with /api/v1/...

// Mount under production domain using the expected API prefix (/api/v1).
// Note: routes in this file are already prefixed with `/api` by the
// RouteServiceProvider, so here we only add the `v1` suffix.
// Route::domain(env('API_HOST', 'fatou.wade'))->group(function () {
//     Route::prefix('v1')->group(function () {
//         require __DIR__ . '/v1_routes.php';
//     });
// });

// Also mount for general API access (when not using the API_HOST domain).
// This registers the same routes under /api/v1 on the current host.
Route::prefix('v1')->group(function () {
    require __DIR__ . '/v1_routes.php';
});

// MongoDB transactions (archives)
Route::get('v1/archives/{semaineId}/transactions', [\App\Http\Controllers\TransactionMongoController::class, 'index']);
Route::post('v1/archives/{semaineId}/transactions', [\App\Http\Controllers\TransactionMongoController::class, 'store']);

// Transactions
Route::get('v1/comptes/{compte}/transactions', [\App\Http\Controllers\TransactionController::class, 'listByCompte']);
Route::post('v1/transactions/depot', [\App\Http\Controllers\TransactionController::class, 'depot']);
Route::post('v1/transactions/retrait', [\App\Http\Controllers\TransactionController::class, 'retrait']);
Route::post('v1/transactions/archiver-semaine', [\App\Http\Controllers\TransactionController::class, 'archiverTransactionsSemaine']);

// Transactions (admin)
Route::get('v1/transactions', [\App\Http\Controllers\DashboardController::class, 'listAllTransactions']);
Route::get('v1/transactions/{id}', [\App\Http\Controllers\DashboardController::class, 'getTransaction']);
Route::delete('v1/transactions/{id}', [\App\Http\Controllers\DashboardController::class, 'deleteTransaction']);

// Statistiques compte
Route::get('v1/comptes/{compte}/statistiques', [\App\Http\Controllers\CompteController::class, 'statistiques']);

// Dashboard
Route::get('v1/dashboard', [\App\Http\Controllers\DashboardController::class, 'globalDashboard']);
Route::get('v1/clients/{client}/dashboard', [\App\Http\Controllers\DashboardController::class, 'clientDashboard']);

// Archives
Route::get('v1/archives/{semaineId}/transactions', [\App\Http\Controllers\ArchiveController::class, 'listBySemaine']);


<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompteController;
use App\Http\Controllers\AccountController;

// Register v1 routes via a closure and mount both under the production
// domain (API_HOST) and locally. routes/api.php is already served under the
// /api prefix by Laravel, so we register routes under /api/v1/...
// Use shared v1 routes so we can mount them in multiple places (domain + local prefix)
// The actual route definitions live in routes/v1_routes.php
// Note: routes defined there use the full paths starting with /api/v1/...

// Mount under production domain using the expected API prefix (/api/v1).
// Note: routes in this file are already prefixed with `/api` by the
// RouteServiceProvider, so here we only add the `v1` suffix.
Route::domain(env('API_HOST', 'fatou.wade'))->group(function () {
    Route::prefix('v1')->group(function () {
        require __DIR__ . '/v1_routes.php';
    });
});

// Also mount for general API access (when not using the API_HOST domain).
// This registers the same routes under /api/v1 on the current host.
Route::prefix('v1')->group(function () {
    require __DIR__ . '/v1_routes.php';
});

// Note: for local testing we mount the v1 routes without the automatic
// /api prefix (see routes/web.php) so the URL http://localhost:8000/fatou.wade/api/v1/...
// will work. Do not duplicate the local prefix here because routes in
// routes/api.php are automatically prefixed with /api by RouteServiceProvider.


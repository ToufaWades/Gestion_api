<?php

use Illuminate\Support\Facades\Route;

// Mount the API v1 routes for local testing under /fatou.wade/api/v1/...
Route::prefix('fatou.wade/api/v1')->group(function () {
    require __DIR__ . '/v1_routes.php';
});


Route::get('/', function () {
    return view('welcome');
});

// Documentation Swagger UI (static)
Route::get('/docs', function () {
    $path = public_path('docs/index.html');
    if (file_exists($path)) {
        return response()->file($path);
    }
    abort(404);
});

// Temporary protected diagnostic endpoint (requires DIAG_SECRET or APP_DEBUG=true)
Route::get('/fatou.wade/_diagnose', [\App\Http\Controllers\DiagController::class, 'index']);

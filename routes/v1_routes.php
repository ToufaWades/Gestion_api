<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompteController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;

// Routes pour /api/v1/... (définies ici pour réutilisation dans api.php et web.php)

// Wrap v1 routes with CORS middleware to allow cross-origin requests (useful for Swagger UI and external docs)
// NOTE: this file defines routes relative to the mount point. When included
// under a prefix like `api/v1` or `fatou.wade/api/v1` the final URIs will be
// constructed correctly. Do NOT hardcode `/v1` or `/api/v1` here.
Route::group([], function () {
    // Authentication routes
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('user', [AuthController::class, 'user'])->middleware('auth:sanctum');

    // Demo endpoint
    Route::get('comptes-demo', function () {
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'numero' => 'CPT-0001',
                    'solde' => '1000.00',
                    'type' => 'courant'
                ]
            ]
        ]);
    });

    // Public read-only endpoints
    Route::get('comptes', [CompteController::class, 'index']);
    Route::get('comptes/{numero}', [CompteController::class, 'show']);

    // Protected endpoints (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        // Mise à jour d'un compte
        Route::put('comptes/{id}', [CompteController::class, 'update']);
        Route::patch('comptes/{id}', [CompteController::class, 'update']);

        // Mise à jour des informations client d'un compte
        Route::patch('comptes/{compteId}/client', [CompteController::class, 'updateClientInfo']);

        // Suppression d'un compte
        Route::delete('comptes/{id}', [CompteController::class, 'destroy']);

        // Désarchiver un compte
        Route::post('comptes/{id}/desarchive', [CompteController::class, 'desarchive']);

        // Débloquer un compte
        Route::post('comptes/{id}/debloquer', [CompteController::class, 'debloquer']);

        // Account creation endpoint
        Route::post('accounts', [AccountController::class, 'store'])->middleware('logging');

        // Generic message sending
        Route::post('messages', [\App\Http\Controllers\MessageController::class, 'send'])->middleware('logging');

        Route::get('users/clients', [UserController::class, 'clients']);
        Route::get('users/admins', [UserController::class, 'admins']);
        Route::get('users/client/telephone/{telephone}', [UserController::class, 'clientByTelephone']);

        Route::get('health', [\App\Http\Controllers\HealthController::class, 'index']);

        Route::get('comptes/mes-comptes', [CompteController::class, 'mesComptes']);
        // Route d'archivage manuel supprimée, archivage uniquement via job

        // Blocage endpoints
        Route::post('comptes/{compte}/bloquer', [CompteController::class, 'bloquer']);
        Route::post('comptes/numero/{numero}/bloquer', [CompteController::class, 'bloquerByNumero']);

        // Endpoint: récupérer un compte par numéro
        Route::get('comptes/{numeroCompte}', [CompteController::class, 'showByNumero']);
    
        // Endpoint: récupérer les comptes archivés
        Route::get('comptes-archives', [CompteController::class, 'comptesArchives']);

        // Endpoint for listing archived accounts (admin only)
        Route::get('comptes-archives', [CompteController::class, 'comptesArchives']);
    });
});

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Services\MessageServiceInterface;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="Authentication endpoints"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/login",
     *     summary="Login user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="activation_code", type="string", description="Required for first login of new clients")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object"),
     *                 @OA\Property(property="token", type="string"),
     *                 @OA\Property(property="role", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'activation_code' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        // Vérification du mot de passe
        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Invalid credentials.'],
            ]);
        }

        // Gestion de l'activation pour les clients
        if ($user->isClient()) {
            $client = $user->client;
            if ($client && !$client->is_active) {
                // Première connexion, code requis
                if (!$request->has('activation_code') || $request->activation_code !== $client->code_activation) {
                    throw ValidationException::withMessages([
                        'activation_code' => ['Code d\'activation invalide ou manquant.'],
                    ]);
                }
                // Activation du client
                $client->is_active = true;
                $client->code_activation = null;
                $client->save();
            }
        }

        // Création du token
        $token = $user->createToken('API Token')->plainTextToken;
        $role = $user->isAdmin() ? 'admin' : 'client';

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'token' => $token,
                'role' => $role,
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/logout",
     *     summary="Logout user",
     *     tags={"Authentication"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Logout successful")
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user",
     *     summary="Get authenticated user",
     *     tags={"Authentication"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="User data",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    }
}
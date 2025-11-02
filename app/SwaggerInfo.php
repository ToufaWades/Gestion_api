/**
 * @OA\Post(
 *     path="/comptes",
 *     summary="Créer un compte bancaire avec client",
 *     tags={"Comptes"},
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"client_id", "type", "solde"},
 *             @OA\Property(property="client_id", type="integer"),
 *             @OA\Property(property="type", type="string"),
 *             @OA\Property(property="solde", type="number", format="float")
 *         )
 *     ),
 *     @OA\Response(response=201, description="Compte créé avec succès"),
 *     @OA\Response(response=400, description="Erreur de validation")
 * )
 *
 * @OA\Get(
 *     path="/users/clients",
 *     summary="Lister tous les clients",
 *     tags={"Users"},
 *     security={{"sanctum":{}}},
 *     @OA\Response(response=200, description="Liste des clients")
 * )
 *
 * @OA\Get(
 *     path="/users/admins",
 *     summary="Lister tous les admins",
 *     tags={"Users"},
 *     security={{"sanctum":{}}},
 *     @OA\Response(response=200, description="Liste des admins")
 * )
 *
 * @OA\Get(
 *     path="/users/client",
 *     summary="Trouver un client par téléphone ou NCI",
 *     tags={"Users"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="telephone",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="nci",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(response=200, description="Client trouvé"),
 *     @OA\Response(response=404, description="Client non trouvé")
 * )
 *
 * @OA\Post(
 *     path="/clients/change-password",
 *     summary="Changer le mot de passe (protégé)",
 *     tags={"Auth"},
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"old_password", "new_password", "new_password_confirmation"},
 *             @OA\Property(property="old_password", type="string"),
 *             @OA\Property(property="new_password", type="string"),
 *             @OA\Property(property="new_password_confirmation", type="string")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Mot de passe changé avec succès"),
 *     @OA\Response(response=422, description="Ancien mot de passe incorrect")
 * )
 */
<?php

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Compte API",
 *     description="Documentation de l'API de gestion des comptes bancaires"
 * )
 *
 * @OA\Server(
 *     url="/api/v1",
 *     description="API v1"
 * )
 */

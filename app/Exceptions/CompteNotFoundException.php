<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class CompteNotFoundException extends Exception
{
    public function __construct($numeroCompte)
    {
        parent::__construct("Le compte {$numeroCompte} est introuvable");
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'COMPTE_NOT_FOUND',
                'message' => $this->getMessage(),
            ],
        ], 404);
    }
}

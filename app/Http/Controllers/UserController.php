<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function clients(Request $request)
    {
        $users = User::whereHas('client')->with('client')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'pagination' => [
                'currentPage' => $users->currentPage(),
                'itemsPerPage' => $users->perPage(),
                'totalItems' => $users->total(),
                'totalPages' => $users->lastPage(),
            ]
        ]);
    }

    public function admins(Request $request)
    {
        $users = User::whereHas('admin')->with('admin')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'pagination' => [
                'currentPage' => $users->currentPage(),
                'itemsPerPage' => $users->perPage(),
                'totalItems' => $users->total(),
                'totalPages' => $users->lastPage(),
            ]
        ]);
    }

    // Récupérer un client à partir du numéro de téléphone
    public function clientByTelephone(Request $request, $telephone)
    {
        $user = User::whereHas('client', function ($q) use ($telephone) {
            $q->where('telephone', $telephone);
        })->with('client')->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'CLIENT_NOT_FOUND',
                    'message' => "Aucun client trouvé avec le téléphone $telephone"
                ]
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }
}

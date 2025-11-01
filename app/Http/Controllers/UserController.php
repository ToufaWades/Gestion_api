<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    // GET /api/v1/users/client/telephone/{telephone}
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
    // GET /api/v1/users/clients
    public function listClients(Request $request)
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

    // GET /api/v1/users/admins
    public function listAdmins(Request $request)
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

    // GET /api/v1/users/client?telephone=...&nci=...
    public function findClient(Request $request)
    {
        $telephone = $request->query('telephone');
        $nci = $request->query('nci');
        $query = User::whereHas('client');
        if ($telephone) {
            $query->whereHas('client', function ($q) use ($telephone) {
                $q->where('telephone', $telephone);
            });
        }
        if ($nci) {
            $query->whereHas('client', function ($q) use ($nci) {
                $q->where('nci', $nci);
            });
        }
        $user = $query->with('client')->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'CLIENT_NOT_FOUND',
                    'message' => 'Aucun client trouvé avec ces critères.'
                ]
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }
}

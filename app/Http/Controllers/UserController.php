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
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserApiController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query();

        if ($request->has('email')) {
            $users->where('email', 'like', '%' . $request->query('email') . '%');
        }

        return response()->json($users->paginate(10));
    }
}

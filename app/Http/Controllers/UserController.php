<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query();

        if ($request->has('email')) {
            $users->where('email', 'like', '%' . $request->query('email') . '%');
        }

        $users = $users->get();

        return view('user.index', compact('users'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users.index');
    }

    public function loginUser(Request $request)
    {
        $request->validate([
            'searchVal' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $request->searchVal)->first();

        if (!$user) {
            return redirect()->back()->with('error', 'Usuario no encontrado');
        }

        // Guardar admin original
        session(['original_admin_id' => auth()->id()]);

        // Cambiar sesión
        Auth::logout();
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Sesión iniciada como: ' . $user->name);
    }
}

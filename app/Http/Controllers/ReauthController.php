<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReauthController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'No hay sesión activa. Recarga la página.',
            ], 401);
        }

        if (! Auth::attempt(['email' => $user->email, 'password' => $request->password], false)) {
            return response()->json([
                'success' => false,
                'message' => 'Contraseña incorrecta.',
            ], 422);
        }

        $request->session()->regenerate();

        return response()->json(['success' => true]);
    }
}

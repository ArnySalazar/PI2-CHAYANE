<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $usuario = DB::table('users')
            ->where('email', $request->email)
            ->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            return response()->json([
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        $rol = DB::table('roles')
            ->where('id', $usuario->role_id)
            ->first();

        return response()->json([
            'message' => 'Login exitoso',
            'user' => [
                'id' => $usuario->id,
                'nombre' => $usuario->name,
                'email' => $usuario->email,
                'rol' => $rol->name ?? 'Usuario'  // ← CORREGIDO: name en vez de nombre
            ],
            'token' => 'token-' . $usuario->id . '-' . time()
        ], 200);
    }

    public function logout(Request $request)
    {
        return response()->json([
            'message' => 'Logout exitoso'
        ], 200);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => [
                'id' => 1,
                'nombre' => 'Administrador',
                'email' => 'admin@chayane.com',
                'rol' => 'Administrador'
            ]
        ], 200);
    }
}
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

        // Buscar usuario
        $user = DB::table('users')
            ->where('email', $request->email)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        // Obtener rol
        $rol = DB::table('roles')
            ->where('id', $user->role_id)
            ->first();

        // 🔥 OBTENER PERMISOS DEL ROL
        $permissions = DB::table('role_permissions')
            ->where('role_id', $user->role_id)
            ->get();

        return response()->json([
            'message' => 'Login exitoso',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'role_name' => $rol->name ?? 'Usuario',
                'permissions' => $permissions
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        return response()->json([
            'message' => 'Logout exitoso'
        ], 200);
    }
}
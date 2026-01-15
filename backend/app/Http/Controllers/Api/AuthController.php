<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 🔥 VERIFICACIÓN CORREGIDA - Usar Eloquent en lugar de DB::table
        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 401);
        }

        // 🔥 VERIFICACIÓN DE CONTRASEÑA CORREGIDA
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Contraseña incorrecta'
            ], 401);
        }

        // Obtener rol
        $rol = DB::table('roles')
            ->where('id', $user->role_id)
            ->first();

        // Obtener permisos del rol
        $permissions = DB::table('role_permissions')
            ->where('role_id', $user->role_id)
            ->get();

        // 🔥 INICIAR SESIÓN MANUALMENTE
        Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => 'Login exitoso',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'role_name' => $rol->nombre ?? 'Usuario',
                'permissions' => $permissions
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        
        return response()->json([
            'success' => true,
            'message' => 'Logout exitoso'
        ], 200);
    }
}
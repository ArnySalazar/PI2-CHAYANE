<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $module
     * @param  string  $action (crear, editar, eliminar, ver)
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $module, $action)
    {
        // Obtener ID del usuario desde el header
        $userId = $request->header('X-User-Id', 1);
        
        // Obtener usuario
        $user = DB::table('users')
            ->where('id', $userId)
            ->first();
        
        if (!$user) {
            return response()->json([
                'message' => 'Usuario no autorizado'
            ], 403);
        }
        
        // Admin (role_id = 1) tiene acceso total
        if ($user->role_id == 1) {
            return $next($request);
        }
        
        // Obtener permisos desde role_permissions
        $permission = DB::table('role_permissions')
            ->where('role_id', $user->role_id)
            ->where('module', $module)
            ->first();
        
        if (!$permission) {
            return response()->json([
                'message' => 'No tienes permisos para acceder a este módulo',
                'required_permission' => "$module.$action"
            ], 403);
        }
        
        // Mapear acciones a columnas
        $actionMap = [
            'crear' => 'can_create',
            'editar' => 'can_edit',
            'eliminar' => 'can_delete',
            'ver' => 'can_read'
        ];
        
        $column = $actionMap[$action] ?? null;
        
        if (!$column || !$permission->$column) {
            return response()->json([
                'message' => 'No tienes permisos para realizar esta acción',
                'required_permission' => "$module.$action",
                'your_permissions' => [
                    'can_read' => $permission->can_read,
                    'can_create' => $permission->can_create,
                    'can_edit' => $permission->can_edit,
                    'can_delete' => $permission->can_delete,
                ]
            ], 403);
        }
        
        return $next($request);
    }
}
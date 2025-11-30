<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\VentaController;

// ==========================================
// RUTAS DE PERMISOS
// ==========================================

Route::get('/user/permissions', function (Illuminate\Http\Request $request) {
    $userId = $request->header('X-User-Id', 1);
    
    $user = DB::table('users')
        ->join('roles', 'users.role_id', '=', 'roles.id')
        ->where('users.id', $userId)
        ->select('users.*', 'roles.nombre as rol_nombre', 'roles.permisos')
        ->first();
    
    if (!$user) {
        return response()->json(['message' => 'Usuario no encontrado'], 404);
    }
    
    $permissions = json_decode($user->permisos, true);
    
    return response()->json([
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role_id' => $user->role_id,
            'rol_nombre' => $user->rol_nombre
        ],
        'permissions' => $permissions
    ]);
});

Route::get('/user/permissions/{userId}', function ($userId) {
    $user = DB::table('users')
        ->join('roles', 'users.role_id', '=', 'roles.id')
        ->where('users.id', $userId)
        ->select('users.*', 'roles.nombre as rol_nombre', 'roles.permisos')
        ->first();
    
    if (!$user) {
        return response()->json(['message' => 'Usuario no encontrado'], 404);
    }
    
    $permissions = json_decode($user->permisos, true);
    
    return response()->json([
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role_id' => $user->role_id,
            'rol_nombre' => $user->rol_nombre
        ],
        'permissions' => $permissions
    ]);
});

// ==========================================
// RUTAS PÚBLICAS
// ==========================================

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/menu-publico', [App\Http\Controllers\Api\MenuPublicoController::class, 'index']);
Route::get('/menu-publico/categorias', [App\Http\Controllers\Api\MenuPublicoController::class, 'categorias']);


Route::get('/test', function () {
    return response()->json([
        'message' => 'API funcionando correctamente',
        'timestamp' => now()
    ]);
});

// ==========================================
// PRODUCTOS - Lectura libre, escritura protegida
// ==========================================

Route::get('/productos', [ProductoController::class, 'index']);
Route::get('/productos/{id}', [ProductoController::class, 'show']);
Route::get('/categorias', [ProductoController::class, 'categorias']);

// Solo admin y gerente pueden crear/editar/eliminar productos
Route::post('/productos', [ProductoController::class, 'store'])
    ->middleware('check.permissions:productos,crear');
Route::put('/productos/{id}', [ProductoController::class, 'update'])
    ->middleware('check.permissions:productos,editar');
Route::delete('/productos/{id}', [ProductoController::class, 'destroy'])
    ->middleware('check.permissions:productos,eliminar');

// ==========================================
// DASHBOARD
// ==========================================

Route::get('/dashboard', [DashboardController::class, 'index']);

// ==========================================
// VENTAS
// ==========================================

Route::get('/ventas', [VentaController::class, 'index']);
Route::post('/ventas', [VentaController::class, 'store'])
    ->middleware('check.permissions:ventas,crear');
Route::get('/ventas/{id}', [VentaController::class, 'show']);
Route::post('/ventas/{id}/cancel', [VentaController::class, 'cancel'])
    ->middleware('check.permissions:ventas,eliminar');
Route::get('/ventas-stats', [VentaController::class, 'stats']);

// ==========================================
// REPORTES - Solo admin y gerente
// ==========================================

Route::get('/reportes/dashboard', [App\Http\Controllers\Api\ReporteController::class, 'dashboard'])
    ->middleware('check.permissions:reportes,ver');
Route::post('/reportes/ventas-por-fecha', [App\Http\Controllers\Api\ReporteController::class, 'ventasPorFecha'])
    ->middleware('check.permissions:reportes,ver');
Route::get('/reportes/productos-mas-vendidos', [App\Http\Controllers\Api\ReporteController::class, 'productosMasVendidos'])
    ->middleware('check.permissions:reportes,ver');
Route::get('/reportes/inventario', [App\Http\Controllers\Api\ReporteController::class, 'inventario'])
    ->middleware('check.permissions:reportes,ver');
Route::get('/reportes/ventas-por-categoria', [App\Http\Controllers\Api\ReporteController::class, 'ventasPorCategoria'])
    ->middleware('check.permissions:reportes,ver');

// ==========================================
// INSUMOS
// ==========================================

Route::post('/insumos/movimiento', [App\Http\Controllers\Api\InsumoController::class, 'registrarMovimiento'])
    ->middleware('check.permissions:insumos,editar');
Route::get('/insumos/{id}/movimientos', [App\Http\Controllers\Api\InsumoController::class, 'movimientos']);
Route::get('/insumos', [App\Http\Controllers\Api\InsumoController::class, 'index']);
Route::get('/insumos/{id}', [App\Http\Controllers\Api\InsumoController::class, 'show']);
Route::get('/insumos-categorias', [App\Http\Controllers\Api\InsumoController::class, 'categorias']);
Route::get('/insumos-stats', [App\Http\Controllers\Api\InsumoController::class, 'stats']);
Route::get('/insumos-stock-bajo', [App\Http\Controllers\Api\InsumoController::class, 'stockBajo']);

// Solo admin y gerente pueden crear/editar/eliminar insumos
Route::post('/insumos', [App\Http\Controllers\Api\InsumoController::class, 'store'])
    ->middleware('check.permissions:insumos,crear');
Route::put('/insumos/{id}', [App\Http\Controllers\Api\InsumoController::class, 'update'])
    ->middleware('check.permissions:insumos,editar');
Route::delete('/insumos/{id}', [App\Http\Controllers\Api\InsumoController::class, 'destroy'])
    ->middleware('check.permissions:insumos,eliminar');

// ==========================================
// CLIENTES
// ==========================================

Route::get('/clientes', [App\Http\Controllers\Api\ClienteController::class, 'index']);
Route::get('/clientes/{id}', [App\Http\Controllers\Api\ClienteController::class, 'show']);
Route::get('/clientes/buscar/{documento}', [App\Http\Controllers\Api\ClienteController::class, 'buscarPorDocumento']);
Route::get('/clientes-stats', [App\Http\Controllers\Api\ClienteController::class, 'stats']);
Route::get('/clientes-frecuentes', [App\Http\Controllers\Api\ClienteController::class, 'clientesFrecuentes']);

// Meseras pueden crear clientes, pero no editar/eliminar
Route::post('/clientes', [App\Http\Controllers\Api\ClienteController::class, 'store'])
    ->middleware('check.permissions:clientes,crear');
Route::put('/clientes/{id}', [App\Http\Controllers\Api\ClienteController::class, 'update'])
    ->middleware('check.permissions:clientes,editar');
Route::delete('/clientes/{id}', [App\Http\Controllers\Api\ClienteController::class, 'destroy'])
    ->middleware('check.permissions:clientes,eliminar');

// ==========================================
// COCINA
// ==========================================

Route::get('/cocina/pedidos', [App\Http\Controllers\Api\CocinaController::class, 'pedidos']);
Route::get('/cocina/completados', [App\Http\Controllers\Api\CocinaController::class, 'pedidosCompletados']);
Route::get('/cocina/stats', [App\Http\Controllers\Api\CocinaController::class, 'stats']);

// Solo cocineros pueden cambiar estados de cocina
Route::post('/cocina/pedidos/{id}/estado', [App\Http\Controllers\Api\CocinaController::class, 'cambiarEstado'])
    ->middleware('check.permissions:cocina,editar');
Route::post('/cocina/pedidos/{id}/iniciar', [App\Http\Controllers\Api\CocinaController::class, 'iniciarPreparacion'])
    ->middleware('check.permissions:cocina,editar');
Route::post('/cocina/pedidos/{id}/listo', [App\Http\Controllers\Api\CocinaController::class, 'marcarListo'])
    ->middleware('check.permissions:cocina,editar');

// ==========================================
// MESAS
// ==========================================

Route::get('/mesas', [App\Http\Controllers\Api\MesaController::class, 'index']);
Route::get('/mesas/{id}', [App\Http\Controllers\Api\MesaController::class, 'show']);
Route::get('/mesas-stats', [App\Http\Controllers\Api\MesaController::class, 'stats']);

// Solo admin y gerente pueden crear/eliminar mesas
Route::post('/mesas', [App\Http\Controllers\Api\MesaController::class, 'store'])
    ->middleware('check.permissions:mesas,crear');
Route::delete('/mesas/{id}', [App\Http\Controllers\Api\MesaController::class, 'destroy'])
    ->middleware('check.permissions:mesas,eliminar');

// Meseras y cajeras pueden editar estados de mesas
Route::put('/mesas/{id}', [App\Http\Controllers\Api\MesaController::class, 'update'])
    ->middleware('check.permissions:mesas,editar');
Route::post('/mesas/{id}/estado', [App\Http\Controllers\Api\MesaController::class, 'cambiarEstado'])
    ->middleware('check.permissions:mesas,editar');
Route::post('/mesas/{id}/liberar', [App\Http\Controllers\Api\MesaController::class, 'liberar'])
    ->middleware('check.permissions:mesas,editar');
Route::post('/mesas/{id}/transferir', [App\Http\Controllers\Api\MesaController::class, 'transferir'])
    ->middleware('check.permissions:mesas,editar');
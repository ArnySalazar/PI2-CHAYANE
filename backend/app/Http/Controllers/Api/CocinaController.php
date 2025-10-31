<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CocinaController extends Controller
{
    /**
     * Obtener todos los pedidos para cocina
     */
    public function pedidos()
    {
        $pedidos = DB::table('ventas as v')
            ->leftJoin('users as u', 'v.usuario_id', '=', 'u.id')
            ->whereIn('v.estado_cocina', ['pendiente', 'en_preparacion'])
            ->where('v.estado', '!=', 'cancelada')
            ->select(
                'v.id',
                'v.numero_venta',
                'v.fecha',
                'v.total',
                'v.estado',
                'v.estado_cocina',
                'v.mesa_id',
                'v.cliente_nombre',
                'v.notas_cocina',
                'v.prioridad',
                'u.name as mesero_nombre',
                DB::raw('EXTRACT(EPOCH FROM (NOW() - v.fecha))/60 as minutos_espera')
            )
            ->orderByRaw("
                CASE v.prioridad 
                    WHEN 'urgente' THEN 1 
                    WHEN 'alta' THEN 2 
                    ELSE 3 
                END
            ")
            ->orderBy('v.fecha', 'asc')
            ->get();

        // Obtener detalles de cada pedido
        foreach ($pedidos as $pedido) {
            $pedido->detalles = DB::table('detalle_ventas as dv')
                ->join('productos as p', 'dv.producto_id', '=', 'p.id')
                ->where('dv.venta_id', $pedido->id)
                ->select(
                    'dv.id',
                    'dv.cantidad',
                    'p.nombre as producto_nombre',
                    'p.categoria_id'
                )
                ->get();
        }

        return response()->json($pedidos);
    }

    /**
     * Cambiar estado de un pedido
     */
    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'estado_cocina' => 'required|in:pendiente,en_preparacion,listo'
        ]);

        try {
            $updated = DB::table('ventas')
                ->where('id', $id)
                ->update([
                    'estado_cocina' => $request->estado_cocina,
                    'updated_at' => now()
                ]);

            if (!$updated) {
                return response()->json(['message' => 'Pedido no encontrado'], 404);
            }

            $pedido = DB::table('ventas')
                ->where('id', $id)
                ->first();

            return response()->json([
                'message' => 'Estado actualizado correctamente',
                'pedido' => $pedido
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar estado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar pedido como en preparación
     */
    public function iniciarPreparacion($id)
    {
        try {
            DB::table('ventas')
                ->where('id', $id)
                ->update([
                    'estado_cocina' => 'en_preparacion',
                    'updated_at' => now()
                ]);

            return response()->json([
                'message' => 'Pedido marcado como en preparación'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al iniciar preparación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar pedido como listo
     */
    public function marcarListo($id)
    {
        try {
            DB::table('ventas')
                ->where('id', $id)
                ->update([
                    'estado_cocina' => 'listo',
                    'updated_at' => now()
                ]);

            return response()->json([
                'message' => 'Pedido marcado como listo'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al marcar como listo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener pedidos completados (últimas 24 horas)
     */
    public function pedidosCompletados()
    {
        $pedidos = DB::table('ventas')
            ->where('estado_cocina', 'listo')
            ->where('fecha', '>=', DB::raw("NOW() - INTERVAL '24 hours'"))
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json($pedidos);
    }

    /**
     * Estadísticas de cocina
     */
    public function stats()
    {
        $stats = [
            'pedidos_pendientes' => DB::table('ventas')
                ->where('estado_cocina', 'pendiente')
                ->where('estado', '!=', 'cancelada')
                ->count(),

            'pedidos_en_preparacion' => DB::table('ventas')
                ->where('estado_cocina', 'en_preparacion')
                ->count(),

            'pedidos_listos_hoy' => DB::table('ventas')
                ->where('estado_cocina', 'listo')
                ->whereDate('updated_at', today())
                ->count(),

            'tiempo_promedio_preparacion' => DB::table('ventas')
                ->where('estado_cocina', 'listo')
                ->whereDate('fecha', today())
                ->selectRaw('AVG(EXTRACT(EPOCH FROM (updated_at - fecha))/60) as promedio')
                ->value('promedio'),

            'productos_mas_pedidos_hoy' => DB::table('detalle_ventas as dv')
                ->join('productos as p', 'dv.producto_id', '=', 'p.id')
                ->join('ventas as v', 'dv.venta_id', '=', 'v.id')
                ->whereDate('v.fecha', today())
                ->select(
                    'p.nombre',
                    DB::raw('SUM(dv.cantidad) as total')
                )
                ->groupBy('p.id', 'p.nombre')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get()
        ];

        return response()->json($stats);
    }
}
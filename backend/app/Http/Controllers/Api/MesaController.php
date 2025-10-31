<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MesaController extends Controller
{
    /**
     * Listar todas las mesas con información de pedidos activos
     */
    public function index()
    {
        $mesas = DB::table('mesas')
            ->leftJoin('ventas', function($join) {
                $join->on('mesas.id', '=', 'ventas.mesa_id')
                     ->whereIn('ventas.estado_cocina', ['pendiente', 'en_preparacion'])
                     ->where('ventas.estado', '!=', 'cancelada');
            })
            ->select(
                'mesas.*',
                DB::raw('COUNT(ventas.id) as pedidos_activos'),
                DB::raw('SUM(ventas.total) as total_cuenta'),
                DB::raw('MIN(ventas.fecha) as hora_inicio')
            )
            ->groupBy('mesas.id', 'mesas.numero', 'mesas.capacidad', 'mesas.estado', 'mesas.ubicacion', 'mesas.notas', 'mesas.created_at', 'mesas.updated_at')
            ->orderBy('mesas.numero')
            ->get();

        return response()->json($mesas);
    }

    /**
     * Obtener detalles de una mesa específica
     */
    public function show($id)
    {
        $mesa = DB::table('mesas')->where('id', $id)->first();

        if (!$mesa) {
            return response()->json(['message' => 'Mesa no encontrada'], 404);
        }

        // Obtener pedidos activos de la mesa
        $pedidos = DB::table('ventas')
            ->leftJoin('users', 'ventas.usuario_id', '=', 'users.id')
            ->where('ventas.mesa_id', $id)
            ->whereIn('ventas.estado_cocina', ['pendiente', 'en_preparacion', 'listo'])
            ->where('ventas.estado', '!=', 'cancelada')
            ->select('ventas.*', 'users.name as mesero_nombre')
            ->get();

        // Obtener detalles de cada pedido
        foreach ($pedidos as $pedido) {
            $pedido->detalles = DB::table('detalle_ventas as dv')
                ->join('productos as p', 'dv.producto_id', '=', 'p.id')
                ->where('dv.venta_id', $pedido->id)
                ->select('dv.*', 'p.nombre as producto_nombre')
                ->get();
        }

        return response()->json([
            'mesa' => $mesa,
            'pedidos' => $pedidos,
            'total_cuenta' => $pedidos->sum('total')
        ]);
    }

    /**
     * Crear una nueva mesa
     */
    public function store(Request $request)
    {
        $request->validate([
            'numero' => 'required|integer|unique:mesas,numero',
            'capacidad' => 'required|integer|min:1',
            'ubicacion' => 'nullable|string|max:50'
        ]);

        $id = DB::table('mesas')->insertGetId([
            'numero' => $request->numero,
            'capacidad' => $request->capacidad,
            'estado' => 'libre',
            'ubicacion' => $request->ubicacion,
            'notas' => $request->notas,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $mesa = DB::table('mesas')->where('id', $id)->first();

        return response()->json([
            'message' => 'Mesa creada correctamente',
            'mesa' => $mesa
        ], 201);
    }

    /**
     * Actualizar una mesa
     */
    public function update(Request $request, $id)
    {
        $mesa = DB::table('mesas')->where('id', $id)->first();

        if (!$mesa) {
            return response()->json(['message' => 'Mesa no encontrada'], 404);
        }

        $request->validate([
            'numero' => 'sometimes|integer|unique:mesas,numero,' . $id,
            'capacidad' => 'sometimes|integer|min:1',
            'estado' => 'sometimes|in:libre,ocupada,reservada',
            'ubicacion' => 'nullable|string|max:50'
        ]);

        DB::table('mesas')
            ->where('id', $id)
            ->update([
                'numero' => $request->numero ?? $mesa->numero,
                'capacidad' => $request->capacidad ?? $mesa->capacidad,
                'estado' => $request->estado ?? $mesa->estado,
                'ubicacion' => $request->ubicacion ?? $mesa->ubicacion,
                'notas' => $request->notas ?? $mesa->notas,
                'updated_at' => now()
            ]);

        $mesaActualizada = DB::table('mesas')->where('id', $id)->first();

        return response()->json([
            'message' => 'Mesa actualizada correctamente',
            'mesa' => $mesaActualizada
        ]);
    }

    /**
     * Cambiar estado de una mesa
     */
    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:libre,ocupada,reservada'
        ]);

        DB::table('mesas')
            ->where('id', $id)
            ->update([
                'estado' => $request->estado,
                'updated_at' => now()
            ]);

        return response()->json([
            'message' => 'Estado actualizado correctamente'
        ]);
    }

    /**
     * Liberar mesa (cerrar cuenta)
     */
    public function liberar($id)
    {
        try {
            // Actualizar estado de la mesa
            DB::table('mesas')
                ->where('id', $id)
                ->update([
                    'estado' => 'libre',
                    'updated_at' => now()
                ]);

            // Marcar pedidos como completados
            DB::table('ventas')
                ->where('mesa_id', $id)
                ->whereIn('estado_cocina', ['pendiente', 'en_preparacion', 'listo'])
                ->update([
                    'estado_cocina' => 'listo',
                    'estado' => 'completada',
                    'updated_at' => now()
                ]);

            return response()->json([
                'message' => 'Mesa liberada correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al liberar mesa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transferir pedidos entre mesas
     */
    public function transferir(Request $request, $id)
    {
        $request->validate([
            'mesa_destino_id' => 'required|exists:mesas,id'
        ]);

        try {
            // Verificar que la mesa destino no sea la misma
            if ($id == $request->mesa_destino_id) {
                return response()->json([
                    'message' => 'No se puede transferir a la misma mesa'
                ], 400);
            }

            // Transferir pedidos
            DB::table('ventas')
                ->where('mesa_id', $id)
                ->whereIn('estado_cocina', ['pendiente', 'en_preparacion', 'listo'])
                ->update([
                    'mesa_id' => $request->mesa_destino_id,
                    'updated_at' => now()
                ]);

            // Actualizar estados de mesas
            DB::table('mesas')->where('id', $id)->update(['estado' => 'libre']);
            DB::table('mesas')->where('id', $request->mesa_destino_id)->update(['estado' => 'ocupada']);

            return response()->json([
                'message' => 'Pedidos transferidos correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al transferir pedidos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas de mesas
     */
    public function stats()
    {
        $stats = [
            'total_mesas' => DB::table('mesas')->count(),
            'mesas_libres' => DB::table('mesas')->where('estado', 'libre')->count(),
            'mesas_ocupadas' => DB::table('mesas')->where('estado', 'ocupada')->count(),
            'mesas_reservadas' => DB::table('mesas')->where('estado', 'reservada')->count(),
            'ocupacion_porcentaje' => 0
        ];

        if ($stats['total_mesas'] > 0) {
            $stats['ocupacion_porcentaje'] = round(
                ($stats['mesas_ocupadas'] / $stats['total_mesas']) * 100,
                2
            );
        }

        return response()->json($stats);
    }

    /**
     * Eliminar una mesa
     */
    public function destroy($id)
    {
        // Verificar que no tenga pedidos activos
        $pedidosActivos = DB::table('ventas')
            ->where('mesa_id', $id)
            ->whereIn('estado_cocina', ['pendiente', 'en_preparacion'])
            ->count();

        if ($pedidosActivos > 0) {
            return response()->json([
                'message' => 'No se puede eliminar una mesa con pedidos activos'
            ], 400);
        }

        DB::table('mesas')->where('id', $id)->delete();

        return response()->json([
            'message' => 'Mesa eliminada correctamente'
        ]);
    }
}
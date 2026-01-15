<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    /**
     * Listar todos los clientes
     */
    public function index()
    {
        $clientes = DB::table('clientes')
            ->where('estado', true)
            ->orderBy('nombre_completo')
            ->get();

        // Agregar estadísticas de compras a cada cliente
        foreach ($clientes as $cliente) {
            $stats = DB::table('ventas')
                ->where('cliente_id', $cliente->id)
                ->where('estado', 'completada')
                ->selectRaw('
                    COUNT(*) as total_compras,
                    SUM(subtotal) as total_gastado,
                    MAX(fecha) as ultima_compra
                ')
                ->first();

            $cliente->total_compras = $stats->total_compras ?? 0;
            $cliente->total_gastado = $stats->total_gastado ?? 0;
            $cliente->ultima_compra = $stats->ultima_compra;
        }

        return response()->json($clientes);
    }

    /**
     * Crear nuevo cliente
     */
    public function store(Request $request)
    {
        $request->validate([
            'tipo_documento' => 'required|in:DNI,RUC,CE,Pasaporte',
            'numero_documento' => 'nullable|unique:clientes,numero_documento',
            'nombres' => 'required|string|max:200',
            'apellidos' => 'nullable|string|max:200',
            'email' => 'nullable|email|max:200',
            'telefono' => 'nullable|string|max:20'
        ]);

        try {
            // Generar código automático
            $ultimoCliente = DB::table('clientes')->orderBy('id', 'desc')->first();
            $codigo = 'CLI-' . str_pad(($ultimoCliente->id ?? 0) + 1, 6, '0', STR_PAD_LEFT);

            // Construir nombre completo
            $nombreCompleto = trim($request->nombres . ' ' . ($request->apellidos ?? ''));

            $id = DB::table('clientes')->insertGetId([
                'codigo' => $codigo,
                'tipo_documento' => $request->tipo_documento,
                'numero_documento' => $request->numero_documento,
                'nombres' => $request->nombres,
                'apellidos' => $request->apellidos,
                'nombre_completo' => $nombreCompleto,
                'email' => $request->email,
                'telefono' => $request->telefono,
                'direccion' => $request->direccion,
                'distrito' => $request->distrito,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'observaciones' => $request->observaciones,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $cliente = DB::table('clientes')->where('id', $id)->first();

            return response()->json([
                'message' => 'Cliente creado exitosamente',
                'cliente' => $cliente
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un cliente específico
     */
    public function show($id)
    {
        $cliente = DB::table('clientes')
            ->where('id', $id)
            ->first();

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        // Obtener estadísticas de compras
        $stats = DB::table('ventas')
            ->where('cliente_id', $id)
            ->where('estado', 'completada')
            ->selectRaw('
                COUNT(*) as total_compras,
                SUM(subtotal) as total_gastado,
                MAX(fecha) as ultima_compra,
                MIN(fecha) as primera_compra
            ')
            ->first();

        $cliente->estadisticas = [
            'total_compras' => $stats->total_compras ?? 0,
            'total_gastado' => $stats->total_gastado ?? 0,
            'ultima_compra' => $stats->ultima_compra,
            'primera_compra' => $stats->primera_compra
        ];

        // Obtener historial de compras
        $cliente->historial_compras = DB::table('ventas')
            ->where('cliente_id', $id)
            ->select('id', 'numero_venta', 'fecha', 'subtotal', 'estado')
            ->orderBy('fecha', 'desc')
            ->limit(10)
            ->get();

        return response()->json($cliente);
    }

    /**
     * Actualizar cliente
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'tipo_documento' => 'required|in:DNI,RUC,CE,Pasaporte',
            'nombres' => 'required|string|max:200',
            'email' => 'nullable|email|max:200',
            'telefono' => 'nullable|string|max:20'
        ]);

        try {
            // Construir nombre completo
            $nombreCompleto = trim($request->nombres . ' ' . ($request->apellidos ?? ''));

            $updated = DB::table('clientes')
                ->where('id', $id)
                ->update([
                    'tipo_documento' => $request->tipo_documento,
                    'numero_documento' => $request->numero_documento,
                    'nombres' => $request->nombres,
                    'apellidos' => $request->apellidos,
                    'nombre_completo' => $nombreCompleto,
                    'email' => $request->email,
                    'telefono' => $request->telefono,
                    'direccion' => $request->direccion,
                    'distrito' => $request->distrito,
                    'fecha_nacimiento' => $request->fecha_nacimiento,
                    'observaciones' => $request->observaciones,
                    'updated_at' => now()
                ]);

            if (!$updated) {
                return response()->json(['message' => 'Cliente no encontrado'], 404);
            }

            $cliente = DB::table('clientes')->where('id', $id)->first();

            return response()->json([
                'message' => 'Cliente actualizado exitosamente',
                'cliente' => $cliente
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar cliente (soft delete)
     */
    public function destroy($id)
    {
        try {
            // Verificar si tiene ventas asociadas
            $tieneVentas = DB::table('ventas')
                ->where('cliente_id', $id)
                ->exists();

            if ($tieneVentas) {
                return response()->json([
                    'message' => 'No se puede eliminar el cliente porque tiene ventas asociadas'
                ], 400);
            }

            $deleted = DB::table('clientes')
                ->where('id', $id)
                ->update([
                    'estado' => false,
                    'updated_at' => now()
                ]);

            if (!$deleted) {
                return response()->json(['message' => 'Cliente no encontrado'], 404);
            }

            return response()->json(['message' => 'Cliente eliminado exitosamente']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar cliente por documento
     */
    public function buscarPorDocumento($documento)
    {
        $cliente = DB::table('clientes')
            ->where('numero_documento', $documento)
            ->where('estado', true)
            ->first();

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        return response()->json($cliente);
    }

    /**
     * Estadísticas de clientes
     */
    public function stats()
    {
        $stats = [
            'total_clientes' => DB::table('clientes')
                ->where('estado', true)
                ->count(),

            'clientes_nuevos_mes' => DB::table('clientes')
                ->where('estado', true)
                ->whereYear('created_at', date('Y'))
                ->whereMonth('created_at', date('m'))
                ->count(),

            'clientes_con_compras' => DB::table('clientes')
                ->join('ventas', 'clientes.id', '=', 'ventas.cliente_id')
                ->where('clientes.estado', true)
                ->where('ventas.estado', 'completada')
                ->distinct('clientes.id')
                ->count('clientes.id'),

            'top_clientes' => DB::table('clientes')
                ->join('ventas', 'clientes.id', '=', 'ventas.cliente_id')
                ->where('clientes.estado', true)
                ->where('ventas.estado', 'completada')
                ->select(
                    'clientes.id',
                    'clientes.nombre_completo',
                    'clientes.numero_documento',
                    DB::raw('COUNT(ventas.id) as total_compras'),
                    DB::raw('SUM(ventas.total) as total_gastado')
                )
                ->groupBy('clientes.id', 'clientes.nombre_completo', 'clientes.numero_documento')
                ->orderBy('total_gastado', 'desc')
                ->limit(10)
                ->get()
        ];

        return response()->json($stats);
    }

    /**
     * Clientes frecuentes
     */
    public function clientesFrecuentes()
    {
        $clientes = DB::table('clientes')
            ->join('ventas', 'clientes.id', '=', 'ventas.cliente_id')
            ->where('clientes.estado', true)
            ->where('ventas.estado', 'completada')
            ->select(
                'clientes.*',
                DB::raw('COUNT(ventas.id) as total_compras'),
                DB::raw('SUM(ventas.subtotal) as total_gastado'),
                DB::raw('MAX(ventas.fecha) as ultima_visita')
            )
            ->groupBy('clientes.id')
            ->having(DB::raw('COUNT(ventas.id)'), '>=', 3)
            ->orderBy('total_compras', 'desc')
            ->get();

        return response()->json($clientes);
    }
}
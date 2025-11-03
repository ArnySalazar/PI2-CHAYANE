<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GastoController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('gastos as g')
            ->leftJoin('categorias_gastos as cg', 'g.categoria_gasto_id', '=', 'cg.id')
            ->leftJoin('proveedores as p', 'g.proveedor_id', '=', 'p.id')
            ->leftJoin('users as u', 'g.usuario_id', '=', 'u.id')
            ->select(
                'g.*',
                'cg.nombre as categoria_nombre',
                'p.nombre_comercial as proveedor_nombre',
                'u.name as usuario_nombre'
            );

        // Filtros
        if ($request->has('categoria_id')) {
            $query->where('g.categoria_gasto_id', $request->categoria_id);
        }

        if ($request->has('fecha_desde')) {
            $query->where('g.fecha', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta')) {
            $query->where('g.fecha', '<=', $request->fecha_hasta);
        }

        if ($request->has('busqueda')) {
            $busqueda = $request->busqueda;
            $query->where(function($q) use ($busqueda) {
                $q->where('g.concepto', 'ILIKE', "%{$busqueda}%")
                  ->orWhere('g.numero_gasto', 'ILIKE', "%{$busqueda}%")
                  ->orWhere('p.nombre_comercial', 'ILIKE', "%{$busqueda}%");
            });
        }

        $gastos = $query->orderBy('g.fecha', 'desc')->get();

        return response()->json($gastos);
    }

    public function store(Request $request)
    {
        $request->validate([
            'concepto' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date',
            'categoria_gasto_id' => 'required|exists:categorias_gastos,id',
            'metodo_pago' => 'required|string',
            'proveedor_id' => 'nullable|exists:proveedores,id',
        ]);

        try {
            DB::beginTransaction();

            $ultimoGasto = DB::table('gastos')->orderBy('id', 'desc')->first();
            $numeroGasto = 'G-' . str_pad(($ultimoGasto->id ?? 0) + 1, 6, '0', STR_PAD_LEFT);

            $userId = $request->header('X-User-Id', 1);

            $gastoId = DB::table('gastos')->insertGetId([
                'numero_gasto' => $numeroGasto,
                'categoria_gasto_id' => $request->categoria_gasto_id,
                'concepto' => $request->concepto,
                'monto' => $request->monto,
                'fecha' => $request->fecha,
                'proveedor_id' => $request->proveedor_id,
                'comprobante' => $request->comprobante,
                'metodo_pago' => $request->metodo_pago,
                'estado' => $request->estado ?? 'pagado',
                'observaciones' => $request->observaciones,
                'usuario_id' => $userId,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            $gasto = DB::table('gastos')->where('id', $gastoId)->first();

            return response()->json([
                'message' => 'Gasto registrado exitosamente',
                'gasto' => $gasto
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al registrar gasto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $gasto = DB::table('gastos as g')
            ->leftJoin('categorias_gastos as cg', 'g.categoria_gasto_id', '=', 'cg.id')
            ->leftJoin('proveedores as p', 'g.proveedor_id', '=', 'p.id')
            ->leftJoin('users as u', 'g.usuario_id', '=', 'u.id')
            ->select(
                'g.*',
                'cg.nombre as categoria_nombre',
                'p.nombre_comercial as proveedor_nombre',
                'p.ruc as proveedor_ruc',
                'u.name as usuario_nombre'
            )
            ->where('g.id', $id)
            ->first();

        if (!$gasto) {
            return response()->json(['message' => 'Gasto no encontrado'], 404);
        }

        return response()->json($gasto);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'concepto' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date',
            'categoria_gasto_id' => 'required|exists:categorias_gastos,id',
            'metodo_pago' => 'required|string',
        ]);

        try {
            DB::table('gastos')
                ->where('id', $id)
                ->update([
                    'concepto' => $request->concepto,
                    'monto' => $request->monto,
                    'fecha' => $request->fecha,
                    'categoria_gasto_id' => $request->categoria_gasto_id,
                    'proveedor_id' => $request->proveedor_id,
                    'comprobante' => $request->comprobante,
                    'metodo_pago' => $request->metodo_pago,
                    'estado' => $request->estado,
                    'observaciones' => $request->observaciones,
                    'updated_at' => now()
                ]);

            return response()->json(['message' => 'Gasto actualizado exitosamente']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar gasto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::table('gastos')->where('id', $id)->delete();
            return response()->json(['message' => 'Gasto eliminado exitosamente']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar gasto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function stats()
    {
        $hoy = date('Y-m-d');
        
        $stats = [
            'gastos_hoy' => DB::table('gastos')
                ->whereDate('fecha', $hoy)
                ->sum('monto') ?? 0,
            
            'gastos_semana' => DB::table('gastos')
                ->whereBetween('fecha', [
                    date('Y-m-d', strtotime('monday this week')),
                    date('Y-m-d', strtotime('sunday this week'))
                ])
                ->sum('monto') ?? 0,
            
            'gastos_mes' => DB::table('gastos')
                ->whereYear('fecha', date('Y'))
                ->whereMonth('fecha', date('m'))
                ->sum('monto') ?? 0,
            
            'gastos_anio' => DB::table('gastos')
                ->whereYear('fecha', date('Y'))
                ->sum('monto') ?? 0,

            'por_categoria' => DB::table('gastos as g')
                ->join('categorias_gastos as cg', 'g.categoria_gasto_id', '=', 'cg.id')
                ->whereYear('g.fecha', date('Y'))
                ->whereMonth('g.fecha', date('m'))
                ->select(
                    'cg.nombre as categoria',
                    DB::raw('SUM(g.monto) as total'),
                    DB::raw('COUNT(*) as cantidad')
                )
                ->groupBy('cg.id', 'cg.nombre')
                ->orderBy('total', 'desc')
                ->get()
        ];

        return response()->json($stats);
    }

    public function categorias()
    {
        $categorias = DB::table('categorias_gastos')
            ->orderBy('nombre')
            ->get();
        
        return response()->json($categorias);
    }
}
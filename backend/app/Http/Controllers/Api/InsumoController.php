<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsumoController extends Controller
{
    /**
     * Listar todos los insumos
     */
    public function index()
    {
        $insumos = DB::table('insumos')
            ->join('categorias_insumos', 'insumos.categoria_id', '=', 'categorias_insumos.id')
            ->select(
                'insumos.*',
                'categorias_insumos.nombre as categoria_nombre',
                DB::raw('CASE 
                    WHEN insumos.stock_actual < insumos.stock_minimo THEN \'bajo\'
                    WHEN insumos.stock_actual = 0 THEN \'agotado\'
                    ELSE \'ok\'
                END as estado_stock')
            )
            ->where('insumos.estado', true)
            ->orderBy('insumos.nombre')
            ->get();

        return response()->json($insumos);
    }

    /**
     * Crear nuevo insumo
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:200',
            'categoria_id' => 'required|integer',
            'unidad_medida' => 'required|string|max:20',
            'stock_actual' => 'required|numeric|min:0',
            'stock_minimo' => 'required|numeric|min:0',
        ]);

        try {
            // Generar código automático
            $ultimoInsumo = DB::table('insumos')->orderBy('id', 'desc')->first();
            $codigo = 'INS-' . str_pad(($ultimoInsumo->id ?? 0) + 1, 6, '0', STR_PAD_LEFT);

            $id = DB::table('insumos')->insertGetId([
                'codigo' => $codigo,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'categoria_id' => $request->categoria_id,
                'unidad_medida' => $request->unidad_medida,
                'stock_actual' => $request->stock_actual,
                'stock_minimo' => $request->stock_minimo,
                'stock_maximo' => $request->stock_maximo,
                'precio_compra' => $request->precio_compra ?? 0,
                'proveedor' => $request->proveedor,
                'ubicacion' => $request->ubicacion,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Registrar movimiento inicial
            if ($request->stock_actual > 0) {
                DB::table('movimientos_inventario')->insert([
                    'insumo_id' => $id,
                    'tipo_movimiento' => 'entrada',
                    'cantidad' => $request->stock_actual,
                    'motivo' => 'inicial',
                    'descripcion' => 'Stock inicial',
                    'stock_anterior' => 0,
                    'stock_nuevo' => $request->stock_actual,
                    'precio_unitario' => $request->precio_compra ?? 0,
                    'total' => ($request->precio_compra ?? 0) * $request->stock_actual,
                    'usuario_id' => 1,
                    'fecha' => now(),
                    'created_at' => now()
                ]);
            }

            $insumo = DB::table('insumos')
                ->join('categorias_insumos', 'insumos.categoria_id', '=', 'categorias_insumos.id')
                ->select('insumos.*', 'categorias_insumos.nombre as categoria_nombre')
                ->where('insumos.id', $id)
                ->first();

            return response()->json([
                'message' => 'Insumo creado exitosamente',
                'insumo' => $insumo
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear insumo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un insumo específico
     */
    public function show($id)
    {
        $insumo = DB::table('insumos')
            ->join('categorias_insumos', 'insumos.categoria_id', '=', 'categorias_insumos.id')
            ->select('insumos.*', 'categorias_insumos.nombre as categoria_nombre')
            ->where('insumos.id', $id)
            ->first();

        if (!$insumo) {
            return response()->json(['message' => 'Insumo no encontrado'], 404);
        }

        return response()->json($insumo);
    }

    /**
     * Actualizar insumo
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:200',
            'categoria_id' => 'required|integer',
            'unidad_medida' => 'required|string|max:20',
            'stock_minimo' => 'required|numeric|min:0',
        ]);

        try {
            $updated = DB::table('insumos')
                ->where('id', $id)
                ->update([
                    'nombre' => $request->nombre,
                    'descripcion' => $request->descripcion,
                    'categoria_id' => $request->categoria_id,
                    'unidad_medida' => $request->unidad_medida,
                    'stock_minimo' => $request->stock_minimo,
                    'stock_maximo' => $request->stock_maximo,
                    'precio_compra' => $request->precio_compra,
                    'proveedor' => $request->proveedor,
                    'ubicacion' => $request->ubicacion,
                    'updated_at' => now()
                ]);

            if (!$updated) {
                return response()->json(['message' => 'Insumo no encontrado'], 404);
            }

            $insumo = DB::table('insumos')
                ->join('categorias_insumos', 'insumos.categoria_id', '=', 'categorias_insumos.id')
                ->select('insumos.*', 'categorias_insumos.nombre as categoria_nombre')
                ->where('insumos.id', $id)
                ->first();

            return response()->json([
                'message' => 'Insumo actualizado exitosamente',
                'insumo' => $insumo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar insumo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar insumo (soft delete)
     */
    public function destroy($id)
    {
        try {
            $deleted = DB::table('insumos')
                ->where('id', $id)
                ->update([
                    'estado' => false,
                    'updated_at' => now()
                ]);

            if (!$deleted) {
                return response()->json(['message' => 'Insumo no encontrado'], 404);
            }

            return response()->json(['message' => 'Insumo eliminado exitosamente']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar insumo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar movimiento de inventario (entrada o salida)
     */
    public function registrarMovimiento(Request $request)
    {
        $request->validate([
            'insumo_id' => 'required|integer',
            'tipo_movimiento' => 'required|in:entrada,salida',
            'cantidad' => 'required|numeric|min:0.01',
            'motivo' => 'required|string',
            'descripcion' => 'nullable|string',
            'precio_unitario' => 'nullable|numeric|min:0',
            'referencia' => 'nullable|string|max:100'
        ]);

        try {
            DB::beginTransaction();

            // Obtener insumo actual
            $insumo = DB::table('insumos')->where('id', $request->insumo_id)->first();

            if (!$insumo) {
                return response()->json(['message' => 'Insumo no encontrado'], 404);
            }

            $stockAnterior = $insumo->stock_actual;
            $cantidad = $request->cantidad;

            // Calcular nuevo stock
            if ($request->tipo_movimiento === 'entrada') {
                $stockNuevo = $stockAnterior + $cantidad;
            } else {
                if ($stockAnterior < $cantidad) {
                    return response()->json([
                        'message' => 'Stock insuficiente',
                        'stock_disponible' => $stockAnterior
                    ], 400);
                }
                $stockNuevo = $stockAnterior - $cantidad;
            }

            // Actualizar stock del insumo
            DB::table('insumos')
                ->where('id', $request->insumo_id)
                ->update([
                    'stock_actual' => $stockNuevo,
                    'updated_at' => now()
                ]);

            // Registrar movimiento
            $movimientoId = DB::table('movimientos_inventario')->insertGetId([
                'insumo_id' => $request->insumo_id,
                'tipo_movimiento' => $request->tipo_movimiento,
                'cantidad' => $cantidad,
                'motivo' => $request->motivo,
                'descripcion' => $request->descripcion,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockNuevo,
                'precio_unitario' => $request->precio_unitario,
                'total' => ($request->precio_unitario ?? 0) * $cantidad,
                'usuario_id' => 1, // Temporal
                'referencia' => $request->referencia,
                'fecha' => now(),
                'created_at' => now()
            ]);

            DB::commit();

            $movimiento = DB::table('movimientos_inventario')
                ->join('insumos', 'movimientos_inventario.insumo_id', '=', 'insumos.id')
                ->select(
                    'movimientos_inventario.*',
                    'insumos.nombre as insumo_nombre',
                    'insumos.codigo as insumo_codigo'
                )
                ->where('movimientos_inventario.id', $movimientoId)
                ->first();

            return response()->json([
                'message' => 'Movimiento registrado exitosamente',
                'movimiento' => $movimiento
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al registrar movimiento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar movimientos de un insumo
     */
    public function movimientos($insumoId)
    {
        $movimientos = DB::table('movimientos_inventario')
            ->where('insumo_id', $insumoId)
            ->orderBy('fecha', 'desc')
            ->get();

        return response()->json($movimientos);
    }

    /**
     * Listar categorías de insumos
     */
    public function categorias()
    {
        $categorias = DB::table('categorias_insumos')
            ->where('estado', true)
            ->orderBy('nombre')
            ->get();

        return response()->json($categorias);
    }

    /**
     * Estadísticas de inventario
     */
    public function stats()
    {
        $stats = [
            'total_insumos' => DB::table('insumos')
                ->where('estado', true)
                ->count(),

            'insumos_stock_bajo' => DB::table('insumos')
                ->where('estado', true)
                ->whereRaw('stock_actual < stock_minimo')
                ->count(),

            'insumos_agotados' => DB::table('insumos')
                ->where('estado', true)
                ->where('stock_actual', 0)
                ->count(),

            'valor_inventario' => DB::table('insumos')
                ->where('estado', true)
                ->selectRaw('SUM(stock_actual * precio_compra) as valor')
                ->first()
                ->valor ?? 0,

            'movimientos_mes' => DB::table('movimientos_inventario')
                ->whereYear('fecha', date('Y'))
                ->whereMonth('fecha', date('m'))
                ->count(),

            'insumos_por_categoria' => DB::table('insumos')
                ->join('categorias_insumos', 'insumos.categoria_id', '=', 'categorias_insumos.id')
                ->where('insumos.estado', true)
                ->select(
                    'categorias_insumos.nombre as categoria',
                    DB::raw('COUNT(*) as cantidad')
                )
                ->groupBy('categorias_insumos.id', 'categorias_insumos.nombre')
                ->orderBy('cantidad', 'desc')
                ->get()
        ];

        return response()->json($stats);
    }

    /**
     * Insumos con stock bajo
     */
    public function stockBajo()
    {
        $insumos = DB::table('insumos')
            ->join('categorias_insumos', 'insumos.categoria_id', '=', 'categorias_insumos.id')
            ->select(
                'insumos.*',
                'categorias_insumos.nombre as categoria_nombre'
            )
            ->where('insumos.estado', true)
            ->whereRaw('insumos.stock_actual < insumos.stock_minimo')
            ->orderBy('insumos.stock_actual', 'asc')
            ->get();

        return response()->json($insumos);
    }
}
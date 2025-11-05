<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\NuevoPedidoCocina;

class VentaController extends Controller
{
    /**
     * Listar todas las ventas
     */
    public function index()
    {
        $ventas = DB::table('ventas')
            ->select(
                'ventas.*',
                DB::raw('(SELECT COUNT(*) FROM detalle_ventas WHERE venta_id = ventas.id) as total_items')
            )
            ->orderBy('fecha', 'desc')
            ->get();

        return response()->json($ventas);
    }

    /**
     * Crear nueva venta
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|integer',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Calcular totales
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['cantidad'] * $item['precio_unitario'];
            }
            
            $descuento = $request->descuento ?? 0;
            $impuesto = ($subtotal - $descuento) * 0.18; // IGV 18%
            $total = $subtotal - $descuento + $impuesto;

            // Generar número de venta
            $ultimaVenta = DB::table('ventas')
                ->orderBy('id', 'desc')
                ->first();
            
            $numeroVenta = 'V-' . str_pad(($ultimaVenta->id ?? 0) + 1, 8, '0', STR_PAD_LEFT);

            // Crear venta
            // En el método store(), dentro del array de inserción, agrega:
            $ventaId = DB::table('ventas')->insertGetId([
                'numero_venta' => $numeroVenta,
                'fecha' => now(),
                'usuario_id' => 1, // Por ahora usuario fijo
                'mesa_id' => $request->mesa_id,
                'cliente_nombre' => $request->cliente_nombre ?? 'Cliente General',
                'cliente_documento' => $request->cliente_documento,
                'cliente_id' => $request->cliente_id ?? null, // ✅ AGREGAR ESTA LÍNEA
                'subtotal' => $subtotal,
                'descuento' => $descuento,
                'impuesto' => $impuesto,
                'total' => $total,
                'metodo_pago' => $request->metodo_pago ?? 'efectivo',
                'estado' => 'completada',
                'estado_cocina' => 'pendiente',
                'observaciones' => $request->observaciones,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Crear detalles y actualizar stock
            $detallesArray = [];
            
            foreach ($request->items as $item) {
                // Insertar detalle
                DB::table('detalle_ventas')->insert([
                    'venta_id' => $ventaId,
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $item['cantidad'] * $item['precio_unitario'],
                    'created_at' => now()
                ]);

                // Actualizar stock del producto
                DB::table('productos')
                    ->where('id', $item['producto_id'])
                    ->decrement('stock_actual', $item['cantidad']);

                // Guardar para el evento
                $producto = DB::table('productos')->where('id', $item['producto_id'])->first();
                $detallesArray[] = [
                    'producto_nombre' => $producto->nombre,
                    'cantidad' => $item['cantidad']
                ];
            }

            DB::commit();

            // 🔔 DISPARAR EVENTO DE NOTIFICACIÓN A COCINA
            Log::info('🔔 A punto de disparar evento NuevoPedidoCocina', [
                'venta_id' => $ventaId,
                'numero_venta' => $numeroVenta,
                'cliente' => $request->cliente_nombre ?? 'Cliente General',
                'detalles_count' => count($detallesArray)
            ]);

            event(new NuevoPedidoCocina([
                'id' => $ventaId,
                'numero_venta' => $numeroVenta,
                'cliente_nombre' => $request->cliente_nombre ?? 'Cliente General',
                'mesa_id' => $request->mesa_id ?? null,
                'detalles' => $detallesArray
            ]));

            Log::info('✅ Evento NuevoPedidoCocina disparado');

            // Obtener venta completa con detalles
            $venta = $this->getVentaCompleta($ventaId);

            return response()->json([
                'message' => 'Venta registrada exitosamente',
                'venta' => $venta
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('❌ Error al crear venta', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Error al registrar venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar una venta específica con sus detalles
     */
    public function show($id)
    {
        $venta = $this->getVentaCompleta($id);

        if (!$venta) {
            return response()->json([
                'message' => 'Venta no encontrada'
            ], 404);
        }

        return response()->json($venta);
    }

    /**
     * Obtener venta con detalles
     */
    private function getVentaCompleta($id)
    {
        $venta = DB::table('ventas')
            ->where('id', $id)
            ->first();

        if (!$venta) {
            return null;
        }

        $detalles = DB::table('detalle_ventas')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->select(
                'detalle_ventas.*',
                'productos.nombre as producto_nombre',
                'productos.codigo as producto_codigo'
            )
            ->where('detalle_ventas.venta_id', $id)
            ->get();

        $venta->detalles = $detalles;

        return $venta;
    }

    /**
     * Cancelar venta (devuelve stock)
     */
    public function cancel($id)
    {
        try {
            DB::beginTransaction();

            $venta = DB::table('ventas')->where('id', $id)->first();

            if (!$venta) {
                return response()->json([
                    'message' => 'Venta no encontrada'
                ], 404);
            }

            if ($venta->estado === 'cancelada') {
                return response()->json([
                    'message' => 'La venta ya está cancelada'
                ], 400);
            }

            // Devolver stock de productos
            $detalles = DB::table('detalle_ventas')
                ->where('venta_id', $id)
                ->get();

            foreach ($detalles as $detalle) {
                DB::table('productos')
                    ->where('id', $detalle->producto_id)
                    ->increment('stock_actual', $detalle->cantidad);
            }

            // Actualizar estado de venta
            DB::table('ventas')
                ->where('id', $id)
                ->update([
                    'estado' => 'cancelada',
                    'updated_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'message' => 'Venta cancelada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al cancelar venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas de ventas
     */
    public function stats()
    {
        $hoy = date('Y-m-d');
        
        $stats = [
            'ventas_hoy' => DB::table('ventas')
                ->whereDate('fecha', $hoy)
                ->where('estado', 'completada')
                ->count(),
            
            'total_hoy' => DB::table('ventas')
                ->whereDate('fecha', $hoy)
                ->where('estado', 'completada')
                ->sum('total') ?? 0,
            
            'ventas_mes' => DB::table('ventas')
                ->whereYear('fecha', date('Y'))
                ->whereMonth('fecha', date('m'))
                ->where('estado', 'completada')
                ->count(),
            
            'total_mes' => DB::table('ventas')
                ->whereYear('fecha', date('Y'))
                ->whereMonth('fecha', date('m'))
                ->where('estado', 'completada')
                ->sum('total') ?? 0,

            'productos_mas_vendidos' => DB::table('detalle_ventas')
                ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
                ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
                ->where('ventas.estado', 'completada')
                ->select(
                    'productos.nombre',
                    DB::raw('SUM(detalle_ventas.cantidad) as total_vendido'),
                    DB::raw('SUM(detalle_ventas.subtotal) as total_ingresos')
                )
                ->groupBy('productos.id', 'productos.nombre')
                ->orderBy('total_vendido', 'desc')
                ->limit(5)
                ->get()
        ];

        return response()->json($stats);
    }
}
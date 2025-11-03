<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\NuevoPedidoCocina;

class VentaController extends Controller
{
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

    public function store(Request $request)
    {
        $request->validate([
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'metodo_pago' => 'required|string',
            'cliente_id' => 'nullable|exists:clientes,id',
            'cliente_nombre' => 'nullable|string',
            'mesa_id' => 'nullable|exists:mesas,id',
        ]);

        try {
            DB::beginTransaction();

            $ultimaVenta = DB::table('ventas')->orderBy('id', 'desc')->first();
            $numeroVenta = 'V-' . str_pad(($ultimaVenta->id ?? 0) + 1, 6, '0', STR_PAD_LEFT);

            $total = 0;
            foreach ($request->productos as $item) {
                $producto = DB::table('productos')->where('id', $item['producto_id'])->first();
                $total += $producto->precio_venta * $item['cantidad'];
            }

            $igv = $total * 0.18;
            $subtotal = $total / 1.18;

            $userId = $request->header('X-User-Id', 1);

            $ventaId = DB::table('ventas')->insertGetId([
                'numero_venta' => $numeroVenta,
                'cliente_id' => $request->cliente_id,
                'cliente_nombre' => $request->cliente_nombre ?? 'Cliente General',
                'usuario_id' => $userId,
                'mesa_id' => $request->mesa_id,
                'fecha' => now(),
                'subtotal' => round($subtotal, 2),
                'impuesto' => round($igv, 2),
                'total' => round($total, 2),
                'metodo_pago' => $request->metodo_pago,
                'estado' => 'completada',
                'estado_cocina' => 'pendiente',
                'notas_cocina' => $request->notas_cocina ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            foreach ($request->productos as $item) {
                $producto = DB::table('productos')->where('id', $item['producto_id'])->first();
                
                DB::table('detalle_ventas')->insert([
                    'venta_id' => $ventaId,
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $producto->precio_venta,
                    'subtotal' => $producto->precio_venta * $item['cantidad'],
                    'created_at' => now(),
                    //'updated_at' => now()
                ]);

                DB::table('productos')
                    ->where('id', $item['producto_id'])
                    ->decrement('stock_actual', $item['cantidad']);
            }

            DB::commit();

            $venta = DB::table('ventas')->where('id', $ventaId)->first();
            
            try {
                event(new NuevoPedidoCocina($venta));
            } catch (\Exception $e) {
                Log::warning('Error al enviar notificación: ' . $e->getMessage());
            }

            return response()->json([
                'message' => 'Venta registrada exitosamente',
                'venta' => $venta
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Error al registrar venta',
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function show($id)
    {
        $venta = $this->getVentaCompleta($id);

        if (!$venta) {
            return response()->json(['message' => 'Venta no encontrada'], 404);
        }

        return response()->json($venta);
    }

    private function getVentaCompleta($id)
    {
        $venta = DB::table('ventas')->where('id', $id)->first();

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

    public function cancel($id)
    {
        try {
            DB::beginTransaction();

            $venta = DB::table('ventas')->where('id', $id)->first();

            if (!$venta) {
                return response()->json(['message' => 'Venta no encontrada'], 404);
            }

            if ($venta->estado === 'cancelada') {
                return response()->json(['message' => 'La venta ya está cancelada'], 400);
            }

            $detalles = DB::table('detalle_ventas')->where('venta_id', $id)->get();

            foreach ($detalles as $detalle) {
                DB::table('productos')
                    ->where('id', $detalle->producto_id)
                    ->increment('stock_actual', $detalle->cantidad);
            }

            DB::table('ventas')
                ->where('id', $id)
                ->update(['estado' => 'cancelada', 'updated_at' => now()]);

            DB::commit();

            return response()->json(['message' => 'Venta cancelada exitosamente']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al cancelar venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

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
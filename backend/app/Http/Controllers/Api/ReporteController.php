<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    /**
     * Reporte de ventas por rango de fechas
     */
    public function ventasPorFecha(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date'
        ]);

        $fechaInicio = $request->fecha_inicio . ' 00:00:00';
        $fechaFin = $request->fecha_fin . ' 23:59:59';

        // Resumen general
        $resumen = DB::table('ventas')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where('estado', 'completada')
            ->selectRaw('
                COUNT(*) as total_ventas,
                SUM(subtotal) as total_subtotal,
                SUM(impuesto) as total_impuesto,
                SUM(total) as total_general,
                AVG(total) as promedio_venta
            ')
            ->first();

        // Ventas por día
        $ventasPorDia = DB::table('ventas')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where('estado', 'completada')
            ->selectRaw('
                DATE(fecha) as fecha,
                COUNT(*) as cantidad,
                SUM(total) as total
            ')
            ->groupBy(DB::raw('DATE(fecha)'))
            ->orderBy('fecha', 'asc')
            ->get();

        // Ventas por método de pago
        $ventasPorMetodo = DB::table('ventas')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where('estado', 'completada')
            ->select('metodo_pago', DB::raw('COUNT(*) as cantidad'), DB::raw('SUM(total) as total'))
            ->groupBy('metodo_pago')
            ->get();

        // Lista detallada de ventas
        $ventas = DB::table('ventas')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->select(
                'id',
                'numero_venta',
                'fecha',
                'cliente_nombre',
                'metodo_pago',
                'total',
                'estado'
            )
            ->orderBy('fecha', 'desc')
            ->get();

        return response()->json([
            'resumen' => $resumen,
            'ventas_por_dia' => $ventasPorDia,
            'ventas_por_metodo' => $ventasPorMetodo,
            'ventas' => $ventas
        ]);
    }

    /**
     * Productos más vendidos
     */
    public function productosMasVendidos(Request $request)
    {
        $limite = $request->limite ?? 10;
        $fechaInicio = $request->fecha_inicio ?? date('Y-m-01') . ' 00:00:00';
        $fechaFin = $request->fecha_fin ?? date('Y-m-d') . ' 23:59:59';

        $productos = DB::table('detalle_ventas')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->whereBetween('ventas.fecha', [$fechaInicio, $fechaFin])
            ->where('ventas.estado', 'completada')
            ->select(
                'productos.id',
                'productos.codigo',
                'productos.nombre',
                'categorias.nombre as categoria',
                DB::raw('SUM(detalle_ventas.cantidad) as cantidad_vendida'),
                DB::raw('SUM(detalle_ventas.subtotal) as ingresos_totales'),
                DB::raw('AVG(detalle_ventas.precio_unitario) as precio_promedio')
            )
            ->groupBy('productos.id', 'productos.codigo', 'productos.nombre', 'categorias.nombre')
            ->orderBy('cantidad_vendida', 'desc')
            ->limit($limite)
            ->get();

        return response()->json($productos);
    }

    /**
     * Reporte de inventario actual
     */
    public function inventario()
    {
        // Productos con stock
        $productos = DB::table('productos')
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->where('productos.estado', true)
            ->select(
                'productos.id',
                'productos.codigo',
                'productos.nombre',
                'categorias.nombre as categoria',
                'productos.stock_actual',
                'productos.stock_minimo',
                'productos.precio_venta',
                'productos.precio_compra',
                DB::raw('(productos.stock_actual * productos.precio_venta) as valor_inventario'),
                DB::raw('(productos.stock_actual * productos.precio_compra) as costo_inventario')
            )
            ->orderBy('productos.nombre')
            ->get();

        // Resumen de inventario
        $resumen = [
            'total_productos' => $productos->count(),
            'valor_total' => $productos->sum('valor_inventario'),
            'costo_total' => $productos->sum('costo_inventario'),
            'productos_stock_bajo' => $productos->filter(function($p) {
                return $p->stock_actual < $p->stock_minimo;
            })->count(),
            'productos_sin_stock' => $productos->filter(function($p) {
                return $p->stock_actual == 0;
            })->count()
        ];

        // Inventario por categoría
        $porCategoria = DB::table('productos')
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->where('productos.estado', true)
            ->select(
                'categorias.nombre as categoria',
                DB::raw('COUNT(*) as cantidad_productos'),
                DB::raw('SUM(productos.stock_actual) as total_stock'),
                DB::raw('SUM(productos.stock_actual * productos.precio_venta) as valor_total')
            )
            ->groupBy('categorias.id', 'categorias.nombre')
            ->orderBy('valor_total', 'desc')
            ->get();

        return response()->json([
            'resumen' => $resumen,
            'productos' => $productos,
            'por_categoria' => $porCategoria
        ]);
    }

    /**
     * Dashboard de reportes (resumen general)
     */
    public function dashboard()
    {
        $hoy = date('Y-m-d');
        $inicioMes = date('Y-m-01');
        $finMes = date('Y-m-t');

        // Ventas del día
        $ventasHoy = DB::table('ventas')
            ->whereDate('fecha', $hoy)
            ->where('estado', 'completada')
            ->selectRaw('COUNT(*) as cantidad, SUM(total) as total')
            ->first();

        // Ventas del mes
        $ventasMes = DB::table('ventas')
            ->whereBetween('fecha', [$inicioMes . ' 00:00:00', $finMes . ' 23:59:59'])
            ->where('estado', 'completada')
            ->selectRaw('COUNT(*) as cantidad, SUM(total) as total')
            ->first();

        // Productos más vendidos del mes
        $topProductos = DB::table('detalle_ventas')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->whereBetween('ventas.fecha', [$inicioMes . ' 00:00:00', $finMes . ' 23:59:59'])
            ->where('ventas.estado', 'completada')
            ->select(
                'productos.nombre',
                DB::raw('SUM(detalle_ventas.cantidad) as cantidad')
            )
            ->groupBy('productos.id', 'productos.nombre')
            ->orderBy('cantidad', 'desc')
            ->limit(5)
            ->get();

        // Productos con stock bajo
        $stockBajo = DB::table('productos')
            ->where('estado', true)
            ->whereRaw('stock_actual < stock_minimo')
            ->count();

        // Valor del inventario
        $valorInventario = DB::table('productos')
            ->where('estado', true)
            ->selectRaw('SUM(stock_actual * precio_venta) as valor')
            ->first()
            ->valor ?? 0;

        return response()->json([
            'ventas_hoy' => [
                'cantidad' => $ventasHoy->cantidad ?? 0,
                'total' => $ventasHoy->total ?? 0
            ],
            'ventas_mes' => [
                'cantidad' => $ventasMes->cantidad ?? 0,
                'total' => $ventasMes->total ?? 0
            ],
            'top_productos' => $topProductos,
            'stock_bajo' => $stockBajo,
            'valor_inventario' => $valorInventario
        ]);
    }

    /**
     * Ventas por categoría
     */
    public function ventasPorCategoria(Request $request)
    {
        $fechaInicio = $request->fecha_inicio ?? date('Y-m-01') . ' 00:00:00';
        $fechaFin = $request->fecha_fin ?? date('Y-m-d') . ' 23:59:59';

        $categorias = DB::table('detalle_ventas')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->whereBetween('ventas.fecha', [$fechaInicio, $fechaFin])
            ->where('ventas.estado', 'completada')
            ->select(
                'categorias.nombre as categoria',
                DB::raw('COUNT(DISTINCT ventas.id) as num_ventas'),
                DB::raw('SUM(detalle_ventas.cantidad) as unidades_vendidas'),
                DB::raw('SUM(detalle_ventas.subtotal) as total_ventas')
            )
            ->groupBy('categorias.id', 'categorias.nombre')
            ->orderBy('total_ventas', 'desc')
            ->get();

        return response()->json($categorias);
    }
}
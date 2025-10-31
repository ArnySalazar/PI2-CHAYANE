<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // KPI 1: Total de productos activos
            $totalProductos = DB::table('productos')
                ->where('estado', true)
                ->count();

            // KPI 2: Valor total del inventario
            $valorInventario = DB::table('productos')
                ->where('estado', true)
                ->selectRaw('SUM(precio_venta * stock_actual) as total')
                ->first()
                ->total ?? 0;

            // KPI 3: Productos con stock bajo
            $productosStockBajo = DB::table('productos')
                ->where('estado', true)
                ->whereRaw('stock_actual < stock_minimo')
                ->count();

            // KPI 4: Total de categorías
            $totalCategorias = DB::table('categorias')->count();

            // Ventas de hoy
            $ventasHoy = DB::table('ventas')
                ->whereDate('fecha', today())
                ->where('estado', '!=', 'cancelada')
                ->count();

            $totalVentasHoy = DB::table('ventas')
                ->whereDate('fecha', today())
                ->where('estado', '!=', 'cancelada')
                ->sum('total');

            // Productos más vendidos
            $productosMasVendidos = DB::table('detalle_ventas')
                ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
                ->select('productos.nombre', DB::raw('SUM(detalle_ventas.cantidad) as total_vendido'))
                ->groupBy('productos.nombre')
                ->orderBy('total_vendido', 'desc')
                ->limit(5)
                ->get();

            // Gráfico: Productos por categoría
            $productosPorCategoria = DB::table('productos')
                ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
                ->where('productos.estado', true)
                ->select('categorias.nombre as categoria', DB::raw('COUNT(*) as cantidad'))
                ->groupBy('categorias.nombre')
                ->orderBy('cantidad', 'desc')
                ->get();

            // Top 5 productos más caros
            $topProductos = DB::table('productos')
                ->where('estado', true)
                ->select('nombre', 'precio_venta as precio')
                ->orderBy('precio_venta', 'desc')
                ->limit(5)
                ->get();

            // Alertas de stock bajo
            $alertasStockBajo = DB::table('productos')
                ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
                ->where('productos.estado', true)
                ->whereRaw('productos.stock_actual < productos.stock_minimo')
                ->select(
                    'productos.id',
                    'productos.nombre',
                    'productos.stock_actual as stock',
                    'productos.stock_minimo',
                    'categorias.nombre as categoria'
                )
                ->orderBy('productos.stock_actual', 'asc')
                ->get();

            // Estado de cocina
            $cocina = [
                'pedidos_pendientes' => DB::table('ventas')
                    ->where('estado_cocina', 'pendiente')
                    ->where('estado', '!=', 'cancelada')
                    ->count(),
                
                'pedidos_en_preparacion' => DB::table('ventas')
                    ->where('estado_cocina', 'en_preparacion')
                    ->count(),
                
                'pedidos_atrasados' => DB::table('ventas')
                    ->where('estado_cocina', 'en_preparacion')
                    ->where('fecha', '<', DB::raw("NOW() - INTERVAL '30 minutes'"))
                    ->count(),
                
                'tiempo_promedio_hoy' => DB::table('ventas')
                    ->where('estado_cocina', 'listo')
                    ->whereDate('fecha', today())
                    ->selectRaw('AVG(EXTRACT(EPOCH FROM (updated_at - fecha))/60) as promedio')
                    ->value('promedio') ?? 0,
            ];

            return response()->json([
                'total_productos' => $totalProductos,
                'valor_inventario' => round($valorInventario, 2),
                'productos_stock_bajo' => $productosStockBajo,
                'total_categorias' => $totalCategorias,
                'ventas_hoy' => $ventasHoy,
                'total_ventas_hoy' => round($totalVentasHoy, 2),
                'productos_mas_vendidos' => $productosMasVendidos,
                'productos_por_categoria' => $productosPorCategoria,
                'top_productos' => $topProductos,
                'alertas_stock_bajo' => $alertasStockBajo,
                'cocina' => $cocina
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
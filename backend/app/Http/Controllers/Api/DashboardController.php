<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // ==========================================
            // KPIs
            // ==========================================
            $kpis = [
                'total_productos' => DB::table('productos')
                    ->where('estado', true)
                    ->count(),

                'valor_inventario' => DB::table('productos')
                    ->where('estado', true)
                    ->selectRaw('SUM(precio_venta * stock_actual) as total')
                    ->value('total') ?? 0,

                'productos_stock_bajo' => DB::table('productos')
                    ->where('estado', true)
                    ->whereRaw('stock_actual < stock_minimo')
                    ->count(),

                'total_categorias' => DB::table('categorias')->count(),
            ];

            // ==========================================
            // Cocina
            // ==========================================
            $cocina = [
                'pedidos_pendientes' => DB::table('ventas')
                    ->where('estado_cocina', 'pendiente')
                    ->where('estado', '!=', 'cancelada')
                    ->count(),

                'pedidos_en_preparacion' => DB::table('ventas')
                    ->where('estado_cocina', 'en_preparacion')
                    ->where('estado', '!=', 'cancelada')
                    ->count(),

                'pedidos_atrasados' => DB::table('ventas')
                    ->where('estado_cocina', 'en_preparacion')
                    ->where('fecha', '<', DB::raw("NOW() - INTERVAL '30 minutes'"))
                    ->where('estado', '!=', 'cancelada')
                    ->count(),

                'tiempo_promedio_hoy' => DB::table('ventas')
                    ->where('estado_cocina', 'listo')
                    ->whereDate('fecha', today())
                    ->selectRaw('AVG(EXTRACT(EPOCH FROM (updated_at - fecha))/60) as promedio')
                    ->value('promedio') ?? 0,
            ];

            // ==========================================
            // Gráficos
            // ==========================================
            $graficos = [
                'productos_por_categoria' => DB::table('productos')
                    ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
                    ->where('productos.estado', true)
                    ->select('categorias.nombre as categoria', DB::raw('COUNT(*) as cantidad'))
                    ->groupBy('categorias.id', 'categorias.nombre')
                    ->orderBy('cantidad', 'desc')
                    ->get(),

                'top_productos' => DB::table('productos')
                    ->where('estado', true)
                    ->select('nombre', 'precio_venta as precio')
                    ->orderBy('precio_venta', 'desc')
                    ->limit(5)
                    ->get(),
            ];

            // ==========================================
            // Alertas
            // ==========================================
            $alertas = [
                'stock_bajo' => DB::table('productos')
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
                    ->limit(10)
                    ->get(),
            ];

            // ==========================================
            // Respuesta estructurada
            // ==========================================
            return response()->json([
                'kpis' => $kpis,
                'cocina' => $cocina,
                'graficos' => $graficos,
                'alertas' => $alertas,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en Dashboard: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al obtener estadísticas del dashboard',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }
}
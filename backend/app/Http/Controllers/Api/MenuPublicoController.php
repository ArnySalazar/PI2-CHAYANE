<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class MenuPublicoController extends Controller
{
    /**
     * Obtener menú público (productos disponibles)
     * Este endpoint NO requiere autenticación
     */
    public function index()
    {
        try {
            // Obtener productos activos con stock disponible
            $productos = DB::table('productos')
                ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
                ->where('productos.estado', true)
                ->where('productos.stock_actual', '>', 0)
                ->select(
                    'productos.id',
                    'productos.nombre',
                    'productos.descripcion',
                    'productos.precio_venta',
                    'productos.stock_actual',
                    'categorias.nombre as categoria',
                    'categorias.id as categoria_id'
                )
                ->orderBy('categorias.nombre')
                ->orderBy('productos.nombre')
                ->get();

            // Agrupar productos por categoría
            $menuPorCategoria = [];
            foreach ($productos as $producto) {
                $categoria = $producto->categoria;
                if (!isset($menuPorCategoria[$categoria])) {
                    $menuPorCategoria[$categoria] = [];
                }
                $menuPorCategoria[$categoria][] = [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'descripcion' => $producto->descripcion,
                    'precio' => number_format($producto->precio_venta, 2),
                    'disponible' => $producto->stock_actual > 0
                ];
            }

            // Información del restaurante
            $infoRestaurante = [
                'nombre' => 'Restaurante Chayane',
                'eslogan' => 'Sabor auténtico peruano',
                'horario' => 'Lunes a Domingo: 12:00 PM - 10:00 PM',
                'telefono' => '+51 999 888 777',
                'direccion' => 'Av. Principal 123, Lima, Perú'
            ];

            return response()->json([
                'restaurante' => $infoRestaurante,
                'menu' => $menuPorCategoria,
                'total_productos' => count($productos),
                'ultima_actualizacion' => now()->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener el menú',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener solo categorías con productos disponibles
     */
    public function categorias()
    {
        try {
            $categorias = DB::table('categorias')
                ->join('productos', 'categorias.id', '=', 'productos.categoria_id')
                ->where('productos.estado', true)
                ->where('productos.stock_actual', '>', 0)
                ->select(
                    'categorias.id',
                    'categorias.nombre',
                    DB::raw('COUNT(productos.id) as total_productos')
                )
                ->groupBy('categorias.id', 'categorias.nombre')
                ->orderBy('categorias.nombre')
                ->get();

            return response()->json($categorias);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener categorías',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
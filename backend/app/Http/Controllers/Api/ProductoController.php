<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    /**
     * Listar todos los productos
     */
    public function index()
    {
        $productos = DB::table('productos')
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->select(
                'productos.id',
                'productos.codigo',
                'productos.nombre',
                'productos.descripcion',
                'productos.categoria_id',
                'categorias.descripcion as categoria_descripcion',
                'productos.precio_venta as precio',
                'productos.precio_compra as costo',
                'productos.stock_actual as stock',
                'productos.stock_minimo',
                'productos.unidad_medida',
                'productos.tipo',
                'productos.imagen',
                'productos.estado',
                'productos.created_at',
                'productos.updated_at'
            )
            ->where('productos.estado', true)
            ->orderBy('productos.nombre')
            ->get();

        return response()->json($productos);
    }

    /**
     * Crear nuevo producto
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:200',
            'categoria_id' => 'required|integer',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        try {
            // Generar código automático si no viene
            $codigo = $request->codigo;
            if (!$codigo) {
                $ultimoId = DB::table('productos')->max('id') ?? 0;
                $codigo = 'PROD-' . str_pad($ultimoId + 1, 6, '0', STR_PAD_LEFT);
            }

            $id = DB::table('productos')->insertGetId([
                'codigo' => $codigo,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'categoria_id' => $request->categoria_id,
                'precio_venta' => $request->precio,
                'precio_compra' => $request->costo ?? 0,
                'stock_actual' => $request->stock,
                'stock_minimo' => $request->stock_minimo ?? 10,
                'unidad_medida' => $request->unidad_medida ?? 'unidad',
                'tipo' => $request->tipo ?? 'producto',
                'imagen' => $request->imagen ?? null,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $producto = DB::table('productos')
                ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
                ->select(
                    'productos.*',
                    'categorias.descripcion as categoria_descripcion',
                    'productos.precio_venta as precio',
                    'productos.stock_actual as stock'
                )
                ->where('productos.id', $id)
                ->first();

            return response()->json([
                'message' => 'Producto creado exitosamente',
                'producto' => $producto
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un producto específico
     */
    public function show($id)
    {
        $producto = DB::table('productos')
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->select(
                'productos.*',
                'categorias.descripcion as categoria_descripcion',
                'productos.precio_venta as precio',
                'productos.stock_actual as stock'
            )
            ->where('productos.id', $id)
            ->first();

        if (!$producto) {
            return response()->json([
                'message' => 'Producto no encontrado'
            ], 404);
        }

        return response()->json($producto);
    }

    /**
     * Actualizar producto
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:200',
            'categoria_id' => 'required|integer',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        try {
            // Generar código automático si no viene
            $codigo = $request->codigo;
            if (!$codigo) {
                $ultimoId = DB::table('productos')->max('id') ?? 0;
                $codigo = 'PROD-' . str_pad($ultimoId + 1, 6, '0', STR_PAD_LEFT);
            }

            $updated = DB::table('productos')
                ->where('id', $id)
                ->update([
                    'codigo' => $codigo,
                    'nombre' => $request->nombre,
                    'descripcion' => $request->descripcion,
                    'categoria_id' => $request->categoria_id,
                    'precio_venta' => $request->precio,
                    'precio_compra' => $request->costo ?? 0,
                    'stock_actual' => $request->stock,
                    'stock_minimo' => $request->stock_minimo ?? 10,
                    'unidad_medida' => $request->unidad_medida ?? 'unidad',
                    'tipo' => $request->tipo ?? 'producto',
                    'imagen' => $request->imagen ?? null,
                    'estado' => $request->estado ?? true,
                    'updated_at' => now()
                ]);

            if (!$updated) {
                return response()->json([
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            $producto = DB::table('productos')
                ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
                ->select(
                    'productos.*',
                    'categorias.descripcion as categoria_descripcion',
                    'productos.precio_venta as precio',
                    'productos.stock_actual as stock'
                )
                ->where('productos.id', $id)
                ->first();

            return response()->json([
                'message' => 'Producto actualizado exitosamente',
                'producto' => $producto
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar producto (soft delete - cambia estado a false)
     */
    public function destroy($id)
    {
        try {
            $deleted = DB::table('productos')
                ->where('id', $id)
                ->update([
                    'estado' => false,
                    'updated_at' => now()
                ]);

            if (!$deleted) {
                return response()->json([
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            return response()->json([
                'message' => 'Producto eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar categorías (para el formulario)
     */
    public function categorias()
    {
        $categorias = DB::table('categorias')
            ->orderBy('nombre')
            ->get();

        return response()->json($categorias);
    }
}
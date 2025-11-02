<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProveedorController extends Controller
{
    public function index()
    {
        $proveedores = DB::table('proveedores')
            ->orderBy('nombre_comercial')
            ->get();
        
        return response()->json($proveedores);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_comercial' => 'required|string|max:255',
            'ruc' => 'nullable|string|max:20|unique:proveedores,ruc',
        ]);

        try {
            $proveedorId = DB::table('proveedores')->insertGetId([
                'nombre_comercial' => $request->nombre_comercial,
                'razon_social' => $request->razon_social,
                'ruc' => $request->ruc,
                'direccion' => $request->direccion,
                'telefono' => $request->telefono,
                'email' => $request->email,
                'contacto_nombre' => $request->contacto_nombre,
                'contacto_telefono' => $request->contacto_telefono,
                'tipo_proveedor' => $request->tipo_proveedor,
                'estado' => 'activo',
                'notas' => $request->notas,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $proveedor = DB::table('proveedores')->where('id', $proveedorId)->first();

            return response()->json([
                'message' => 'Proveedor creado exitosamente',
                'proveedor' => $proveedor
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear proveedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
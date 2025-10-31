<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Limpiar permisos existentes
        DB::table('role_permissions')->truncate();

        // Admin (role_id: 1) - Acceso total a todo
        $modules = ['productos', 'insumos', 'ventas', 'mesas', 'cocina', 'clientes', 'reportes', 'usuarios', 'configuracion'];
        
        foreach ($modules as $module) {
            DB::table('role_permissions')->insert([
                'role_id' => 1,
                'module' => $module,
                'can_read' => true,
                'can_create' => true,
                'can_edit' => true,
                'can_delete' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Gerente (role_id: 2) - Acceso casi total, no puede eliminar
        foreach ($modules as $module) {
            DB::table('role_permissions')->insert([
                'role_id' => 2,
                'module' => $module,
                'can_read' => true,
                'can_create' => true,
                'can_edit' => true,
                'can_delete' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Cajera (role_id: 3) - Solo ventas y clientes
        DB::table('role_permissions')->insert([
            [
                'role_id' => 3,
                'module' => 'ventas',
                'can_read' => true,
                'can_create' => true,
                'can_edit' => false,
                'can_delete' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 3,
                'module' => 'clientes',
                'can_read' => true,
                'can_create' => true,
                'can_edit' => false,
                'can_delete' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 3,
                'module' => 'productos',
                'can_read' => true,
                'can_create' => false,
                'can_edit' => false,
                'can_delete' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Cocinero (role_id: 4) - Cocina, productos (lectura), mesas (lectura)
        DB::table('role_permissions')->insert([
            [
                'role_id' => 4,
                'module' => 'cocina',
                'can_read' => true,
                'can_create' => false,
                'can_edit' => true,
                'can_delete' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 4,
                'module' => 'productos',
                'can_read' => true,
                'can_create' => false,
                'can_edit' => false,
                'can_delete' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 4,
                'module' => 'mesas',
                'can_read' => true,
                'can_create' => false,
                'can_edit' => false,
                'can_delete' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Mesera (role_id: 5) - Ventas, mesas, productos (lectura), clientes, cocina (lectura)
        DB::table('role_permissions')->insert([
            [
                'role_id' => 5,
                'module' => 'ventas',
                'can_read' => true,
                'can_create' => true,
                'can_edit' => false,
                'can_delete' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 5,
                'module' => 'mesas',
                'can_read' => true,
                'can_create' => false,
                'can_edit' => true,
                'can_delete' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 5,
                'module' => 'productos',
                'can_read' => true,
                'can_create' => false,
                'can_edit' => false,
                'can_delete' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 5,
                'module' => 'clientes',
                'can_read' => true,
                'can_create' => true,
                'can_edit' => false,
                'can_delete' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 5,
                'module' => 'cocina',
                'can_read' => true,
                'can_create' => false,
                'can_edit' => false,
                'can_delete' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        echo "✅ Permisos creados exitosamente!\n";
    }
}
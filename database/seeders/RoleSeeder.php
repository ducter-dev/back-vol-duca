<?php

namespace Database\Seeders;

use App\Enums\PermisosGroupEnum;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $role1 = Role::create(['name' => 'administrador']);
        $role2 = Role::create(['name' => 'supervisor']);
        $role3 = Role::create(['name' => 'operador']);
        $role4 = Role::create(['name' => 'auditor']);

        
        Permission::create(['name' => 'admin.home', 'description' => 'ver dashboard'], )->syncRoles([$role1, $role2, $role3, $role4]);

        Permission::create(['name' => 'admin.empresa.index', 'description' => 'ver empresas'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.empresa.create', 'description' => 'insertar empresas'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.empresa.show', 'description' => 'mostrar empresas'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.empresa.update', 'description' => 'actualizar empresas'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.empresa.delete', 'description' => 'eliminar empresas'])->syncRoles([$role1]);

        Permission::create(['name' => 'admin.archivo.index', 'description' => 'ver archivos'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.archivo.create', 'description' => 'insertar archivos'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.archivo.show', 'description' => 'mostrar archivos'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.archivo.update', 'description' => 'actualizar archivos'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.archivo.delete', 'description' => 'eliminar archivos'])->syncRoles([$role1]);

        Permission::create(['name' => 'admin.archivomensual.index', 'description' => 'ver archivos mensual'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.archivomensual.create', 'description' => 'insertar archivos mensual'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.archivomensual.show', 'description' => 'mostrar archivos mensual'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.archivomensual.update', 'description' => 'actualizar archivos mensual'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.archivomensual.delete', 'description' => 'eliminar archivos mensual'])->syncRoles([$role1]);

        Permission::create(['name' => 'admin.bitacora.index', 'description' => 'ver bitacoras'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.bitacora.create', 'description' => 'insertar bitacoras'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.bitacora.show', 'description' => 'mostrar bitacoras'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.bitacora.update', 'description' => 'actualizar bitacoras'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.bitacora.delete', 'description' => 'eliminar bitacoras'])->syncRoles([$role1]);

        Permission::create(['name' => 'admin.cliente.index', 'description' => 'ver clientes'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.cliente.create', 'description' => 'insertar clientes'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.cliente.show', 'description' => 'mostrar clientes'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.cliente.update', 'description' => 'actualizar clientes'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.cliente.delete', 'description' => 'eliminar clientes'])->syncRoles([$role1]);

        Permission::create(['name' => 'admin.compuesto.index', 'description' => 'ver compuestos'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.compuesto.create', 'description' => 'insertar compuestos'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.compuesto.show', 'description' => 'mostrar compuestos'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.compuesto.update', 'description' => 'actualizar compuestos'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.compuesto.delete', 'description' => 'eliminar compuestos'])->syncRoles([$role1]);

        Permission::create(['name' => 'admin.compuestoproducto.index', 'description' => 'ver compuestos productos'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.compuestoproducto.create', 'description' => 'insertar compuestos productos'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.compuestoproducto.show', 'description' => 'mostrar compuestos productos'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.compuestoproducto.update', 'description' => 'actualizar compuestos productos'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.compuestoproducto.delete', 'description' => 'eliminar compuestos productos'])->syncRoles([$role1]);

        Permission::create(['name' => 'admin.densidad.index', 'description' => 'ver densidades'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.densidad.create', 'description' => 'insertar densidades'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.densidad.show', 'description' => 'mostrar densidades'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.densidad.update', 'description' => 'actualizar densidades'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.densidad.delete', 'description' => 'eliminar densidades'])->syncRoles([$role1]);

        Permission::create(['name' => 'admin.dictamen.index', 'description' => 'ver dictamenes'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.dictamen.create', 'description' => 'insertar dictamenes'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.dictamen.show', 'description' => 'mostrar dictamenes'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.dictamen.update', 'description' => 'actualizar dictamenes'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.dictamen.delete', 'description' => 'eliminar dictamenes'])->syncRoles([$role1]);

        Permission::create(['name' => 'admin.evento.index', 'description' => 'ver eventos'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.evento.create', 'description' => 'insertar eventos'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.evento.show', 'description' => 'mostrar eventos'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.evento.update', 'description' => 'actualizar eventos'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.evento.delete', 'description' => 'eliminar eventos'])->syncRoles([$role1]);

        Permission::create(['name' => 'admin.producto.index', 'description' => 'ver productos'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.producto.create', 'description' => 'insertar productos'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.producto.show', 'description' => 'mostrar productos'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.producto.update', 'description' => 'actualizar productos'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.producto.delete', 'description' => 'eliminar productos'])->syncRoles([$role1]);

        Permission::create(['name' => 'admin.productoempresa.index', 'description' => 'ver productos empresas'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.productoempresa.create', 'description' => 'insertar productos empresas'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.productoempresa.show', 'description' => 'mostrar productos empresas'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.productoempresa.update', 'description' => 'actualizar productos empresas'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.productoempresa.delete', 'description' => 'eliminar productos empresas'])->syncRoles([$role1]);

        Permission::create(['name' => 'admin.recibo.index', 'description' => 'ver recibos'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.recibo.create', 'description' => 'insertar recibos'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.recibo.show', 'description' => 'mostrar recibos'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.recibo.update', 'description' => 'actualizar recibos'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.recibo.delete', 'description' => 'eliminar recibos'])->syncRoles([$role1]);

        Permission::create(['name' => 'admin.user.index', 'description' => 'ver usuarios'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.user.create', 'description' => 'insertar usuarios'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.user.show', 'description' => 'mostrar usuarios'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'admin.user.update', 'description' => 'actualizar usuarios'])->syncRoles([$role1]);
        Permission::create(['name' => 'admin.user.delete', 'description' => 'eliminar usuarios'])->syncRoles([$role1]);


    }
}
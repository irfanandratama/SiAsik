<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["view_condition","view_any_condition","create_condition","update_condition","restore_condition","restore_any_condition","replicate_condition","reorder_condition","delete_condition","delete_any_condition","force_delete_condition","force_delete_any_condition","view_role","view_any_role","create_role","update_role","delete_role","delete_any_role","view_room","view_any_room","create_room","update_room","restore_room","restore_any_room","replicate_room","reorder_room","delete_room","delete_any_room","force_delete_room","force_delete_any_room","view_status","view_any_status","create_status","update_status","restore_status","restore_any_status","replicate_status","reorder_status","delete_status","delete_any_status","force_delete_status","force_delete_any_status"]},{"name":"cleaner","guard_name":"web","permissions":["view_reporting","update_reporting"]},{"name":"Koordinator","guard_name":"web","permissions":["view_condition","view_any_condition","create_condition","update_condition","delete_condition","view_room","view_any_room","create_room","update_room","delete_room","view_status","view_any_status","create_status","update_status","delete_status","view_reporting","update_reporting","view_any_reporting","create_reporting"]},{"name":"Kepala Sub Bagian","guard_name":"web","permissions":["view_condition","view_any_condition","create_condition","update_condition","restore_condition","restore_any_condition","replicate_condition","reorder_condition","delete_condition","delete_any_condition","force_delete_condition","force_delete_any_condition","view_room","view_any_room","create_room","update_room","restore_room","restore_any_room","replicate_room","reorder_room","delete_room","delete_any_room","force_delete_room","force_delete_any_room","view_status","view_any_status","create_status","update_status","restore_status","restore_any_status","replicate_status","reorder_status","delete_status","delete_any_status","force_delete_status","force_delete_any_status","view_reporting","update_reporting","view_any_reporting","create_reporting","restore_reporting","restore_any_reporting","replicate_reporting","reorder_reporting","view_user","view_any_user","update_user"]}]';
        $directPermissions = '[]';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (! blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            /** @var Model $roleModel */
            $roleModel = Utils::getRoleModel();
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (! blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn ($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();

                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (! blank($permissions = json_decode($directPermissions, true))) {
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($permissions as $permission) {
                if ($permissionModel::whereName($permission)->doesntExist()) {
                    $permissionModel::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}

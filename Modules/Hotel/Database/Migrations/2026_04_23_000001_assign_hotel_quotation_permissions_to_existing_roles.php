<?php

use App\Models\Module;
use App\Models\Restaurant;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $hotelModule = Module::firstOrCreate(['name' => 'Hotel']);

        $quotationPermissions = [
            ['guard_name' => 'web', 'name' => 'Create Hotel Quotation', 'module_id' => $hotelModule->id],
            ['guard_name' => 'web', 'name' => 'Show Hotel Quotations', 'module_id' => $hotelModule->id],
            ['guard_name' => 'web', 'name' => 'Update Hotel Quotation', 'module_id' => $hotelModule->id],
            ['guard_name' => 'web', 'name' => 'Delete Hotel Quotation', 'module_id' => $hotelModule->id],
        ];

        foreach ($quotationPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name'], 'guard_name' => $permission['guard_name']],
                $permission
            );
        }

        $restaurantIds = Restaurant::pluck('id');
        if ($restaurantIds->isEmpty()) {
            return;
        }

        $roleNames = $restaurantIds->flatMap(fn ($id) => ["Admin_{$id}", "Branch Head_{$id}"])->toArray();
        $permissionNames = array_column($quotationPermissions, 'name');

        Role::whereIn('name', $roleNames)->get()->each(function ($role) use ($permissionNames) {
            $role->givePermissionTo($permissionNames);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $restaurantIds = Restaurant::pluck('id');
        if ($restaurantIds->isEmpty()) {
            return;
        }

        $roleNames = $restaurantIds->flatMap(fn ($id) => ["Admin_{$id}", "Branch Head_{$id}"])->toArray();
        $permissionNames = [
            'Create Hotel Quotation',
            'Show Hotel Quotations',
            'Update Hotel Quotation',
            'Delete Hotel Quotation',
        ];

        Role::whereIn('name', $roleNames)->get()->each(function ($role) use ($permissionNames) {
            $role->revokePermissionTo($permissionNames);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};


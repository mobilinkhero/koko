<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class EcommercePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create e-commerce permissions if they don't exist
        $permissions = [
            'tenant.ecommerce.view',
            'tenant.ecommerce.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $this->command->info('E-commerce permissions created successfully.');
    }
}

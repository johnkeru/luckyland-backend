<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['roleName' => 'Admin', 'description' => "You'll have full administrative privileges."],
            ['roleName' => 'Inventory', 'description' => "You'll have the ability to modify inventory."],
            ['roleName' => 'Front Desk', 'description' => "You'll have the ability to manage front desk operations."],
            ['roleName' => 'House Keeping', 'description' => "Ensures clean, comfortable guest rooms with attention to detail."]
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}

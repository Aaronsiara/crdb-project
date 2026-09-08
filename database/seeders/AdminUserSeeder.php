<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds a default admin login so you can access the app immediately after
 * setup.
 *
 * Default credentials (CHANGE THIS PASSWORD after first login):
 *   email:    admin@crdb-segmentation.local
 *   password: crdb-admin-2026
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@crdb-segmentation.local'],
            [
                'name' => 'Admin',
                'role' => 'admin',
                'password' => Hash::make('admin1234'),
            ]
        );
    }
}

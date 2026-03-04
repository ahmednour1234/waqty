<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Admin::where('email', 'admin@example.com')->first();
        
        if ($admin) {
            $admin->update([
                'name' => 'Super Admin',
                'password' => Hash::make('Admin@12345'),
                'active' => true,
            ]);
        } else {
            Admin::create([
                'name' => 'Super Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('Admin@12345'),
                'active' => true,
            ]);
        }
    }
}

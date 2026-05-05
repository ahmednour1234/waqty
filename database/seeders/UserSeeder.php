<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'              => 'Ahmed Hassan',
                'email'             => 'ahmed@example.com',
                'phone'             => '+201000000001',
                'gender'            => 'male',
                'date_birth'        => '1990-05-15',
                'active'            => true,
                'blocked'           => false,
                'banned'            => false,
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Sara Mohamed',
                'email'             => 'sara@example.com',
                'phone'             => '+201000000002',
                'gender'            => 'female',
                'date_birth'        => '1995-08-22',
                'active'            => true,
                'blocked'           => false,
                'banned'            => false,
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Omar Khalid',
                'email'             => 'omar@example.com',
                'phone'             => '+201000000003',
                'gender'            => 'male',
                'date_birth'        => '1988-01-10',
                'active'            => true,
                'blocked'           => false,
                'banned'            => false,
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Fatima Ali',
                'email'             => 'fatima@example.com',
                'phone'             => '+201000000004',
                'gender'            => 'female',
                'date_birth'        => '1992-11-03',
                'active'            => true,
                'blocked'           => false,
                'banned'            => false,
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Youssef Ibrahim',
                'email'             => 'youssef@example.com',
                'phone'             => '+201000000005',
                'gender'            => 'male',
                'date_birth'        => '1997-07-19',
                'active'            => false,
                'blocked'           => false,
                'banned'            => false,
                'email_verified_at' => now(),
            ],
        ];

        $password = Hash::make('User@12345');
        $count    = 0;

        foreach ($users as $data) {
            $existing = User::where('email', $data['email'])->first();

            if ($existing) {
                $existing->update(array_merge($data, ['password' => $password]));
            } else {
                User::create(array_merge($data, ['password' => $password]));
                $count++;
            }
        }

        if ($this->command) {
            $this->command->info("Seeded {$count} users. (password: User@12345)");
        }
    }
}

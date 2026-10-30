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
                'username' => 'admin',
                'password' => Hash::make('password'),
                'role'     => 'admin',
                'is_active'=> true,
                'failed_login_count' => 0,
                'role_name' => 'admin',
            ],
            [
                'username' => 'kasir',
                'password' => Hash::make('password'),
                'role'     => 'kasir',
                'is_active'=> true,
                'failed_login_count' => 0,
                'role_name' => 'kasir',
            ],
            [
                'username' => 'operator',
                'password' => Hash::make('password'),
                'role'     => 'operator',
                'is_active'=> true,
                'failed_login_count' => 0,
                'role_name' => 'operator',
            ],
        ];

        foreach ($users as $data) {
            $roleName = $data['role_name'];
            unset($data['role_name']);

            $user = User::firstOrCreate(['username' => $data['username']], $data);
            $user->assignRole($roleName);
        }
    }
}

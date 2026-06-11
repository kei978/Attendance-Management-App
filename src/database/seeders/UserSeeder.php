<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // user1（一般）
        User::create([
            'name' => '一般ユーザー1',
            'email' => 'user1@example.com',
            'password' => Hash::make('password'),
            'role' => 0,
            'email_verified_at' => now(),
        ]);

        // user2（一般）
        User::create([
            'name' => '一般ユーザー2',
            'email' => 'user2@example.com',
            'password' => Hash::make('password'),
            'role' => 0,
            'email_verified_at' => now(),
        ]);

        // user3（管理者）
        User::create([
            'name' => '管理者ユーザー',
            'email' => 'user3@example.com',
            'password' => Hash::make('password'),
            'role' => 1,
            'email_verified_at' => now(),
        ]);
    }
}
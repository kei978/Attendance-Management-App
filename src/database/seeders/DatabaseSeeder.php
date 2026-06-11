<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(UserSeeder::class);

        if (!app()->environment('testing')) {
            $this->call(AttendanceSeeder::class);
        }
    }
}
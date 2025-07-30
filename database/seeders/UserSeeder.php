<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'test',
            'username' => 'test',
            'password' => Hash::make('test'),
            'token' => 'test'
        ]);

        User::create([
            'name' => 'admin',
            'username' => 'admin',
            'password' => Hash::make('admin'),
            'token' => 'admin'
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@unitru.edu.pe',
            'codigo' => null,
            'role' => 'admin',
            'password' => Hash::make('password1'),
        ]);
    }
}

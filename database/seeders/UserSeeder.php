<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profesor;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // ===============================
        //  ADMIN
        // ===============================
        $admin = User::create([
            'name'     => 'Administrador General',
            'email'    => 'admin@unitru.edu.pe',
            'codigo'   => '0000000001',
            'password' => Hash::make('password1'),
            'role'     => 'admin',
        ]);

        // ===============================
        //  PROFESORES
        // ===============================

        // Profesor 1
        $prof1 = User::create([
            'name'     => 'Profesor Uno',
            'email'    => 'prof1@unitru.edu.pe',
            'codigo'   => '1000000001',
            'password' => Hash::make('password1'),
            'role'     => 'profesor',
        ]);

        Profesor::create([
            'user_id'   => $prof1->id,
            'categoria' => 'asociado',
            'modalidad' => 'tiempo completo',
            'horas'     => 20,
            'estado'    => 'activo',
        ]);

        // Profesor 2
        $prof2 = User::create([
            'name'     => 'Profesor Dos',
            'email'    => 'prof2@unitru.edu.pe',
            'codigo'   => '1000000002',
            'password' => Hash::make('password1'),
            'role'     => 'profesor',
        ]);

        Profesor::create([
            'user_id'   => $prof2->id,
            'categoria' => 'principal',
            'modalidad' => 'tiempo parcial',
            'horas'     => 10,
            'estado'    => 'activo',
        ]);

        // Profesor 3
        $prof3 = User::create([
            'name'     => 'Profesor Tres',
            'email'    => 'prof3@unitru.edu.pe',
            'codigo'   => '1000000003',
            'password' => Hash::make('password1'),
            'role'     => 'profesor',
        ]);

        Profesor::create([
            'user_id'   => $prof3->id,
            'categoria' => 'auxiliar',
            'modalidad' => 'tiempo completo',
            'horas'     => 12,
            'estado'    => 'inactivo',
        ]);

        // ===============================
        //  ALUMNOS
        // ===============================

        User::create([
            'name'     => 'Alumno Uno',
            'email'    => 'alumno1@unitru.edu.pe',
            'codigo'   => '2000000001',
            'password' => Hash::make('password1'),
            'role'     => 'alumno',
        ]);

        User::create([
            'name'     => 'Alumno Dos',
            'email'    => 'alumno2@unitru.edu.pe',
            'codigo'   => '2000000002',
            'password' => Hash::make('password1'),
            'role'     => 'alumno',
        ]);

        User::create([
            'name'     => 'Alumno Tres',
            'email'    => 'alumno3@unitru.edu.pe',
            'codigo'   => '2000000003',
            'password' => Hash::make('password1'),
            'role'     => 'alumno',
        ]);
    }
}

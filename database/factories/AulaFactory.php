<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AulaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'codigo'    => 'A'.fake()->unique()->regexify('[A-Z0-9]{6}'), // UNIQUE, 7 chars p.ej. A3FJ82C
            'nombre'    => 'Aula '.fake()->unique()->numberBetween(100, 999),
            'tipo'      => fake()->randomElement(['Laboratorio', 'Teoria-Practica']), // respeta el CHECK
            'capacidad' => fake()->numberBetween(20, 60),
            // opcionales (nullable):
            'piso'      => fake()->optional()->bothify('#'),        // o algo como '1', '2', etc.
            'edificio'  => fake()->optional()->streetName(),
            // 'activo'  => true, // puedes omitirlo: default true en DB
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Docente>
 */
class DocenteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
// database/factories/DocenteFactory.php
    public function definition(): array {
        return [
            'nombre' => fake()->name(),
            'email'  => fake()->unique()->safeEmail(),
            'activo' => true,
        ];
    }

// database/seeders/DocenteSeeder.php
    public function run(): void {
        \App\Models\Docente::factory(10)->create();
    }

}

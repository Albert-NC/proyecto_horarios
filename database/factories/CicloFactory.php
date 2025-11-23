<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ciclo>
 */
class CicloFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    // database/factories/CicloFactory.php
    public function definition(): array {
        return [
            'nombre' => fake()->unique()->randomElement(['2024-I','2024-II','2025-I']),
            'activo' => true,
        ];
    }

// database/seeders/CicloSeeder.php
    public function run(): void {
        \App\Models\Ciclo::factory(3)->create();
    }

}

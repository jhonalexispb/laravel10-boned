<?php

namespace Database\Factories\Configuration;

use App\Models\Configuration\Departamento;
use Illuminate\Database\Eloquent\Factories\Factory;
class departamentoFactory extends Factory
{   
    protected $model = Departamento::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->uuid,
            'image' => $this->faker->imageUrl(),       // Genera una URL de imagen aleatoria
            'state' => $this->faker->boolean(), // Estado aleatorio
        ];
    }
}

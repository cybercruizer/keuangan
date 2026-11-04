<?php

namespace Database\Factories;

use App\Models\ClassRoom;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nis' => (string) fake()->unique()->numerify('#####'),
            'nisn' => (string) fake()->unique()->numerify('##########'),
            'name' => fake()->name(),
            'gender' => fake()->randomElement(['L', 'P']),
            'class_room_id' => ClassRoom::factory(),
            'category' => fake()->randomElement(['reguler', 'beasiswa_50', 'beasiswa_100']),
            'phone' => fake()->phoneNumber(),
            'parent_name' => fake()->name(),
            'parent_phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'status' => 'aktif',
        ];
    }

    public function reguler(): static
    {
        return $this->state(fn () => ['category' => 'reguler']);
    }

    public function beasiswa50(): static
    {
        return $this->state(fn () => ['category' => 'beasiswa_50']);
    }

    public function beasiswa100(): static
    {
        return $this->state(fn () => ['category' => 'beasiswa_100']);
    }
}

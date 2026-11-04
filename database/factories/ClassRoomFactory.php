<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Major;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassRoom>
 */
class ClassRoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'major_id' => Major::factory(),
            'name' => 'X RPL 1',
            'level' => 'X',
        ];
    }
}

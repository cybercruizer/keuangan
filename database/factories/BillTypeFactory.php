<?php

namespace Database\Factories;

use App\Models\BillType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillType>
 */
class BillTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'SPP Bulanan',
            'amount' => 250000,
            'type' => 'spp',
            'is_discountable' => true,
            'description' => 'SPP Bulanan Siswa',
        ];
    }
}

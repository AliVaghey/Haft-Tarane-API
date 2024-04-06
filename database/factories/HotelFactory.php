<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Hotel>
 */
class HotelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $f = fake();
        return [
            'name' => $f->building(),
            'address' => $f->address(),
            'country' => $f->country(),
            'state' => $f->randomElement(['کرمان', 'شیراز', 'اهواز', 'تهران', 'مشهد']),
            'city' => $f->city(),
            'gallery' => collect(),
        ];
    }
}

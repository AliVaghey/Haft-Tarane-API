<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tour>
 */
class TourFactory extends Factory
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
            'title' => $f->title(),
            'trip_type' => $f->randomElement(['داخلی', 'خارجی', 'طبیعت گردی']),
            'expiration' => $f->randomElement([1, 2, 3, 4, 5, 6]),
            'selling_type' => $f->randomElement(['فروش نقدی', 'فروش اقساطی']),
            'capacity' => $f->randomElement([1, 2, 3, 4, 5, 6, 7, 8, 9]),
            'tour_styles' => collect(['کروز', 'گردشی', 'تفریحی']),
            'evening_support' => true,
            'midnight_support' => true,
            'origin' => $f->city(),
            'destination' => $f->city(),
            'staying_nights' => $f->randomElement([1, 2, 3, 4, 5]),
            'transportation_type' => 'self',
            'start' => $f->dateTime(),
            'end' => $f->dateTime(),
            'hotels' => collect($f->randomElements([1, 2, 3, 4, 5, 6, 7, 8, 9])),
        ];
    }
}

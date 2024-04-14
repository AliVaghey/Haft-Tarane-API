<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AgencyInfo>
 */
class AgencyInfoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->building(),
            'address' => $this->faker->address(),
            'c_phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'zip_code' => $this->faker->postcode(),
            'web_site' => $this->faker->url(),
        ];
    }
}

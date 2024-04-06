<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $f = fake('fa_IR');
        $f_en = fake('en_US');
        return [
            'username' => $f->name(),
            'first_name_fa' => $f->firstName(),
            'first_name_en' => $f_en->firstName(),
            'last_name_fa' => $f->lastName(),
            'last_name_en' => $f_en->lastName(),
            'birth_date' => $f->date(),
            'gender' => $f->randomElement(['female', 'male']),
            'phone' => '09' . $f->randomNumber(9, true),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }
}

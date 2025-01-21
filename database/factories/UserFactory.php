<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory {
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition() {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make($this->faker->password(10, 10)),
            'remember_token' => Str::random(10),

            'country_id' => null,
            'type' => $this->faker->randomElement(['Manager', 'Advertiser', 'Publisher']),
            'active' => Carbon::yesterday(),
            'company' => $this->faker->company(),
            'phone' => $this->faker->phoneNumber(),
            'business_id' => $this->faker->word(),
            'address' => $this->faker->address(),
            'state' => $this->faker->state(),
            'city' => $this->faker->city(),
            'zip' => $this->faker->postcode(),
            'notifications' => [],
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return Factory
     */
    public function unverified() {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}

<?php

namespace Database\Factories;

use App\Models\Promo;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromoFactory extends Factory {
    protected $model = Promo::class;

    public function definition() {
        return [
            'title' => $this->faker->words(3, true),
            'code' => strtoupper($this->faker->word()),
            'bonus' => $this->faker->randomFloat(2, 0, 100),
            'total' => $this->faker->numberBetween(100, 1000),
        ];
    }
}

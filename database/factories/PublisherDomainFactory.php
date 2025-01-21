<?php

namespace Database\Factories;

use App\Models\Domains\PublisherDomain;
use Illuminate\Database\Eloquent\Factories\Factory;

class PublisherDomainFactory extends Factory {
    protected $model = PublisherDomain::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition() {
        return [
            'publisher_id' => null,
            'domain' => 'https://' . $this->faker->domainName(),
            'category_id' => null,
            'approved_at' => now(),
            'approved_by_id' => 1
        ];
    }
}

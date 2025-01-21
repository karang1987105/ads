<?php

namespace Database\Factories;

use App\Models\Domains\AdvertiserDomain;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdvertiserDomainFactory extends Factory {
    protected $model = AdvertiserDomain::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition() {
        return [
            'advertiser_id' => null,
            'domain' => 'https://' . $this->faker->domainName(),
            'approved_at' => now(),
            'approved_by_id' => 1
        ];
    }
}

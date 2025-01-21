<?php

namespace Database\Seeders;

use App\Models\Promo;
use Illuminate\Database\Seeder;

class PromoSeeder extends Seeder {
    public function run() {
        Promo::factory()->count(2)->create();
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    public function run() {
        $this->call(CountrySeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(UserSeeder::class);
        $this->call(AdTypeSeeder::class);
        $this->call(PlaceSeeder::class);
        $this->call(PromoSeeder::class);
    }
}

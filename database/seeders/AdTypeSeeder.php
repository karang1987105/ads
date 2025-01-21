<?php

namespace Database\Seeders;

use App\Models\AdType;
use Illuminate\Database\Seeder;

class AdTypeSeeder extends Seeder {
    public function run() {
        AdType::create(['kind' => 'CPC', 'name' => 'B1', 'active' => true, 'type' => 'Banner', 'width' => 600, 'height' => 200]);
        AdType::create(['kind' => 'CPM', 'name' => 'B2', 'active' => true, 'type' => 'Banner', 'width' => 500, 'height' => 100]);
        AdType::create(['kind' => 'CPV', 'name' => 'V1', 'active' => true, 'type' => 'Video', 'width' => 300, 'height' => 300]);
        AdType::create(['kind' => 'CPV', 'name' => 'V2', 'active' => true, 'type' => 'Video', 'width' => 200, 'height' => 200]);
    }
}

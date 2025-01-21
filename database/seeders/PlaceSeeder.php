<?php

namespace Database\Seeders;

use App\Models\AdType;
use App\Models\Place;
use App\Models\UserPublisher;
use Illuminate\Database\Seeder;
use Str;

class PlaceSeeder extends Seeder {
    public function run() {
        $publishers = UserPublisher::with('domains:publisher_id,id')->get();
        $i = 0;
        AdType::all('id')->pluck('id')->each(function ($adTypeId) use ($publishers, &$i) {
            $domains = $publishers->random()->domains;
            Place::create(['title' => 'Place ' . (++$i), 'ad_type_id' => $adTypeId, 'domain_id' => $domains->random()->id, 'uuid' => Str::uuid()->toString()]);
            //Place::create(['title' => 'Place ' . (++$i), 'ad_type_id' => $adTypeId, 'domain_id' => $domains->random()->id, 'uuid' => Str::uuid()->toString()]);
        });
    }
}

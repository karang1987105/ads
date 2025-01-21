<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryCountry;
use App\Models\Country;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder {
    public function run() {
        $countries = Country::all('id')->shuffle();

        Category::create(['title' => 'Gold', 'cpm' => 3, 'cpc' => 3, 'cpv' => 3, 'revenue_share' => 20, 'active' => true])
            ->countries()->saveMany([
                new CategoryCountry(['country_id' => $countries[0]->id, 'cpm' => 3.1, 'cpc' => 3.1, 'cpv' => 3.1]),
                new CategoryCountry(['country_id' => $countries[1]->id, 'cpm' => 3.1, 'cpc' => 3.1, 'cpv' => 3.1]),
                new CategoryCountry(['country_id' => $countries[2]->id, 'cpm' => 3.1, 'cpc' => 3.1, 'cpv' => 3.1]),
                new CategoryCountry(['country_id' => $countries[3]->id, 'cpm' => 3.1, 'cpc' => 3.1, 'cpv' => 3.1]),
            ]);
        Category::create(['title' => 'Silver', 'cpm' => 2, 'cpc' => 2, 'cpv' => 2, 'revenue_share' => 15, 'active' => true])
            ->countries()->saveMany([
                new CategoryCountry(['country_id' => $countries[4]->id, 'cpm' => 2.1, 'cpc' => 2.1, 'cpv' => 2.1]),
                new CategoryCountry(['country_id' => $countries[5]->id, 'cpm' => 2.1, 'cpc' => 2.1, 'cpv' => 2.1]),
                new CategoryCountry(['country_id' => $countries[6]->id, 'cpm' => 2.1, 'cpc' => 2.1, 'cpv' => 2.1]),
                new CategoryCountry(['country_id' => $countries[7]->id, 'cpm' => 2.1, 'cpc' => 2.1, 'cpv' => 2.1]),
            ]);

        Category::create(['title' => 'Bronze', 'cpm' => 1, 'cpc' => 1, 'cpv' => 1, 'revenue_share' => 10, 'active' => true])
            ->countries()->saveMany([
                new CategoryCountry(['country_id' => $countries[8]->id, 'cpm' => 1.1, 'cpc' => 1.1, 'cpv' => 1.1]),
                new CategoryCountry(['country_id' => $countries[9]->id, 'cpm' => 1.1, 'cpc' => 1.1, 'cpv' => 1.1]),
            ]);
    }
}

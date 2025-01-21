<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Country;
use App\Models\Domains\AdvertiserDomain;
use App\Models\Domains\PublisherDomain;
use App\Models\User;
use App\Models\UserAdvertiser;
use App\Models\UserManager;
use App\Models\UserPublisher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $countries = Country::limit(50)->get();
        $categories = Category::all('id');
        User::factory()->count(2)->create(['type' => 'Manager', 'country_id' => $countries->random()->id])->each(function (User $user) {
            $user->manager()->save(UserManager::factory()->make());
        });
        User::factory()->count(3)->create(['type' => 'Advertiser', 'country_id' => $countries->random()->id])->each(function (User $user) use ($categories) {
            $user->advertiser()->save(new UserAdvertiser())->domains()
                ->saveMany(AdvertiserDomain::factory()->count(2)->make());
        });
        User::factory()->count(3)->create(['type' => 'Publisher', 'country_id' => $countries->random()->id])->each(function (User $user) use ($categories) {
            $user->publisher()->save(new UserPublisher())->domains()
                ->saveMany(PublisherDomain::factory()->count(2)->make(['category_id' => $categories->random()->id]));
        });

        $admin = User::find(1);
        $admin->update(['name' => 'Mr. Admin', 'email' => 'admin@a.com', 'password' => Hash::make('admin')]);
        $admin->manager->is_admin = true;
        $admin->manager->save();
        User::find(2)->update(['name' => 'Mr. Manager', 'email' => 'manager@a.com', 'password' => Hash::make('manager')]);
        User::find(3)->update(['name' => 'Mr. Advertiser', 'email' => 'advertiser@a.com', 'password' => Hash::make('advertiser')]);
        User::find(6)->update(['name' => 'Mr. Publisher', 'email' => 'publisher@a.com', 'password' => Hash::make('publisher')]);
    }
}

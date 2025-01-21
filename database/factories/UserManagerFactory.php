<?php

namespace Database\Factories;

use App\Models\UserManager;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserManagerFactory extends Factory {
    protected $model = UserManager::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition() {
        return [
            'is_admin' => null,
            'publishers' => $this->faker->randomElements(['List', 'Create', 'Update', 'Delete', 'Block', 'Activate', 'Send Email', 'Add Fund', 'Remove Fund', 'Login Behalf'], $this->faker->numberBetween(1, 10)),
            'advertisers' => $this->faker->randomElements(['List', 'Create', 'Update', 'Delete', 'Block', 'Activate', 'Send Email', 'Add Fund', 'Remove Fund', 'Login Behalf'], $this->faker->numberBetween(1, 10)),
            'advertisements' => $this->faker->randomElements(['Create', 'Update', 'Delete', 'Block', 'Activate'], $this->faker->numberBetween(1, 5)),
            'promos' => $this->faker->randomElements(['Create', 'Update', 'Delete'], $this->faker->numberBetween(1, 3)),
            'send_email' => $this->faker->randomElements(['Create', 'Update', 'Delete', 'Send'], $this->faker->numberBetween(1, 2)),
        ];
    }
}

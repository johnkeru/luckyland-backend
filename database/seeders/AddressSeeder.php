<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $address = new Address([
            'barangay' => fake()->streetName,
            'city' => fake()->city(),
            'province' => fake()->state,
        ]);

        foreach (User::all() as $user) {
            $user->address()->save($address);
        }
    }
}

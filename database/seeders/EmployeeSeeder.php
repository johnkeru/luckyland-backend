<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $employees = [
            [
                'firstName' => 'John',
                'lastName' => 'Miller',
                'email' => 'john@gmail.com',
                'phoneNumber' => '09123456781',
                'email_verified_at' => now(),
                'password' => Hash::make('john'),
                'remember_token' => Str::random(10),
            ],
            [
                'firstName' => 'Vince',
                'lastName' => 'Cordovilla',
                'email' => 'vince@gmail.com',
                'phoneNumber' => '09123456781',
                'email_verified_at' => now(),
                'password' => Hash::make('vince'),
                'remember_token' => Str::random(10),
            ],
            [
                'firstName' => 'Kate',
                'lastName' => 'Kirab',
                'email' => 'kate@gmail.com',
                'phoneNumber' => '09123456781',
                'email_verified_at' => now(),
                'password' => Hash::make('kate'),
                'remember_token' => Str::random(10),
            ],
        ];

        foreach ($employees as $index => $employee) {
            $user = User::create($employee);
            $user->roles()->attach($index + 2);

            $address = new Address([
                'barangay' => fake()->streetName,
                'city' => fake()->city(),
                'province' => fake()->state,
            ]);
            $user->address()->save($address);
        }
    }
}

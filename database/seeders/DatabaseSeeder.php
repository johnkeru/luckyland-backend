<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Creating Admin and assign all the system's roles.
        User::insert([
            [
                'firstName' => 'Amy',
                'middleName' => 'P.',
                'lastName' => 'Trinanes',
                'email' => 'keru@gmail.com',
                // 'email' => 'jrquerobin@forbescollege.org',
                'phoneNumber' => '09123456789',
                'graduated_at' => 'Bicol University',
                'description' => "Meet the visionary owner of Luckyland Resort, a hospitality enthusiast with a passion for creating unforgettable experiences. With a keen eye for detail and a commitment to excellence, they ensure that every guest at Luckyland Resort is treated to a world-class stay. From luxurious accommodations to top-notch amenities, the owner's dedication to hospitality shines through, making",
                'facebook' => 'https://www.facebook.com/Etami8',
                'email_verified_at' => now(),
                'password' => Hash::make('keru'),
                'remember_token' => Str::random(10),
            ]
        ]);

        $this->call(RoleSeeder::class);
        User::find(1)->roles()->attach(Role::all());
        $this->call(AddressSeeder::class);

        $this->call(CategorySeeder::class);
        $this->call(ItemSeeder::class);

        $this->call(DeliverySeeder::class);

        $this->call(RoomTypeSeeder::class);
        $this->call(RoomSeeder::class);

        $this->call(CottageTypeSeeder::class);
        $this->call(CottageSeeder::class);

        $this->call(EmployeeSeeder::class);

        // $this->call(CustomerSeeder::class);
    }
}

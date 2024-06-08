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
        // Creating multiple Admins and assign all the system's roles.
        $users = [
            // [
            //     'firstName' => 'Amy',
            //     'middleName' => 'P.',
            //     'lastName' => 'Triñanes',
            //     'email' => 'amytrinanes143@gmail.com',
            //     'phoneNumber' => '09123456789',
            //     'graduated_at' => 'Bicol University',
            //     'description' => "Meet the visionary owner of Luckyland Resort, a hospitality enthusiast with a passion for creating unforgettable experiences. With a keen eye for detail and a commitment to excellence, they ensure that every guest at Luckyland Resort is treated to a world-class stay. From luxurious accommodations to top-notch amenities, the owner's dedication to hospitality shines through, making",
            //     'facebook' => 'https://www.facebook.com/Etami8',
            //     'email_verified_at' => now(),
            //     'password' => Hash::make('November101976'),
            //     'remember_token' => Str::random(10),
            // ],
            // [
            //     'firstName' => 'Amy',
            //     'middleName' => 'P.',
            //     'lastName' => 'Triñanes',
            //     'email' => 'amytrinanes1@gmail.com',
            //     'phoneNumber' => '09123456789',
            //     'graduated_at' => 'Bicol University',
            //     'description' => "Meet the visionary owner of Luckyland Resort, a hospitality enthusiast with a passion for creating unforgettable experiences. With a keen eye for detail and a commitment to excellence, they ensure that every guest at Luckyland Resort is treated to a world-class stay. From luxurious accommodations to top-notch amenities, the owner's dedication to hospitality shines through, making",
            //     'facebook' => 'https://www.facebook.com/Etami8',
            //     'email_verified_at' => now(),
            //     'password' => Hash::make('November101976'),
            //     'remember_token' => Str::random(10),
            // ],
            [
                'firstName' => 'John',
                'middleName' => 'M.',
                'lastName' => 'Keru',
                'email' => env('APP_PROD') ? 'johnkeru128@gmail.com' : 'keru@gmail.com',
                'phoneNumber' => '09876543210',
                'graduated_at' => 'Forbes College',
                'description' => "Developer",
                'facebook' => 'https://www.facebook.com/profile.php?id=100009257219664',
                'email_verified_at' => now(),
                'password' => Hash::make('keru'),
                'remember_token' => Str::random(10),
            ],
            // [
            //     'firstName' => 'Jun',
            //     'middleName' => 'P.',
            //     'lastName' => 'Triñanes',
            //     'email' => 'vstjr7672@gmail.com',
            //     'phoneNumber' => '09876543210',
            //     'graduated_at' => 'Bicol University',
            //     'description' => "Developer",
            //     'facebook' => 'https://www.facebook.com/jun.trinanes.1',
            //     'email_verified_at' => now(),
            //     'password' => Hash::make('vstjr7672'),
            //     'remember_token' => Str::random(10),
            // ],
        ];

        User::insert($users);

        $this->call(RoleSeeder::class);

        // Assign all roles to each user
        $allRoles = Role::all();
        foreach (User::all() as $user) {
            $user->roles()->attach($allRoles);
        }

        $this->call(AddressSeeder::class);

        $this->call(CategorySeeder::class);
        $this->call(ItemSeeder::class);

        $this->call(DeliverySeeder::class);

        $this->call(RoomTypeSeeder::class);
        $this->call(CottageTypeSeeder::class);
        $this->call(OtherTypeSeeder::class);

        $this->call(EmployeeSeeder::class);

        // $this->call(CustomerSeeder::class);

        $this->call(FAQSeeder::class);
    }
}

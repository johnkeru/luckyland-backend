<?php

namespace Database\Seeders;

use Database\Seeders\Rooms\ACCoupleRoomSeeder;
use Database\Seeders\Rooms\ACFamilyRoom;
use Database\Seeders\Rooms\EFCoupleRoomSeeder;
use Database\Seeders\Rooms\EFFamilyRoom;
use Illuminate\Database\Seeder;

class RoomTypeSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ACCoupleRoomSeeder::class);
        $this->call(EFCoupleRoomSeeder::class);
        $this->call(ACFamilyRoom::class);
        $this->call(EFFamilyRoom::class);
    }
}

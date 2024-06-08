<?php

namespace Database\Seeders;

// use Database\Seeders\Others\ClosedHall2Seeder;
use Database\Seeders\Others\OpenHall1Seeder;
use Database\Seeders\Others\TreeHouseSeeder;
use Illuminate\Database\Seeder;


// Other::where('id', 2)->with(['items' => function ($query) {
//     $query->whereHas('categories', function ($query) {
//         $query->where('name', 'Other Add Ons');
//     });
// }])->get();

class OtherTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(TreeHouseSeeder::class);
        $this->call(OpenHall1Seeder::class);
        // $this->call(ClosedHall2Seeder::class);
    }
}

<?php

namespace Database\Seeders;

use Database\Seeders\Cottages\AnahawCottageSeeder;
use Database\Seeders\Cottages\BigCottage1Seeder;
use Database\Seeders\Cottages\BigCottage2Seeder;
use Database\Seeders\Cottages\BigCottage3Seeder;
use Database\Seeders\Cottages\BigCottage4Seeder;
use Database\Seeders\Cottages\DuplexCottageSeeder;
use Database\Seeders\Cottages\GazeeboTypeCottageSeeder;
use Database\Seeders\Cottages\PoolSideCottageSeeder1;
use Database\Seeders\Cottages\PoolSideCottageSeeder2;
use Illuminate\Database\Seeder;

class CottageTypeSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PoolSideCottageSeeder1::class);
        $this->call(PoolSideCottageSeeder2::class);
        $this->call(AnahawCottageSeeder::class);
        $this->call(GazeeboTypeCottageSeeder::class);
        $this->call(DuplexCottageSeeder::class);
        $this->call(BigCottage1Seeder::class);
        $this->call(BigCottage2Seeder::class);
        $this->call(BigCottage3Seeder::class);
        $this->call(BigCottage4Seeder::class);
    }
}

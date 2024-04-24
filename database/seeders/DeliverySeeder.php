<?php

namespace Database\Seeders;

use App\Models\Delivery;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeliverySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $item_data = [
            [
                'item_id' => 1,
                'quantity' => 20
            ],
            [
                'item_id' => 2,
                'quantity' => 100
            ],
        ];

        $delivery = Delivery::create(['companyName' => 'SM', 'user_id' => 1, 'arrivalDate' => now()]);
        foreach ($item_data as $itemData) {
            $delivery->items()->attach($itemData['item_id'], ['quantity' => $itemData['quantity']]);
        }

        $item_data2 = [
            [
                'item_id' => 4,
                'quantity' => 15
            ],
            [
                'item_id' => 3,
                'quantity' => 10
            ],
        ];

        $delivery2 = Delivery::create(['companyName' => 'Shoppee', 'user_id' => 1, 'arrivalDate' => now()]);
        foreach ($item_data2 as $itemData2) {
            $delivery2->items()->attach($itemData2['item_id'], ['quantity' => $itemData2['quantity']]);
        }
    }
}

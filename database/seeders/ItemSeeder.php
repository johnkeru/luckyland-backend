<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $itemsForResort = [
            [
                'name' => 'Bottled Water',
                'price' => 25,
                'description' => 'Bottled Water',
                'status' => 'In Stock',
                'maxQuantity' => 50,
                'currentQuantity' => 50,
                'reOrderPoint' => 20,
            ],
            [
                'name' => 'Tide',
                'price' => 15,
                'description' => 'Laundry Detergent',
                'status' => 'In Stock',
                'maxQuantity' => 50,
                'currentQuantity' => 50,
                'reOrderPoint' => 20,
            ],
            [
                'name' => 'Lysol',
                'price' => 8,
                'description' => 'Disinfectant Spray',
                'status' => 'In Stock',
                'maxQuantity' => 50,
                'currentQuantity' => 50,
                'reOrderPoint' => 20,
            ],
        ];
        foreach ($itemsForResort as $itemForResort) {
            $item = Item::create($itemForResort);
            $item->categories()->attach(1);
        }

        $itemsForRoom = [
            [
                'name' => 'Soap',
                'price' => 20,
                'description' => 'Soap',
                'status' => 'In Stock',
                'maxQuantity' => 50,
                'currentQuantity' => 50,
                'reOrderPoint' => 20,
            ],
            [
                'name' => 'Shampoo',
                'price' => 10,
                'description' => 'Shampoo',
                'status' => 'In Stock',
                'maxQuantity' => 50,
                'currentQuantity' => 50,
                'reOrderPoint' => 20,
            ],
            [
                'name' => 'Toothbrush',
                'price' => 12,
                'description' => 'Toothbrush',
                'status' => 'In Stock',
                'maxQuantity' => 50,
                'currentQuantity' => 50,
                'reOrderPoint' => 20,
            ],
            [
                'name' => 'Toothpaste',
                'price' => 20,
                'description' => 'Toothpaste',
                'status' => 'In Stock',
                'maxQuantity' => 50,
                'currentQuantity' => 50,
                'reOrderPoint' => 20,
            ],
        ];
        foreach ($itemsForRoom as $itemForRoom) {
            $item = Item::create($itemForRoom);
            $item->categories()->attach(2);
        }

        $itemsForResortAndRooms = [
            [
                'name' => 'Towel',
                'price' => 20,
                'description' => 'towel',
                'status' => 'In Stock',
                'maxQuantity' => 50,
                'currentQuantity' => 50,
                'reOrderPoint' => 20,
            ],
            [
                'name' => 'Pillow',
                'price' => 20,
                'description' => 'towel',
                'status' => 'In Stock',
                'maxQuantity' => 50,
                'currentQuantity' => 50,
                'reOrderPoint' => 20,
            ],
        ];
        foreach ($itemsForResortAndRooms as $itemsForResortAndRoom) {
            $item = Item::create($itemsForResortAndRoom);
            $item->categories()->attach([1, 2]);
        }

        $cottageAddOns = [
            [
                'name' => 'Karaoke',
                'price' => 50,
                'description' => 'Just sing a long',
                'status' => 'In Stock',
                'maxQuantity' => 50,
                'currentQuantity' => 50,
                'reOrderPoint' => 20,
            ],
        ];
        foreach ($cottageAddOns as $cottageAddOn) {
            $item = Item::create($cottageAddOn);
            $item->categories()->attach(5);
        }

        $roomAddOns = [
            [
                'name' => 'Bed',
                'price' => 28,
                'description' => 'comfortable to pee',
                'status' => 'In Stock',
                'maxQuantity' => 50,
                'currentQuantity' => 50,
                'reOrderPoint' => 20,
            ],
        ];
        foreach ($roomAddOns as $roomAddOn) {
            $item = Item::create($roomAddOn);
            $item->categories()->attach(4);
        }

        $bothAddOns = [
            [
                'name' => 'Grill Grate',
                'price' => 50,
                'description' => '',
                'status' => 'In Stock',
                'maxQuantity' => 50,
                'currentQuantity' => 50,
                'reOrderPoint' => 20,
            ],
        ];
        foreach ($bothAddOns as $bothAddOn) {
            $item = Item::create($bothAddOn);
            $item->categories()->attach([4, 5]);
        }
    }
}

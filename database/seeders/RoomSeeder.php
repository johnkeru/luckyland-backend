<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Room;
use App\Models\RoomImage;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $roomsData = [
            [
                'name' => 'Room 101',
                'room_type_id' => 1,
            ],
            [
                'name' => 'Room 102',
                'room_type_id' => 1,
            ],
            [
                'name' => 'Room 103',
                'room_type_id' => 1,
            ],
            [
                'name' => 'Room 104',
                'room_type_id' => 1,
            ],
            [
                'name' => 'Room 105',
                'room_type_id' => 1,
            ],
            [
                'name' => 'Room 106',
                'room_type_id' => 2,
            ],
            [
                'name' => 'Room 107',
                'room_type_id' => 2,
            ],
            [
                'name' => 'Room 108',
                'room_type_id' => 2,
            ],
            [
                'name' => 'Room 109',
                'room_type_id' => 2,
            ],
        ];

        foreach ($roomsData as $roomData) {
            $room = Room::create($roomData);
            $this->setImages($room->id); //image

            $roomType = RoomType::where('id', $roomData['room_type_id'])->first();

            // Retrieve all item IDs where associated categories have the name 'Room'
            $itemIds = Item::whereHas('categories', function ($query) {
                $query->where('name', 'Room');
            })->pluck('id')->toArray();

            $itemRooms = [];
            foreach ($itemIds as $itemId) {
                $itemRooms[$itemId] = [
                    'minQuantity' => $roomType->minCapacity, // the items will deduct once it book based on minCapacity
                    'maxQuantity' => $roomType->maxCapacity,
                ];
            }
            $room->items()->attach($itemRooms); //items
        }
    }

    private function setImages($room_id)
    {
        $images = [
            [
                'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716089173/442487600_367902302930391_3012646217232738360_n_kfknoi.jpg',
                'room_id' => $room_id
            ],
            [
                'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716089202/442494026_367900836263871_890804219610397319_n_rthdry.jpg',
                'room_id' => $room_id
            ],
            [
                'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716089208/440901151_367900942930527_8608555542000400424_n_zjpfyg.jpg',
                'room_id' => $room_id
            ],
            [
                'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716089222/441288097_367900976263857_2100345856779005616_n_icmkgp.jpg',
                'room_id' => $room_id
            ],
            [
                'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716089228/442494034_367901046263850_194221314086700140_n_hgqmzv.jpg',
                'room_id' => $room_id
            ],
            [
                'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716089259/441287396_367901202930501_560307115632412192_n_ajub9s.jpg',
                'room_id' => $room_id
            ],
        ];

        foreach ($images as $img) {
            RoomImage::create($img);
        }
    }
}

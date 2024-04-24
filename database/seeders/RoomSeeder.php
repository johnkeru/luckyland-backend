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
                'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1712931182/pxm41t5ltg6j9grmujhp.webp',
                'room_id' => $room_id
            ],
            [
                'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1712931580/lxebu5zmf93mcia0quen.webp',
                'room_id' => $room_id
            ],
            [
                'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1712931580/txyazkthaenzorqf39rm.webp',
                'room_id' => $room_id
            ],
            [
                'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1712931581/bleghytopcm2l49bkatn.jpg',
                'room_id' => $room_id
            ],
        ];

        foreach ($images as $img) {
            RoomImage::create($img);
        }
    }
}

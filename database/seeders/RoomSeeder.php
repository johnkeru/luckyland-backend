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
                'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1714146712/435150110_3673487429560716_1023056281452641595_n_thkyeo.jpg',
                'room_id' => $room_id
            ],
            [
                'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1714146487/435010970_1760424487770875_441501228963471840_n_bx1szd.jpg',
                'room_id' => $room_id
            ],
            [
                'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1714146476/384173444_702795625027230_1127771356806399914_n_sn1zyw.jpg',
                'room_id' => $room_id
            ],
            [
                'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1714146474/435068781_760031502899526_1012775348193875190_n_etul91.jpg',
                'room_id' => $room_id
            ],
        ];

        foreach ($images as $img) {
            RoomImage::create($img);
        }
    }
}

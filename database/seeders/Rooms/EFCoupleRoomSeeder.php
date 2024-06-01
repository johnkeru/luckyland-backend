<?php

namespace Database\Seeders\Rooms;

use App\Models\Item;
use App\Models\Room;
use App\Models\RoomAttribute;
use App\Models\RoomImage;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

class EFCoupleRoomSeeder extends Seeder
{
    private $type = 'EF Couple Rooms';

    public function run(): void
    {
        $this->setAttributes(); // initialize the attributes.
        $roomType = RoomType::create([
            'description' => 'Experience the perfect blend of intimacy and camaraderie in our Friends/Couple Room. This room offers a cozy retreat where you can relax and reconnect. Enjoy the comfort of shared moments and the privacy you need for a memorable stay with your favorite companion.',
            'type' => $this->type,
            'price' => 2000,
            'minCapacity' => 2,
            'maxCapacity' => 4,
        ]);
        $attributeIds = RoomAttribute::where('type', $this->type)->pluck('id');
        $roomType->attributes()->attach($attributeIds); //attributes
        $this->roomUnits($roomType->id);
    }

    private function setAttributes()
    {
        $attributes = [
            [
                'name' => '50” LED TV Cable Satellite Television with HD Channels',
                'type' => $this->type,
            ],
            [
                'name' => 'Complimentary Wifi Internet Access',
                'type' => $this->type,
            ],
            [
                'name' => 'Comfort Room',
                'type' => $this->type,
            ],
            [
                'name' => '1 Bed',
                'type' => $this->type,
            ],
            [
                'name' => 'Cabinet',
                'type' => $this->type,
            ],
            [
                'name' => 'Extra Bed (+2 pax)',
                'type' => $this->type,
            ],
            [
                'name' => 'Electric Fan',
                'type' => $this->type,
            ],
        ];

        foreach ($attributes as $attr) {
            RoomAttribute::create($attr);
        }
    }

    private function roomUnits(int $roomTypeId): void
    {
        $roomData = [
            [
                'name' => 'EF Couple Room 5',
                'room_type_id' => $roomTypeId,
                'images' => fn (int $roomId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241901/rooms/coup/ef/IMG20240527101810_xmt5fc.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241898/rooms/coup/ef/IMG20240527101704_egjt1d.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241898/rooms/coup/ef/IMG20240527101647_yhvwhf.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241897/rooms/coup/ef/IMG20240527101518_plktim.jpg',
                        'room_id' => $roomId
                    ],
                ]
            ],
            [
                'name' => 'EF Couple Room 6',
                'room_type_id' => $roomTypeId,
                'images' => fn (int $roomId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241901/rooms/coup/ef/IMG20240527101810_xmt5fc.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241898/rooms/coup/ef/IMG20240527101704_egjt1d.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241898/rooms/coup/ef/IMG20240527101647_yhvwhf.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241897/rooms/coup/ef/IMG20240527101518_plktim.jpg',
                        'room_id' => $roomId
                    ],
                ]
            ],
            [
                'name' => 'EF Couple Room 7',
                'room_type_id' => $roomTypeId,
                'images' => fn (int $roomId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241901/rooms/coup/ef/IMG20240527101810_xmt5fc.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241898/rooms/coup/ef/IMG20240527101704_egjt1d.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241898/rooms/coup/ef/IMG20240527101647_yhvwhf.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241897/rooms/coup/ef/IMG20240527101518_plktim.jpg',
                        'room_id' => $roomId
                    ],
                ]
            ],
            [
                'name' => 'EF Couple Room 8',
                'room_type_id' => $roomTypeId,
                'images' => fn (int $roomId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241901/rooms/coup/ef/IMG20240527101810_xmt5fc.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241898/rooms/coup/ef/IMG20240527101704_egjt1d.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241898/rooms/coup/ef/IMG20240527101647_yhvwhf.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241897/rooms/coup/ef/IMG20240527101518_plktim.jpg',
                        'room_id' => $roomId
                    ],
                ]
            ],
        ];


        foreach ($roomData as $data) {
            $imagesCallback = $data['images'];
            unset($data['images']);
            $room = Room::create($data);
            foreach ($imagesCallback($room->id) as $imageData) {
                RoomImage::create($imageData);
            }

            $roomType = RoomType::where('id', $data['room_type_id'])->first();

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
}

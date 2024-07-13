<?php

namespace Database\Seeders\Rooms;

use App\Models\Item;
use App\Models\Room;
use App\Models\RoomAttribute;
use App\Models\RoomImage;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

class ACCoupleRoomSeeder extends Seeder
{
    private $type = 'AC Couple Rooms';

    public function run(): void
    {
        $this->setAttributes(); // initialize the attributes.
        $roomType = RoomType::create([
            'description' => 'Experience the perfect blend of intimacy and camaraderie in our Friends/Couple Room. This room offers a cozy retreat where you can relax and reconnect. Enjoy the comfort of shared moments and the privacy you need for a memorable stay with your favorite companion.',
            'type' => $this->type,
            'price' => 2500,
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
                'name' => 'Air Conditioning',
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
                'name' => 'AC Couple Room 1',
                'room_type_id' => $roomTypeId,
                'images' => fn (int $roomId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241900/rooms/coup/ac/IMG20240527101346_zwdfqo.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241901/rooms/coup/ac/IMG20240527101500_jwgn5x.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241900/rooms/coup/ac/IMG20240527101416_ce8upn.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717243347/rooms/fam/IMG20240527101230_ij9gcw.jpg',
                        'room_id' => $roomId
                    ],
                ]
            ],
            [
                'name' => 'AC Couple Room 2',
                'room_type_id' => $roomTypeId,
                'images' => fn (int $roomId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241900/rooms/coup/ac/IMG20240527101346_zwdfqo.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241901/rooms/coup/ac/IMG20240527101500_jwgn5x.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241900/rooms/coup/ac/IMG20240527101416_ce8upn.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717243347/rooms/fam/IMG20240527101230_ij9gcw.jpg',
                        'room_id' => $roomId
                    ],
                ]
            ],
            [
                'name' => 'AC Couple Room 3',
                'room_type_id' => $roomTypeId,
                'images' => fn (int $roomId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241900/rooms/coup/ac/IMG20240527101346_zwdfqo.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241901/rooms/coup/ac/IMG20240527101500_jwgn5x.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241900/rooms/coup/ac/IMG20240527101416_ce8upn.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717243347/rooms/fam/IMG20240527101230_ij9gcw.jpg',
                        'room_id' => $roomId
                    ],
                ]
            ],
            [
                'name' => 'AC Couple Room 4',
                'room_type_id' => $roomTypeId,
                'images' => fn (int $roomId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241900/rooms/coup/ac/IMG20240527101346_zwdfqo.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241901/rooms/coup/ac/IMG20240527101500_jwgn5x.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717241900/rooms/coup/ac/IMG20240527101416_ce8upn.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717243347/rooms/fam/IMG20240527101230_ij9gcw.jpg',
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

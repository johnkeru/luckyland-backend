<?php

namespace Database\Seeders\Rooms;

use App\Models\Item;
use App\Models\Room;
use App\Models\RoomAttribute;
use App\Models\RoomImage;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

class ACFamilyRoom2 extends Seeder
{
    private $type = 'AC Family Rooms 2';

    public function run(): void
    {
        $this->setAttributes(); // initialize the attributes.
        $roomType = RoomType::create([
            'description' => 'The Family Room offers a warm and welcoming space for your loved ones to gather and unwind. Designed with family in mind, this room provides comfortable accommodations and convenient amenities, ensuring a delightful stay for all ages.',
            'type' => $this->type,
            'price' => 3500,
            'minCapacity' => 4,
            'maxCapacity' => 6,
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
                'name' => '2 Bed',
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
                'name' => 'AC Family Rooms 2 #5',
                'room_type_id' => $roomTypeId,
                'images' => fn (int $roomId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717243781/rooms/fam/05.1_nb3hiv.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717243349/rooms/fam/IMG20240527101142_oqlmop.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717243345/rooms/fam/IMG20240527101203_n0s8u2.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717243347/rooms/fam/IMG20240527101230_ij9gcw.jpg',
                        'room_id' => $roomId
                    ],
                ]
            ],
            [
                'name' => 'AC Family Rooms 2 #6',
                'room_type_id' => $roomTypeId,
                'images' => fn (int $roomId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717243781/rooms/fam/05.1_nb3hiv.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717243349/rooms/fam/IMG20240527101142_oqlmop.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717243345/rooms/fam/IMG20240527101203_n0s8u2.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717243347/rooms/fam/IMG20240527101230_ij9gcw.jpg',
                        'room_id' => $roomId
                    ],
                ]
            ],
            [
                'name' => 'AC Family Rooms 2 #7',
                'room_type_id' => $roomTypeId,
                'images' => fn (int $roomId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717243781/rooms/fam/05.1_nb3hiv.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717243349/rooms/fam/IMG20240527101142_oqlmop.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717243345/rooms/fam/IMG20240527101203_n0s8u2.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717243347/rooms/fam/IMG20240527101230_ij9gcw.jpg',
                        'room_id' => $roomId
                    ],
                ]
            ],
            [
                'name' => 'AC Family Rooms 2 #8',
                'room_type_id' => $roomTypeId,
                'images' => fn (int $roomId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717243781/rooms/fam/05.1_nb3hiv.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717243349/rooms/fam/IMG20240527101142_oqlmop.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717243345/rooms/fam/IMG20240527101203_n0s8u2.jpg',
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

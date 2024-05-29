<?php

namespace Database\Seeders\Rooms;

use App\Models\Item;
use App\Models\Room;
use App\Models\RoomAttribute;
use App\Models\RoomImage;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

class ACFamilyRoom extends Seeder
{
    private $type = 'AC Family Rooms';

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
                'name' => 'Extra Bed (+2 capacity)',
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
                'name' => 'AC Family Room 1',
                'room_type_id' => $roomTypeId,
                'images' => fn (int $roomId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716089173/442487600_367902302930391_3012646217232738360_n_kfknoi.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716089202/442494026_367900836263871_890804219610397319_n_rthdry.jpg',
                        'room_id' => $roomId
                    ],
                ]
            ],
            [
                'name' => 'AC Family Room 2',
                'room_type_id' => $roomTypeId,
                'images' => fn (int $roomId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716089173/442487600_367902302930391_3012646217232738360_n_kfknoi.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716089202/442494026_367900836263871_890804219610397319_n_rthdry.jpg',
                        'room_id' => $roomId
                    ],
                ]
            ],
            [
                'name' => 'AC Family Room 3',
                'room_type_id' => $roomTypeId,
                'images' => fn (int $roomId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716089173/442487600_367902302930391_3012646217232738360_n_kfknoi.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716089202/442494026_367900836263871_890804219610397319_n_rthdry.jpg',
                        'room_id' => $roomId
                    ],
                ]
            ],
            [
                'name' => 'AC Family Room 4',
                'room_type_id' => $roomTypeId,
                'images' => fn (int $roomId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716089173/442487600_367902302930391_3012646217232738360_n_kfknoi.jpg',
                        'room_id' => $roomId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716089202/442494026_367900836263871_890804219610397319_n_rthdry.jpg',
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

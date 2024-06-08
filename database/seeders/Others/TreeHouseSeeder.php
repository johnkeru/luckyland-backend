<?php

namespace Database\Seeders\Others;

use App\Models\Other;
use App\Models\OtherAttribute;
use App\Models\OtherImage;
use App\Models\OtherType;
use Illuminate\Database\Seeder;

class TreeHouseSeeder extends Seeder
{
    private $type = 'Tree Houses';

    public function run(): void
    {
        $this->setAttributes(); // initialize the attributes.
        $otherType = OtherType::create([
            'description' => 'A spacious retreat with a capacity for 20 guests, ideal for gatherings and relaxation.',
            'type' => $this->type,
            'price' => 2000,
            'capacity' => 20,
        ]);
        $attributeIds = OtherAttribute::where('type', $this->type)->pluck('id');
        $otherType->attributes()->attach($attributeIds); //attributes
        $this->otherUnits($otherType->id);
    }

    private function setAttributes()
    {
        $attributes = [
            [
                'name' => 'Complimentary Wifi Internet Access',
                'type' => $this->type,
            ],
            [
                'name' => 'Good for 20 pax',
                'type' => $this->type,
            ],
        ];

        foreach ($attributes as $attr) {
            OtherAttribute::create($attr);
        }
    }

    private function otherUnits(int $otherTypeId): void
    {
        $otherData = [
            [
                'name' => 'Tree House',
                'other_type_id' => $otherTypeId,
                'images' => fn (int $otherId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717244425/others/IMG20240601093935_l0lxzq.jpg',
                        'other_id' => $otherId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717244427/others/IMG20240601093946_ewzynd.jpg',
                        'other_id' => $otherId
                    ],

                ]
            ],
            // [
            //     'name' => 'Tree House 2',
            //     'other_type_id' => $otherTypeId,
            //     'images' => fn (int $otherId) => [
            //         [
            //             'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717244425/others/IMG20240601093935_l0lxzq.jpg',
            //             'other_id' => $otherId
            //         ],
            //         [
            //             'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717244427/others/IMG20240601093946_ewzynd.jpg',
            //             'other_id' => $otherId
            //         ],

            //     ]
            // ],
        ];


        foreach ($otherData as $data) {
            $imagesCallback = $data['images'];
            unset($data['images']);
            $other = Other::create($data);
            foreach ($imagesCallback($other->id) as $imageData) {
                OtherImage::create($imageData);
            }
        }
    }
}

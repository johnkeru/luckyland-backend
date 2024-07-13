<?php

namespace Database\Seeders\Others;

use App\Models\Item;
use App\Models\Other;
use App\Models\OtherAttribute;
use App\Models\OtherImage;
use App\Models\OtherType;
use Illuminate\Database\Seeder;

class OpenHall1Seeder extends Seeder
{
    private $type = 'Open Hall';

    public function run(): void
    {
        $this->setAttributes(); // initialize the attributes.
        $otherType = OtherType::create([
            'description' => 'A spacious retreat with a capacity for 120 guests, ideal for gatherings, birthdays, weddings, and other special events.',
            'type' => $this->type,
            'price' => 10000,
            'capacity' => 120,
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
                'name' => 'Good for 120 pax',
                'type' => $this->type,
            ],
            [
                'name' => 'Free use of Videoke',
                'type' => $this->type,
            ],
            [
                'name' => 'Free use of Balloon stand',
                'type' => $this->type,
            ],
            [
                'name' => 'Free use of grill stand',
                'type' => $this->type,
            ],
            [
                'name' => 'Free use of Love seat',
                'type' => $this->type,
            ],
            [
                'name' => 'Free use of Tiffany chairs & tables with cloth',
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
                'name' => 'Open Hall',
                'other_type_id' => $otherTypeId,
                'images' => fn (int $otherId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717810080/others/Open%20Hall/IMG20240604072237_h3ovxg.jpg',
                        'other_id' => $otherId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717810079/others/Open%20Hall/IMG20240604072253_ia0klp.jpg',
                        'other_id' => $otherId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717810082/others/Open%20Hall/IMG20240604072401_ia2acq.jpg',
                        'other_id' => $otherId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717810079/others/Open%20Hall/IMG20240604072317_jewqbv.jpg',
                        'other_id' => $otherId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717810080/others/Open%20Hall/IMG20240604072358_itseww.jpg',
                        'other_id' => $otherId
                    ],
                ]
            ],
        ];

        foreach ($otherData as $data) {
            $imagesCallback = $data['images'];
            unset($data['images']);
            $other = Other::create($data);
            foreach ($imagesCallback($other->id) as $imageData) {
                OtherImage::create($imageData);
            }
        }

        // Retrieve all item IDs where associated categories have the name 'Room'
        $itemIds = Item::whereHas('categories', function ($query) {
            $query->where('name', 'Other');
        })->pluck('id')->toArray();

        $itemOthers = [];
        foreach ($itemIds as $itemId) {
            $itemOthers[$itemId] = [
                'quantity' => 1, // the items will deduct once. bcz minimun is 1
            ];
        }
        $other->items()->attach($itemOthers); //items
    }
}

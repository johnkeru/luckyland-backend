<?php

namespace Database\Seeders\Others;

use App\Models\Other;
use App\Models\OtherAttribute;
use App\Models\OtherImage;
use App\Models\OtherType;
use Illuminate\Database\Seeder;

class OpenHall1Seeder extends Seeder
{
    private $type = 'Open Hall 1';

    public function run(): void
    {
        $this->setAttributes(); // initialize the attributes.
        $otherType = OtherType::create([
            'description' => 'A spacious retreat with a capacity for 120 guests, ideal for gatherings and relaxation.',
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
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716088997/441349048_367900629597225_4736797796269193821_n_vnhcnr.jpg',
                        'other_id' => $otherId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716088954/441550028_367900606263894_1442755070444327851_n_hdwxji.jpg',
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
    }
}

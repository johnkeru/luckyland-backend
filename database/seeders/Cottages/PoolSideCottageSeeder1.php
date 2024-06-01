<?php

namespace Database\Seeders\Cottages;

use App\Models\Cottage;
use App\Models\CottageAttribute;
use App\Models\CottageImage;
use App\Models\CottageType;
use Illuminate\Database\Seeder;

class PoolSideCottageSeeder1 extends Seeder
{
    private $type = 'Poolside Cottages 1';

    public function run(): void
    {
        $this->setAttributes(); // initialize the attributes.
        $cottageType = CottageType::create([
            'description' => 'A spacious retreat with a capacity for 15 guests, ideal for gatherings and relaxation.',
            'type' => $this->type,
            'price' => 700,
            'capacity' => 15,
        ]);
        $attributeIds = CottageAttribute::where('type', $this->type)->pluck('id');
        $cottageType->attributes()->attach($attributeIds); //attributes
        $this->cottageUnits($cottageType->id);
    }

    private function setAttributes()
    {
        $attributes = [
            [
                'name' => 'Complimentary Wifi Internet Access',
                'type' => $this->type,
            ],
            [
                'name' => 'Good for 15 pax',
                'type' => $this->type,
            ],
        ];

        foreach ($attributes as $attr) {
            CottageAttribute::create($attr);
        }
    }

    private function cottageUnits(int $cottageTypeId): void
    {
        $cottageData = [
            [
                'name' => 'Poolside Cottage 1',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717201462/cottages/pool%20side%20cottages%201/IMG20240601071021_qd1f9s.jpg',
                        'cottage_id' => $cottageId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717201462/cottages/pool%20side%20cottages%201/IMG20240601071041_gvmrkt.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Poolside Cottage 2',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717201462/cottages/pool%20side%20cottages%201/IMG20240601071210_sshtry.jpg',
                        'cottage_id' => $cottageId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717201474/cottages/pool%20side%20cottages%201/IMG20240601071218_o9uwey.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Poolside Cottage 3',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717201475/cottages/pool%20side%20cottages%201/IMG20240601071320_cqvy4n.jpg',
                        'cottage_id' => $cottageId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717201418/cottages/pool%20side%20cottages%201/IMG20240601071326_gdwdvd.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Poolside Cottage 4',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717201475/cottages/pool%20side%20cottages%201/IMG20240601071410_wpqyah.jpg',
                        'cottage_id' => $cottageId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717201425/cottages/pool%20side%20cottages%201/IMG20240601071417_pwjaiq.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Poolside Cottage 5',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717201428/cottages/pool%20side%20cottages%201/IMG20240601071431_b3fxof.jpg',
                        'cottage_id' => $cottageId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717201420/cottages/pool%20side%20cottages%201/IMG20240601071436_wn1wma.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Poolside Cottage 7',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717201487/cottages/pool%20side%20cottages%201/IMG20240601071532_zzbpmc.jpg',
                        'cottage_id' => $cottageId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717201418/cottages/pool%20side%20cottages%201/IMG20240601071539_vicmru.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Poolside Cottage 8',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717201551/cottages/pool%20side%20cottages%201/IMG20240601071552_hxtjzj.jpg',
                        'cottage_id' => $cottageId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717201562/cottages/pool%20side%20cottages%201/IMG20240601071605_btubmx.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Poolside Cottage 9',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717201478/cottages/pool%20side%20cottages%201/IMG20240601071644_norn3t.jpg',
                        'cottage_id' => $cottageId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717201481/cottages/pool%20side%20cottages%201/IMG20240601071651_yuuv3g.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Poolside Cottage 10',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717201429/cottages/pool%20side%20cottages%201/IMG20240601071703_tzntst.jpg',
                        'cottage_id' => $cottageId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717201418/cottages/pool%20side%20cottages%201/IMG20240601071709_sdqaau.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Poolside Cottage 11',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717201437/cottages/pool%20side%20cottages%201/IMG20240601071718_td0dkh.jpg',
                        'cottage_id' => $cottageId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717201437/cottages/pool%20side%20cottages%201/IMG20240601071724_icfiwc.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Poolside Cottage 12',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717201558/cottages/pool%20side%20cottages%201/IMG20240601071735_fjhfwk.jpg',
                        'cottage_id' => $cottageId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717201459/cottages/pool%20side%20cottages%201/IMG20240601071738_ngu162.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Poolside Cottage 13',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717201460/cottages/pool%20side%20cottages%201/IMG20240601071752_mnfre7.jpg',
                        'cottage_id' => $cottageId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717201465/cottages/pool%20side%20cottages%201/IMG20240601071756_ag3loi.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
        ];


        foreach ($cottageData as $data) {
            $imagesCallback = $data['images'];
            unset($data['images']);
            $cottage = Cottage::create($data);
            foreach ($imagesCallback($cottage->id) as $imageData) {
                CottageImage::create($imageData);
            }
        }
    }
}

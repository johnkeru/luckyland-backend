<?php

namespace Database\Seeders\Cottages;

use App\Models\Cottage;
use App\Models\CottageAttribute;
use App\Models\CottageImage;
use App\Models\CottageType;
use Illuminate\Database\Seeder;

class AnahawCottageSeeder extends Seeder
{
    private $type = 'Anahaw Cottages';

    public function run(): void
    {
        $this->setAttributes(); // initialize the attributes.
        $cottageType = CottageType::create([
            'description' => 'A spacious retreat with a capacity for 10 guests, ideal for gatherings and relaxation.',
            'type' => $this->type,
            'price' => 500,
            'capacity' => 10,
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
                'name' => 'Good for 10 pax',
                'type' => $this->type,
            ],
        ];

        foreach ($attributes as $attr) {
            CottageAttribute::create($attr);
        }
    }

    // 6
    private function cottageUnits(int $cottageTypeId): void
    {
        $cottageData = [
            [
                'name' => 'Anahaw Cottage 1',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717202344/cottages/anahaw/IMG20240601071900_vhaq9w.jpg',
                        'cottage_id' => $cottageId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717202322/cottages/anahaw/IMG20240601071909_xcbdye.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Anahaw Cottage 2',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717202321/cottages/anahaw/IMG20240601071924_rhyw3b.jpg',
                        'cottage_id' => $cottageId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717202346/cottages/anahaw/IMG20240601071915_mmit8n.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Anahaw Cottage 3',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717202351/cottages/anahaw/IMG20240601071937_afma4a.jpg',
                        'cottage_id' => $cottageId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717202314/cottages/anahaw/IMG20240601071943_lb1rbs.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Anahaw Cottage 4',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717202335/cottages/anahaw/IMG20240601072013_dacvtw.jpg',
                        'cottage_id' => $cottageId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717202328/cottages/anahaw/IMG20240601072019_zhibx2.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Anahaw Cottage 5',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717202343/cottages/anahaw/IMG20240601072038_ivyy5z.jpg',
                        'cottage_id' => $cottageId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717202338/cottages/anahaw/IMG20240601072040_bf5vh5.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Anahaw Cottage 6',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717202329/cottages/anahaw/IMG20240601072027_t4c6gv.jpg',
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

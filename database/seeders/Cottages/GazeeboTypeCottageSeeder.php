<?php

namespace Database\Seeders\Cottages;

use App\Models\Cottage;
use App\Models\CottageAttribute;
use App\Models\CottageImage;
use App\Models\CottageType;
use Illuminate\Database\Seeder;

class GazeeboTypeCottageSeeder extends Seeder
{
    private $type = 'Gazeebo Typed Cottages';

    public function run(): void
    {
        $this->setAttributes(); // initialize the attributes.
        $cottageType = CottageType::create([
            'description' => 'A spacious retreat with a capacity for 6 guests, ideal for gatherings and relaxation.',
            'type' => $this->type,
            'price' => 500,
            'capacity' => 6,
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
                'name' => 'Good for 6 pax',
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
                'name' => 'Gazeebo Typed Cottage 1',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717245861/cottages/gazebo/IMG20240601093715_jp9qb5.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Gazeebo Typed Cottage 2',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717245877/cottages/gazebo/IMG20240601093723_qd01nf.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Gazeebo Typed Cottage 3',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717245882/cottages/gazebo/IMG20240601093848_we56a2.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Gazeebo Typed Cottage 4',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717245874/cottages/gazebo/IMG20240601093811_ti6r3b.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Gazeebo Typed Cottage 5',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717245867/cottages/gazebo/IMG20240601093741_o725so.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Gazeebo Typed Cottage 6',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717245866/cottages/gazebo/IMG20240601093707_r7xivx.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Gazeebo Typed Cottage 7',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717245861/cottages/gazebo/IMG20240601093715_jp9qb5.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Gazeebo Typed Cottage 8',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717245869/cottages/gazebo/IMG20240601093803_ply02b.jpg',
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

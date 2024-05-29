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
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716088997/441349048_367900629597225_4736797796269193821_n_vnhcnr.jpg',
                        'cottage_id' => $cottageId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716088954/441550028_367900606263894_1442755070444327851_n_hdwxji.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Anahaw Cottage 2',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716088997/441349048_367900629597225_4736797796269193821_n_vnhcnr.jpg',
                        'cottage_id' => $cottageId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716088954/441550028_367900606263894_1442755070444327851_n_hdwxji.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Anahaw Cottage 3',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716088997/441349048_367900629597225_4736797796269193821_n_vnhcnr.jpg',
                        'cottage_id' => $cottageId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716088954/441550028_367900606263894_1442755070444327851_n_hdwxji.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Anahaw Cottage 4',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716088997/441349048_367900629597225_4736797796269193821_n_vnhcnr.jpg',
                        'cottage_id' => $cottageId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716088954/441550028_367900606263894_1442755070444327851_n_hdwxji.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Anahaw Cottage 5',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716088997/441349048_367900629597225_4736797796269193821_n_vnhcnr.jpg',
                        'cottage_id' => $cottageId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716088954/441550028_367900606263894_1442755070444327851_n_hdwxji.jpg',
                        'cottage_id' => $cottageId
                    ],
                ]
            ],
            [
                'name' => 'Anahaw Cottage 6',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716088997/441349048_367900629597225_4736797796269193821_n_vnhcnr.jpg',
                        'cottage_id' => $cottageId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716088954/441550028_367900606263894_1442755070444327851_n_hdwxji.jpg',
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

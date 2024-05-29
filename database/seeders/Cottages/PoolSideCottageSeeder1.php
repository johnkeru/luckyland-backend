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
                'name' => 'Poolside Cottage 2',
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
                'name' => 'Poolside Cottage 3',
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
                'name' => 'Poolside Cottage 4',
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
                'name' => 'Poolside Cottage 5',
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
                'name' => 'Poolside Cottage 6',
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
                'name' => 'Poolside Cottage 7',
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
                'name' => 'Poolside Cottage 8',
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
                'name' => 'Poolside Cottage 9',
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
                'name' => 'Poolside Cottage 10',
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
                'name' => 'Poolside Cottage 11',
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
                'name' => 'Poolside Cottage 12',
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

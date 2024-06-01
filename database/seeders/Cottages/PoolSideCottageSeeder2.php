<?php

namespace Database\Seeders\Cottages;

use App\Models\Cottage;
use App\Models\CottageAttribute;
use App\Models\CottageImage;
use App\Models\CottageType;
use Illuminate\Database\Seeder;

class PoolSideCottageSeeder2 extends Seeder
{
    private $type = 'Poolside Cottages 2';

    public function run(): void
    {
        $this->setAttributes(); // initialize the attributes.
        $cottageType = CottageType::create([
            'description' => 'A spacious retreat with a capacity for 20 guests, ideal for gatherings and relaxation.',
            'type' => $this->type,
            'price' => 1000,
            'capacity' => 20,
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
                'name' => 'Good for 20 pax',
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
                'name' => 'Poolside Cottage 6',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717201430/cottages/pool%20side%20cottages%201/IMG20240601071502_akizwi.jpg',
                        'cottage_id' => $cottageId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717201550/cottages/pool%20side%20cottages%201/IMG20240601071514_xyrlqb.jpg',
                        'cottage_id' => $cottageId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717201485/cottages/pool%20side%20cottages%201/IMG20240601071509_d51udi.jpg',
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

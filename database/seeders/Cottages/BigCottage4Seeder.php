<?php

namespace Database\Seeders\Cottages;

use App\Models\Cottage;
use App\Models\CottageAttribute;
use App\Models\CottageImage;
use App\Models\CottageType;
use Illuminate\Database\Seeder;

class BigCottage4Seeder extends Seeder
{
    private $type = 'Big Cottages 4';

    public function run(): void
    {
        $this->setAttributes(); // initialize the attributes.
        $cottageType = CottageType::create([
            'description' => 'A spacious retreat with a capacity for 20 guests, ideal for gatherings and relaxation.',
            'type' => $this->type,
            'price' => 2500,
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
            [
                'name' => 'Free use of videoke',
                'type' => $this->type,
            ]
        ];

        foreach ($attributes as $attr) {
            CottageAttribute::create($attr);
        }
    }

    private function cottageUnits(int $cottageTypeId): void
    {
        $cottageData = [
            [
                'name' => 'Big Cottage 4',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717810567/cottages/Big%20Cottages/BIg%20Cottage%204/IMG20240604071828_asyitx.jpg',
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

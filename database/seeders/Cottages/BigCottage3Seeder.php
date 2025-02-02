<?php

namespace Database\Seeders\Cottages;

use App\Models\Cottage;
use App\Models\CottageAttribute;
use App\Models\CottageImage;
use App\Models\CottageType;
use App\Models\Item;
use Illuminate\Database\Seeder;

class BigCottage3Seeder extends Seeder
{
    private $type = 'Big Cottages 3';

    public function run(): void
    {
        $this->setAttributes(); // initialize the attributes.
        $cottageType = CottageType::create([
            'description' => 'A spacious retreat with a capacity for 40 guests, ideal for gatherings and relaxation.',
            'type' => $this->type,
            'price' => 5000,
            'capacity' => 40,
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
                'name' => 'Good for 40 pax',
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
                'name' => 'Big Cottage 3',
                'cottage_type_id' => $cottageTypeId,
                'images' => fn (int $cottageId) => [
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717810199/cottages/Big%20Cottages/Big%20Cottage%203/IMG20240604071958_dayts8.jpg',
                        'cottage_id' => $cottageId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717810202/cottages/Big%20Cottages/Big%20Cottage%203/IMG20240604072036_cnn4rv.jpg',
                        'cottage_id' => $cottageId
                    ],
                    [
                        'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1717810199/cottages/Big%20Cottages/Big%20Cottage%203/IMG20240604072046_btwrqi.jpg',
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

            // Retrieve all item IDs where associated categories have the name 'Room'
            $itemIds = Item::whereHas('categories', function ($query) {
                $query->where('name', 'Cottage');
            })->pluck('id')->toArray();

            $itemCottage = [];
            foreach ($itemIds as $itemId) {
                $itemCottage[$itemId] = [
                    'quantity' => 1, // the items will deduct once. bcz minimun is 1
                ];
            }
            $cottage->items()->attach($itemCottage); //items
        }
    }
}

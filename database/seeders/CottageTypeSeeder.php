<?php

namespace Database\Seeders;

use App\Models\CottageAttribute;
use App\Models\CottageType;
use Illuminate\Database\Seeder;

class CottageTypeSeeder extends Seeder
{
    public $smallCottage = 'Small Cottages';
    public $bigCottage = 'Big Cottages';

    public function run(): void
    {
        $cottageTypeData = [
            [
                'description' => 'A spacious retreat with a capacity for 20 guests, ideal for gatherings and relaxation.',
                'type' => $this->bigCottage,
                'price' => 3500,
                'capacity' => 20,
            ],
            [
                'description' => "A cozy retreat designed for intimate getaways, accommodating up to 10 guests comfortably.",
                'type' => $this->smallCottage,
                'price' =>  2500,
                'capacity' => 10,
            ]
        ];

        $this->setAttributes(); // initialize the attributes.

        foreach ($cottageTypeData as $cottageType) {
            $cottageType = CottageType::create($cottageType);
            if ($cottageType->type === $this->smallCottage) {
                $attributeIds = CottageAttribute::where('type', $this->smallCottage)->pluck('id');
            } else {
                $attributeIds = CottageAttribute::where('type', $this->bigCottage)->pluck('id');
            }
            $cottageType->attributes()->attach($attributeIds); //attributes
        }
    }


    private function setAttributes()
    {
        $attributes1 = [
            [
                'name' => 'Complimentary Wifi Internet Access',
                'type' => $this->smallCottage,
            ],
        ];
        foreach ($attributes1 as $attr) {
            CottageAttribute::create($attr);
        }

        $attributes2 = [
            [
                'name' => 'Complimentary Wifi Internet Access',
                'type' => $this->bigCottage,
            ],
        ];
        foreach ($attributes2 as $attr) {
            CottageAttribute::create($attr);
        }
    }
}

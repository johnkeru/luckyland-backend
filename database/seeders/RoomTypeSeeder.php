<?php

namespace Database\Seeders;

use App\Models\RoomAttribute;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

class RoomTypeSeeder extends Seeder
{
    public $forFam = 'Family';
    public $forCouple = 'Friends/Couples';

    public function run(): void
    {
        $roomTypeData = [
            [
                'description' => 'Experience the perfect blend of intimacy and camaraderie in our Friends/Couple Room. Designed for pairs or close friends, this room offers a cozy retreat where you can relax and reconnect. Enjoy the comfort of shared moments and the privacy you need for a memorable stay with your favorite companion.',
                'type' => $this->forCouple,
                'price' => 2500,
                'minCapacity' => 4,
                'maxCapacity' => 6,
            ],
            [
                'description' => "The Family Room offers a warm and welcoming space for your loved ones to gather and unwind. Designed with family in mind, this room provides comfortable accommodations and convenient amenities, ensuring a delightful stay for all ages. Create cherished memories together in our cozy Family Room.",
                'type' => $this->forFam,
                'price' =>  3500,
                'minCapacity' => 8,
                'maxCapacity' => 10,
            ]
        ];

        $this->setAttributes(); // initialize the attributes.

        foreach ($roomTypeData as $roomType) {
            $roomType = RoomType::create($roomType);
            if ($roomType->type === $this->forFam) {
                $attributeIds = RoomAttribute::where('type', $this->forFam)->pluck('id');
            } else {
                $attributeIds = RoomAttribute::where('type', $this->forCouple)->pluck('id');
            }
            $roomType->attributes()->attach($attributeIds); //attributes
        }
    }


    private function setAttributes()
    {
        $attributes1 = [
            [
                'name' => '50” LED TV Cable Satellite Television with HD Channels',
                'type' => $this->forFam,
            ],
            [
                'name' => 'Complimentary Wifi Internet Access',
                'type' => $this->forFam,
            ],
            [
                'name' => 'Comfort Room',
                'type' => $this->forFam,
            ],
            [
                'name' => '4 Bed',
                'type' => $this->forFam,
            ],
            [
                'name' => '2 Cabinet',
                'type' => $this->forFam,
            ],
            [
                'name' => 'Extra Bed (+2 capacity)',
                'type' => $this->forFam,
            ]
        ];
        foreach ($attributes1 as $attr) {
            RoomAttribute::create($attr);
        }

        $attributes2 = [
            [
                'name' => '50” LED TV Cable Satellite Television with HD Channels',
                'type' => $this->forCouple,
            ],
            [
                'name' => 'Complimentary Wifi Internet Access',
                'type' => $this->forCouple,
            ],
            [
                'name' => 'Comfort Room',
                'type' => $this->forCouple,
            ],
            [
                'name' => '2 Bed',
                'type' => $this->forCouple,
            ],
            [
                'name' => '1 Cabinet',
                'type' => $this->forCouple,
            ],
            [
                'name' => 'Extra Bed (+2 capacity)',
                'type' => $this->forCouple,
            ]
        ];
        foreach ($attributes2 as $attr) {
            RoomAttribute::create($attr);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Cottage;
use App\Models\CottageImage;
use Illuminate\Database\Seeder;

class CottageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cottageData = [
            [
                'name' => 'Cottage 1',
                'cottage_type_id' => 2
            ],
            [
                'name' => 'Cottage 2',
                'cottage_type_id' => 2
            ],
            [
                'name' => 'Cottage 3',
                'cottage_type_id' => 2
            ],
            [
                'name' => 'Cottage 4',
                'cottage_type_id' => 1
            ],
            [
                'name' => 'Cottage 5',
                'cottage_type_id' => 1
            ],
        ];


        foreach ($cottageData as $data) {
            $cottage = Cottage::create($data);
            $this->setImages($cottage->id);
        }
    }


    private function setImages($cottage_id)
    {
        $images = [
            [
                'url' => 'https://source.unsplash.com/featured/?cottage,' . $cottage_id,
                'cottage_id' => $cottage_id
            ],
            [
                'url' => 'https://source.unsplash.com/featured/?cottage,' . ($cottage_id + 1),
                'cottage_id' => $cottage_id
            ],
            [
                'url' => 'https://source.unsplash.com/featured/?cottage,' . ($cottage_id + 2),
                'cottage_id' => $cottage_id
            ],
        ];


        foreach ($images as $img) {
            CottageImage::create($img);
        }
    }
}

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
                'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716088997/441349048_367900629597225_4736797796269193821_n_vnhcnr.jpg',
                'cottage_id' => $cottage_id
            ],
            [
                'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716088954/441550028_367900606263894_1442755070444327851_n_hdwxji.jpg',
                'cottage_id' => $cottage_id
            ],
            [
                'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716089032/441325075_367900429597245_3761056138771772773_n_ymt5fh.jpg',
                'cottage_id' => $cottage_id
            ],
            [
                'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716089150/444839273_367900832930538_3778265056649699977_n_lcr4jd.jpg',
                'cottage_id' => $cottage_id
            ],
            [
                'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716089142/441327224_367900756263879_2255196024670694207_n_lcjlgu.jpg',
                'cottage_id' => $cottage_id
            ],
            [
                'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716089158/440983858_367901092930512_2515053077184066296_n_ulqfcc.jpg',
                'cottage_id' => $cottage_id
            ],
            [
                'url' => 'https://res.cloudinary.com/kerutman/image/upload/v1716089167/441292585_367901272930494_7623719480856449387_n_ujzwrf.jpg',
                'cottage_id' => $cottage_id
            ],
        ];


        foreach ($images as $img) {
            CottageImage::create($img);
        }
    }
}

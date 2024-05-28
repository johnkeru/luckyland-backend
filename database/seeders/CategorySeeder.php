<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ["name" => "Resort"], // 1
            ["name" => "Room"], // 2
            ["name" => "Cottage"], // 3
            ["name" => "Room Add Ons"], // 4
            ["name" => "Cottage Add Ons"], // 5
            ["name" => "Other"], // 6
            ["name" => "Other Add Ons"], // 7
        ];
        Category::insert($categories);
    }
}

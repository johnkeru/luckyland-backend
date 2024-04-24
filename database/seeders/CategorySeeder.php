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
            ["name" => "Resort"],
            ["name" => "Room"],
            ["name" => "Cottage"],
            ["name" => "Room Add Ons"],
            ["name" => "Cottage Add Ons"],
        ];
        Category::insert($categories);
    }
}

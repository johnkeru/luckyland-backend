<?php

namespace Database\Seeders;

use App\Models\FAQ;
use Illuminate\Database\Seeder;

class FAQSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Generate 20 dummy FAQs
        for ($i = 0; $i < 20; $i++) {
            FAQ::create([
                'question' => fake()->sentence,
                'answer' => fake()->paragraph,
                'email' => fake()->email, // Optionally include email
                'display' => fake()->boolean()
            ]);
        }
    }
}

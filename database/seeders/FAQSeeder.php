<?php

namespace Database\Seeders;

use App\Models\FAQ;
use Illuminate\Database\Seeder;

class FAQSeeder extends Seeder
{
    public function run(): void
    {
        // Define the most commonly asked questions
        $commonQuestions = [
            [
                'question' => 'What payment methods do you accept?',
                'answer' => 'We accept GCash as our primary payment method.',
                'display' => true,
                'email' => fake()->email
            ],
            [
                'question' => 'What is your return policy?',
                'answer' => 'Our return policy allows returns within 30 days of purchase with proof of receipt.',
                'display' => true,
                'email' => fake()->email
            ],
            [
                'question' => 'How do I track my order?',
                'answer' => 'You can track your order by logging into your account or using the tracking number provided in your confirmation email.',
                'display' => true,
                'email' => fake()->email
            ],
            [
                'question' => 'Do you offer international shipping?',
                'answer' => 'Yes, we offer international shipping to most countries. Shipping rates may vary.',
                'display' => true,
                'email' => fake()->email
            ],
            [
                'question' => 'What is your customer support contact information?',
                'answer' => 'You can reach our customer support team at support@example.com or by phone at 1-800-555-1234.',
                'display' => true,
                'email' => fake()->email
            ],
        ];

        // Insert the common questions into the database
        foreach ($commonQuestions as $questionData) {
            FAQ::create($questionData);
        }
    }
}

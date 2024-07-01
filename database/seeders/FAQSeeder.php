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
                'answer' => 'We currently accept GCash as our primary payment method. For walk-in guests, we also accept cash payments.',
                'display' => true,
                'email' => fake()->email
            ],
            [
                'question' => 'What is your customer support contact information?',
                'answer' => 'You can reach our customer support at Luckyland.resort58@gmail.com, FB Page or by phone at (Globe) 0915-6332893 or (Smart) 0968-5290685.',
                'display' => true,
                'email' => fake()->email
            ],
            [
                'question' => 'Pwede po ba ireschedule ang pagbook?',
                'answer' => 'Yes, after you successfully reserve, we will send you an email with information about the reservation details, as well as a link to reschedule.',
                'display' => true,
                'email' => fake()->email
            ],
            [
                'question' => 'Makakakuha ba ako ng refund kung ika-cancel ko ang aking reservation?',
                'answer' => "Thank you for your question. Unfortunately, we do not offer refunds for canceled reservations. However, we would be happy to assist you with rescheduling your stay to a later date that suits you better.",
                'display' => true,
                'email' => fake()->email
            ],
            [
                'question' => 'Has the chlorine issue been resolved in your pools?',
                'answer' => 'We have taken steps to address any previous issues with chlorine levels in our pools. Our maintenance team regularly monitors and adjusts chlorine levels to ensure they are within safe and comfortable limits for our guests. If you have any concerns during your visit, please notify our staff immediately so we can promptly address them.',
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

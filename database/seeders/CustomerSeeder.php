<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Cottage;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customer::factory()->count(50)->create()->each(function ($customer) {
            Address::create([
                'barangay' => fake()->streetName,
                'city' => fake()->city(),
                'province' => fake()->state,
                'customer_id' => $customer->id,
            ]);

            // Generate a random check-out date within the next 1 to 5 months
            $checkOut = Carbon::now()->addMonths(rand(1, 5))->endOfMonth()->subDays(rand(0, Carbon::now()->endOfMonth()->diffInDays()));

            // Generate a random check-in date within the last month up to the generated check-out date
            $checkIn = Carbon::parse($checkOut)->subDays(rand(0, $checkOut->diffInDays()));
            $reservation = Reservation::create([
                'reservationHASH' => uniqid(),
                'checkIn' => $checkIn,
                'checkOut' => $checkOut,
                'total' => fake()->numberBetween(100, 1000),
                'paid' => fake()->numberBetween(0, 1000),
                'balance' => 550, // you may adjust this as needed
                'guests' => fake()->numberBetween(1, 10),

                'isMinimumAccepted' => true,
                'isPaymentWithinDay' => true,
                'isConfirmed' => true,

                'status' => fake()->randomElement(['Approved', 'Cancelled', 'Departed', 'In Resort']),
                'isWalkIn' => fake()->boolean(),
                'gCashRefNumber' => fake()->unique()->bankAccountNumber,
                'gCashRefNumberURL' => fake()->unique()->url,

                'isWalkIn' => fake()->boolean(),

                'totalRoomsPrice' => fake()->numberBetween(50, 500),
                'totalCottagesPrice' => fake()->numberBetween(50, 500),
                'days' => $checkIn->diff($checkOut)->days,
                'accommodationType' => fake()->randomElement(['both', 'rooms', 'cottages']),
                'customer_id' => $customer->id,
                'user_id' => 1,
            ]);

            // Attach random rooms to the reservation
            $rooms = Room::inRandomOrder()->limit(rand(1, 2))->pluck('id')->toArray();
            $reservation->rooms()->attach($rooms);

            // Attach random cottages to the reservation
            $cottages = Cottage::inRandomOrder()->limit(rand(1, 2))->pluck('id')->toArray();
            $reservation->cottages()->attach($cottages);
        });
    }
}

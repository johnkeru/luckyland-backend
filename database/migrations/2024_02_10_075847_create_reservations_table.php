<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reservationHASH')->unique(); // Unique reservation ID sent to customer's email
            $table->dateTime('checkIn'); // Date and time of check-in
            $table->dateTime('checkOut'); // Date and time of check-out

            $table->dateTime('actualCheckIn')->nullable(); // uses for actual checkIn and checkOut
            $table->dateTime('actualCheckOut')->nullable();

            $table->integer('total');
            $table->integer('paid')->default(0);
            $table->integer('balance')->default(0);
            $table->integer('refund')->nullable();
            $table->integer('guests')->min(1);

            $table->boolean('isChecked'); // agree on terms and policy
            $table->boolean('isConfirmed');

            $table->enum('status', ['Approved', 'Cancelled', 'Departed', 'In Resort'])->default('Approved');
            $table->boolean('isWalkIn')->default(false);
            $table->string('gCashRefNumber')->nullable();
            $table->string('gCashRefNumberURL')->unique()->nullable();

            // added for reschedule to track the new prices depends on day/s            
            $table->integer('totalRoomsPrice')->nullable();
            $table->integer('totalCottagesPrice')->nullable();
            $table->integer('totalOthersPrice')->nullable();
            $table->integer('days')->nullable();
            $table->enum('accommodationType', ['all', 'rooms', 'cottages', 'others'])->default('all');

            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            // an employee who manage the reservation instead the customer.
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};



//   "checkIn": "2024-03-15T14:00:00",
//   "checkOut": "2024-03-20T12:00:00"

// "availableFrom": "2024-03-03T06:45:30.776078Z",
// "availableTo": "2124-03-03T06:45:30.776098Z"

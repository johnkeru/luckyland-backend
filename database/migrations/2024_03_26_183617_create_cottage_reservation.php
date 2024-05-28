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
        Schema::create('cottage_reservation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cottage_id')->nullable()->constrained('cottages')->onDelete('set null');
            $table->foreignId('reservation_id')->constrained('reservations')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cottage_reservation');
    }
};

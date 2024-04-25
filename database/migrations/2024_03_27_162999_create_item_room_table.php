<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // to fix the adding bed add ons this must have minQuantity and maxQuantity
    public function up(): void
    {
        Schema::create('item_room', function (Blueprint $table) {
            $table->id();
            $table->integer('minQuantity')->default(1);
            $table->integer('maxQuantity')->default(1);
            $table->integer('needStock')->default(0); // this will only set IF item quantity can't fill the room need anymore.
            $table->boolean('isBed')->default(false); // this will tell if the quantity to remove is min or max
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_room');
    }
};

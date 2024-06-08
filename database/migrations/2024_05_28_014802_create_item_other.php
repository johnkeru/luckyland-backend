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
        Schema::create('item_other', function (Blueprint $table) {
            $table->id();
            $table->integer('quantity')->default(1);
            $table->integer('needStock')->default(0); // this will only set IF item quantity can't fill the room need anymore.
            $table->integer('reservation_id')->nullable();
            $table->foreignId('other_id')->constrained('others')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_other');
    }
};

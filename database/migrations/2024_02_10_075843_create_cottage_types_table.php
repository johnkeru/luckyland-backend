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
        Schema::create('cottage_types', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'Big Cottages', 'Small Cottages'
            $table->decimal('price', 10, 2);
            $table->decimal('rate', 10, 2)->nullable(); // Adding the rate field
            $table->text('description')->nullable();
            $table->integer('capacity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cottage_types');
    }
};

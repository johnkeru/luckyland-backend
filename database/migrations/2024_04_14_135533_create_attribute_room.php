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
        Schema::create('cottage_attribute_cottage_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cottage_attribute_id')->constrained('cottage_attributes');
            $table->foreignId('cottage_type_id')->constrained('cottage_types');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cottage_attribute_cottage_type');
    }
};

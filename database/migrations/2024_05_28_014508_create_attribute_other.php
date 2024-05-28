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
        Schema::create('other_attribute_other_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('other_attribute_id')->constrained('other_attributes');
            $table->foreignId('other_type_id')->constrained('other_types');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('other_attribute_other_type');
    }
};

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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 8, 2);
            $table->text('description')->nullable();
            $table->string('image')->nullable();

            $table->enum('status', ['Low Stock', 'Out of Stock', 'In Stock'])->default('In Stock');
            $table->integer('maxQuantity')->default(0); // Maximum quantity allowed in stock.
            $table->integer('currentQuantity')->default(0);
            $table->integer('reOrderPoint')->default(15); // Minimum quantity for reorder - Notification will comes in.
            $table->timestamp('lastCheck')->default(now());
            $table->boolean('isBorrowable')->default(false);
            $table->boolean('isConsumable')->default(false);

            $table->foreignId('delivery_id')->nullable()->constrained('deliveries')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};

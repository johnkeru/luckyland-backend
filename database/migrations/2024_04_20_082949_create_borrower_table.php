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
        Schema::create('borrows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('item_id')->constrained('items');
            $table->integer('paid')->nullable();
            $table->integer('borrowed_quantity');
            $table->integer('return_quantity')->default(0);
            $table->enum('status', ['Borrowed', 'Returned', 'Paid'])->nullable();
            $table->timestamp('borrowed_at')->nullable();
            $table->timestamp('returned_at')->nullable();
        });

        // Schema::create('borrows', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
        //     $table->foreignId('inventory_id')->constrained('inventories')->onDelete('cascade');
        //     $table->string('name');
        //     $table->integer('borrowed_quantity');
        //     $table->enum('status', ['Borrowed', 'Partial-Returned', 'Returned', 'Overdue'])->nullable();
        //     $table->timestamp('borrowed_at')->nullable();
        //     $table->timestamp('returned_at')->nullable();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrows');
    }
};

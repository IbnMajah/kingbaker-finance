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
        Schema::create('bills_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_id');
            $table->string('description');
            $table->decimal('unit_price', 12, 2);
            $table->string('unit_measurement', 50)->nullable();
            $table->integer('quantity');
            $table->decimal('total', 12, 2); // Calculated field: unit_price * quantity
            $table->timestamps();

            $table->foreign('bill_id')->references('id')->on('bills')->onDelete('cascade');
            $table->index(['bill_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills_items');
    }
};

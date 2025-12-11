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
        Schema::create('sales_credit_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->unsignedInteger('customer_id'); // References contacts table (uses increments, not bigIncrements)
            $table->string('description');
            $table->decimal('unit_price', 12, 2);
            $table->string('unit_measurement', 50)->nullable();
            $table->decimal('quantity', 12, 2);
            $table->decimal('total', 12, 2); // Calculated field: unit_price * quantity
            $table->timestamps();

            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->index(['sale_id']);
            $table->index(['customer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_credit_items');
    }
};

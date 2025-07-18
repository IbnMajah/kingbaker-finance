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
        Schema::create('expense_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expense_claim_id');
            $table->date('expense_date');
            $table->string('title');
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->string('category')->nullable(); // e.g. 'transport', 'meals', 'supplies', etc.
            $table->string('receipt_image_path')->nullable(); // For receipt image
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('expense_claim_id')->references('id')->on('expense_claims')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_items');
    }
};
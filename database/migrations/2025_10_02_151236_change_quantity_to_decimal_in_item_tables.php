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
        // Change quantity from integer to decimal in bills_items table
        Schema::table('bills_items', function (Blueprint $table) {
            $table->decimal('quantity', 10, 2)->change();
        });

        // Change quantity from integer to decimal in invoice_items table
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->decimal('quantity', 10, 2)->change();
        });

        // Change quantity from unsignedInteger to decimal in expense_items table
        Schema::table('expense_items', function (Blueprint $table) {
            $table->decimal('quantity', 10, 2)->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert quantity back to integer in bills_items table
        Schema::table('bills_items', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });

        // Revert quantity back to integer in invoice_items table
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });

        // Revert quantity back to unsignedInteger in expense_items table
        Schema::table('expense_items', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->default(1)->change();
        });
    }
};

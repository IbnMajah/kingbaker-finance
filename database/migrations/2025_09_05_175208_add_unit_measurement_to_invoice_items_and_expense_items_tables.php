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
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('unit_measurement', 50)->nullable()->after('unit_price');
        });

        Schema::table('expense_items', function (Blueprint $table) {
            $table->string('unit_measurement', 50)->nullable()->after('unit_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('unit_measurement');
        });

        Schema::table('expense_items', function (Blueprint $table) {
            $table->dropColumn('unit_measurement');
        });
    }
};

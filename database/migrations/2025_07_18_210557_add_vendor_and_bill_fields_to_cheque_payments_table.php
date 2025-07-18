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
        Schema::table('cheque_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('vendor_id')->nullable()->after('branch_id');
            $table->unsignedBigInteger('bill_id')->nullable()->after('vendor_id');
            $table->enum('recurring_frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'annually'])->nullable()->after('bill_id');

            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('set null');
            $table->foreign('bill_id')->references('id')->on('bills')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cheque_payments', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropForeign(['bill_id']);
            $table->dropColumn(['vendor_id', 'bill_id', 'recurring_frequency']);
        });
    }
};

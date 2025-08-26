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
        // Add new fields to expense_items table
        Schema::table('expense_items', function (Blueprint $table) {
            $table->string('category')->nullable()->after('description');
            $table->string('receipt_image_path')->nullable()->after('category');
        });

        // Add bank_account_id to expense_claims and remove category and receipt_image_path
        Schema::table('expense_claims', function (Blueprint $table) {
            $table->unsignedBigInteger('bank_account_id')->nullable()->after('branch_id');
            $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->onDelete('set null');
        });

        // Remove category and receipt_image_path from expense_claims table
        Schema::table('expense_claims', function (Blueprint $table) {
            $table->dropColumn(['category', 'receipt_image_path']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back category and receipt_image_path to expense_claims
        Schema::table('expense_claims', function (Blueprint $table) {
            $table->string('category')->nullable()->after('title');
            $table->string('receipt_image_path')->nullable()->after('category');
        });

        // Remove bank_account_id from expense_claims
        Schema::table('expense_claims', function (Blueprint $table) {
            $table->dropForeign(['bank_account_id']);
            $table->dropColumn('bank_account_id');
        });

        // Remove category and receipt_image_path from expense_items
        Schema::table('expense_items', function (Blueprint $table) {
            $table->dropColumn(['category', 'receipt_image_path']);
        });
    }
};

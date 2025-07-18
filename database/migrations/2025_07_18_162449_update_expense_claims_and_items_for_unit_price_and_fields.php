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
        // Expense Claims table changes
        Schema::table('expense_claims', function (Blueprint $table) {
            if (Schema::hasColumn('expense_claims', 'total_amount')) {
                $table->renameColumn('total_amount', 'total');
            }
            if (Schema::hasColumn('expense_claims', 'bank_account_id')) {
                $table->dropForeign(['bank_account_id']);
                $table->dropColumn('bank_account_id');
            }
            if (!Schema::hasColumn('expense_claims', 'title')) {
                $table->string('title')->after('claim_date');
            }
            if (!Schema::hasColumn('expense_claims', 'category')) {
                $table->string('category')->after('title');
            }
            if (!Schema::hasColumn('expense_claims', 'receipt_image_path')) {
                $table->string('receipt_image_path')->nullable()->after('category');
            }
        });

        // Expense Items table changes
        Schema::table('expense_items', function (Blueprint $table) {
            if (Schema::hasColumn('expense_items', 'amount')) {
                $table->dropColumn('amount');
            }
            if (!Schema::hasColumn('expense_items', 'unit_price')) {
                $table->decimal('unit_price', 12, 2)->after('description');
            }
            if (Schema::hasColumn('expense_items', 'expense_date')) {
                $table->dropColumn('expense_date');
            }
            if (Schema::hasColumn('expense_items', 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn('expense_items', 'category')) {
                $table->dropColumn('category');
            }
            if (Schema::hasColumn('expense_items', 'receipt_image_path')) {
                $table->dropColumn('receipt_image_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Expense Claims table rollback
        Schema::table('expense_claims', function (Blueprint $table) {
            if (Schema::hasColumn('expense_claims', 'total')) {
                $table->renameColumn('total', 'total_amount');
            }
            if (!Schema::hasColumn('expense_claims', 'bank_account_id')) {
                $table->unsignedBigInteger('bank_account_id')->nullable();
            }
            if (Schema::hasColumn('expense_claims', 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn('expense_claims', 'category')) {
                $table->dropColumn('category');
            }
            if (Schema::hasColumn('expense_claims', 'receipt_image_path')) {
                $table->dropColumn('receipt_image_path');
            }
        });

        // Expense Items table rollback
        Schema::table('expense_items', function (Blueprint $table) {
            if (!Schema::hasColumn('expense_items', 'amount')) {
                $table->decimal('amount', 12, 2)->after('description');
            }
            if (Schema::hasColumn('expense_items', 'unit_price')) {
                $table->dropColumn('unit_price');
            }
            if (!Schema::hasColumn('expense_items', 'expense_date')) {
                $table->date('expense_date')->nullable()->after('expense_claim_id');
            }
            if (!Schema::hasColumn('expense_items', 'title')) {
                $table->string('title')->nullable()->after('expense_date');
            }
            if (!Schema::hasColumn('expense_items', 'category')) {
                $table->string('category')->nullable()->after('quantity');
            }
            if (!Schema::hasColumn('expense_items', 'receipt_image_path')) {
                $table->string('receipt_image_path')->nullable()->after('category');
            }
        });
    }
};

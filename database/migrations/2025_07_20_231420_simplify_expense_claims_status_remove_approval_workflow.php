<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First update any existing claims with approval statuses to 'active'
        DB::table('expense_claims')
            ->whereIn('status', ['submitted', 'approved', 'paid'])
            ->update(['status' => 'active']);

        DB::table('expense_claims')
            ->where('status', 'rejected')
            ->update(['status' => 'cancelled']);

        // Modify the status enum to only include: draft, active, cancelled
        DB::statement("ALTER TABLE expense_claims MODIFY COLUMN status ENUM('draft', 'active', 'cancelled') DEFAULT 'active'");

        // Remove approval-related columns
        Schema::table('expense_claims', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn('approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the approved_by column
        Schema::table('expense_claims', function (Blueprint $table) {
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users');
        });

        // Restore the original status enum
        DB::statement("ALTER TABLE expense_claims MODIFY COLUMN status ENUM('draft', 'submitted', 'approved', 'rejected', 'paid') DEFAULT 'draft'");

        // Update statuses back
        DB::table('expense_claims')
            ->where('status', 'active')
            ->update(['status' => 'approved']);

        DB::table('expense_claims')
            ->where('status', 'cancelled')
            ->update(['status' => 'rejected']);
    }
};

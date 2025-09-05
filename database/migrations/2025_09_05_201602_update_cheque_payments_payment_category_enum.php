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
        // Update the ENUM values for payment_category
        DB::statement("ALTER TABLE cheque_payments MODIFY COLUMN payment_category ENUM('vendor_payment','bill','staff_advance','loan_payment','institutional_payment','other_payment') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to the original ENUM values
        DB::statement("ALTER TABLE cheque_payments MODIFY COLUMN payment_category ENUM('vendor_payment','recurring_bill','staff_advance','loan_payment','institutional_payment','other_payment') NOT NULL");
    }
};

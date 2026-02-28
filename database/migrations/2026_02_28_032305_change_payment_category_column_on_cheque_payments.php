<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE cheque_payments MODIFY COLUMN payment_category VARCHAR(255) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE cheque_payments MODIFY COLUMN payment_category ENUM('vendor_payment','bill','staff_advance','loan_payment','institutional_payment','other_payment') NOT NULL");
    }
};

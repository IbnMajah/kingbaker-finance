<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->modifyPaymentCategory("VARCHAR(255) NOT NULL");
    }

    public function down(): void
    {
        $this->modifyPaymentCategory("ENUM('vendor_payment','bill','staff_advance','loan_payment','institutional_payment','other_payment') NOT NULL");
    }

    private function modifyPaymentCategory(string $definition): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE cheque_payments MODIFY COLUMN payment_category {$definition}");
    }
};

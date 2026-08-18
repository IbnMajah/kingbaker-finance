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
        $this->modifyPaymentCategory("ENUM('vendor_payment','bill','staff_advance','loan_payment','institutional_payment','other_payment') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->modifyPaymentCategory("ENUM('vendor_payment','recurring_bill','staff_advance','loan_payment','institutional_payment','other_payment') NOT NULL");
    }

    private function modifyPaymentCategory(string $definition): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE cheque_payments MODIFY COLUMN payment_category {$definition}");
    }
};

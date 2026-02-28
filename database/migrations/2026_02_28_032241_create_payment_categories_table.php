<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_categories', function (Blueprint $table) {
            $table->id();
            $table->string('value')->unique();
            $table->string('label');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('payment_categories')->insert([
            ['value' => 'vendor_payment', 'label' => 'Vendor Payment', 'created_at' => now(), 'updated_at' => now()],
            ['value' => 'bill', 'label' => 'Bill', 'created_at' => now(), 'updated_at' => now()],
            ['value' => 'staff_advance', 'label' => 'Staff Advance', 'created_at' => now(), 'updated_at' => now()],
            ['value' => 'loan_payment', 'label' => 'Loan Payment', 'created_at' => now(), 'updated_at' => now()],
            ['value' => 'institutional_payment', 'label' => 'Institutional Payment', 'created_at' => now(), 'updated_at' => now()],
            ['value' => 'other_payment', 'label' => 'Other Payment', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_categories');
    }
};

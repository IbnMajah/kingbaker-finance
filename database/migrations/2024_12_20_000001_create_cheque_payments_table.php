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
        Schema::create('cheque_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->string('payee');
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->enum('payment_category', [
                'vendor_payment',
                'recurring_bill',
                'staff_advance',
                'loan_payment',
                'institutional_payment',
                'other_payment'
            ]);
            $table->enum('payment_mode', ['cheque', 'bank_transfer', 'cash', 'nafa', 'wave', 'other']);
            $table->unsignedBigInteger('bank_account_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('cheque_number')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('description');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'issued', 'cleared', 'cancelled'])->default('pending');
            $table->string('receipt_image_path')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['payment_date', 'status']);
            $table->index(['bank_account_id', 'payment_date']);
            $table->index(['payment_category', 'payment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cheque_payments');
    }
};
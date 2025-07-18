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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bank_account_id');
            $table->date('transaction_date');
            $table->enum('type', ['credit', 'debit']);
            $table->enum('payment_mode', ['cash', 'cheque', 'bank_transfer', 'nafa', 'wave', 'sales', 'other']);
            $table->string('reference_number')->nullable(); // Cheque number or reference ID
            $table->string('payee')->nullable(); // Who was paid or who paid
            $table->decimal('amount', 12, 2);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('shift_id')->nullable();
            $table->string('category')->nullable(); // e.g. 'petty_cash', 'vendor_bill', 'recurring_bill', 'miscellaneous'
            $table->string('image_path')->nullable(); // For deposit slips or receipts
            $table->unsignedInteger('created_by')->nullable();
            $table->boolean('is_reconciled')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
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
        Schema::create('expense_claims', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id'); // Person who made the expense
            $table->string('reference_id')->unique(); // Unique ID for the expense claim
            $table->date('claim_date');
            $table->decimal('total_amount', 12, 2);
            $table->string('payee'); // Added payee field
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'paid'])->default('draft');
            $table->enum('expense_type', ['petty_cash', 'cash_on_hand', 'other'])->default('petty_cash');
            $table->unsignedBigInteger('bank_account_id'); // Which account to debit
            $table->unsignedBigInteger('branch_id')->nullable(); // Optional branch association
            $table->text('notes')->nullable();
            $table->unsignedInteger('approved_by')->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable(); // If paid
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('set null');
            $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_claims');
    }
};
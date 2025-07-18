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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('customer_address')->nullable();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->decimal('amount', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->enum('status', ['draft', 'sent', 'paid', 'partially_paid', 'overdue', 'cancelled'])->default('draft');
            $table->enum('invoice_type', ['bulk_sales', 'credit_customer', 'loans', 'daily_supply', 'partner_billing', 'other'])->default('bulk_sales');
            $table->enum('customer_type', ['individual', 'shop', 'partner', 'branch', 'hotel', 'other'])->default('individual');
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('bank_account_id'); // Which account should be credited
            $table->unsignedBigInteger('branch_id')->nullable(); // Source branch
            $table->string('billing_period')->nullable(); // For monthly billing (e.g., "2024-01")
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_frequency')->nullable(); // monthly, quarterly, annually
            $table->date('next_invoice_date')->nullable();
            $table->string('attachment_path')->nullable(); // For invoice attachments
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashier_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('sale_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('hr_cashier_id')->constrained()->restrictOnDelete();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->foreignId('shift_id')->constrained()->restrictOnDelete();
            $table->string('cashier_employee_id', 50);
            $table->string('cashier_name');
            $table->string('branch_name');
            $table->string('shift_name');
            $table->string('till_number', 100)->nullable();
            $table->string('pos_reference', 100)->nullable();
            $table->date('business_date');
            $table->dateTime('closed_at');
            $table->decimal('expected_total', 14, 2);
            $table->decimal('actual_total', 14, 2);
            $table->decimal('calculated_variance', 14, 2);
            $table->string('classification', 20)->index();
            $table->string('receipt_image_path')->nullable();
            $table->text('comments')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('verified_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedInteger('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('rejected_by')->references('id')->on('users')->nullOnDelete();
            $table->unique('sale_id');
            $table->index(['cashier_employee_id', 'business_date']);
        });

        Schema::create('cashier_variance_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('cashier_reconciliation_id')->nullable();
            $table->foreignId('hr_cashier_id')->constrained()->restrictOnDelete();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->foreignId('shift_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('reverses_entry_id')->nullable();
            $table->string('cashier_employee_id', 50);
            $table->string('cashier_name');
            $table->string('branch_name');
            $table->string('shift_name');
            $table->date('entry_date');
            $table->string('entry_type', 20)->index();
            $table->decimal('variance_amount', 14, 2);
            $table->text('reason')->nullable();
            $table->unsignedInteger('created_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('cashier_reconciliation_id', 'cv_ledger_reconciliation_fk')
                ->references('id')->on('cashier_reconciliations')->restrictOnDelete();
            $table->foreign('reverses_entry_id', 'cv_ledger_reversal_fk')
                ->references('id')->on('cashier_variance_ledger_entries')->restrictOnDelete();
            $table->index(['cashier_employee_id', 'entry_date'], 'cv_ledger_cashier_date_idx');
        });

        Schema::create('cashier_variance_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cashier_reconciliation_id');
            $table->string('event_type', 30)->index();
            $table->unsignedInteger('actor_id');
            $table->text('reason')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('actor_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('cashier_reconciliation_id', 'cv_events_reconciliation_fk')
                ->references('id')->on('cashier_reconciliations')->cascadeOnDelete();
        });

        Schema::create('cashier_variance_outbox', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_uuid')->unique();
            $table->unsignedBigInteger('cashier_reconciliation_id');
            $table->string('event_type', 30);
            $table->json('payload');
            $table->string('status', 20)->default('pending')->index();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('available_at')->nullable()->index();
            $table->timestamp('processed_at')->nullable();
            $table->text('last_error')->nullable();
            $table->string('external_reference')->nullable();
            $table->timestamps();

            $table->unique(
                ['cashier_reconciliation_id', 'event_type'],
                'cashier_variance_outbox_event_unique'
            );
            $table->foreign('cashier_reconciliation_id', 'cv_outbox_reconciliation_fk')
                ->references('id')->on('cashier_reconciliations')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashier_variance_outbox');
        Schema::dropIfExists('cashier_variance_events');
        Schema::dropIfExists('cashier_variance_ledger_entries');
        Schema::dropIfExists('cashier_reconciliations');
    }
};

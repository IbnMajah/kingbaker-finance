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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('bill_number')->nullable();
            $table->date('bill_date');
            $table->date('due_date')->nullable();
            $table->decimal('amount', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->enum('status', ['draft', 'pending', 'paid', 'partially_paid', 'overdue', 'cancelled'])->default('pending');
            $table->enum('bill_type', ['inventory', 'utility', 'service', 'recurring', 'miscellaneous'])->default('miscellaneous');
            $table->text('description')->nullable();
            $table->string('recurring_frequency')->nullable(); // monthly, quarterly, annually
            $table->date('next_due_date')->nullable(); // For recurring bills
            $table->string('image_path')->nullable(); // For bill/invoice image
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
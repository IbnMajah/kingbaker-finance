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
        Schema::table('cheque_payments', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(false)->after('bill_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cheque_payments', function (Blueprint $table) {
            $table->dropColumn('is_recurring');
        });
    }
};

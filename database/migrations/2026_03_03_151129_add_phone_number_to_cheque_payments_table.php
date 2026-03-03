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
            $table->string('phone_number')->nullable()->after('payee');
        });
    }

    public function down(): void
    {
        Schema::table('cheque_payments', function (Blueprint $table) {
            $table->dropColumn('phone_number');
        });
    }
};

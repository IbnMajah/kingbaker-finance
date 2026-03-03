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
        Schema::table('payment_categories', function (Blueprint $table) {
            $table->string('description')->default('')->after('label');
            $table->string('description_placeholder')->default('')->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('payment_categories', function (Blueprint $table) {
            $table->dropColumn(['description', 'description_placeholder']);
        });
    }
};

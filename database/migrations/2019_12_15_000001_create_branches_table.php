<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('location', 200);
            $table->string('phone', 20)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('manager_name', 100)->nullable();
            $table->text('description')->nullable();
            $table->decimal('daily_sales_target', 12, 2)->nullable();
            $table->decimal('monthly_sales_target', 12, 2)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('branches');
    }
};
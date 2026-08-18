<?php

use App\Support\SqliteSchema;
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
        $columns = ['organization_id', 'city', 'region', 'country', 'postal_code'];

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            SqliteSchema::dropColumns('contacts', $columns);

            return;
        }

        Schema::table('contacts', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->integer('organization_id')->nullable()->index();
            $table->string('city', 50)->nullable();
            $table->string('region', 50)->nullable();
            $table->string('country', 2)->nullable();
            $table->string('postal_code', 25)->nullable();
        });
    }
};

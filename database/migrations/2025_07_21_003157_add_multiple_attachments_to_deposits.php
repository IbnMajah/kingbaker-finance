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
        Schema::table('deposits', function (Blueprint $table) {
            // Add new column for multiple attachments
            $table->json('attachments')->nullable()->after('image_path');

            // Keep the old image_path column for backward compatibility during transition
            // We'll migrate existing data and then drop it in a separate migration if needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropColumn('attachments');
        });
    }
};

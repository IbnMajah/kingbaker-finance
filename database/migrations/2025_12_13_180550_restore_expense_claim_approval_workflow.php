<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, temporarily modify the column to allow all values
        DB::statement("ALTER TABLE expense_claims MODIFY COLUMN status VARCHAR(20) DEFAULT 'draft'");

        // Update any existing 'active' claims to 'submitted' for approval
        DB::table('expense_claims')
            ->where('status', 'active')
            ->update(['status' => 'submitted']);

        // Add back the approved_by column if it doesn't exist
        // Note: Must use unsignedInteger to match users.id type (int unsigned)
        if (!Schema::hasColumn('expense_claims', 'approved_by')) {
            Schema::table('expense_claims', function (Blueprint $table) {
                $table->unsignedInteger('approved_by')->nullable()->after('status');
            });
        } else {
            // If column exists, check if it needs to be modified to match users.id type
            $columnInfo = DB::selectOne("
                SELECT DATA_TYPE, COLUMN_TYPE
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = ?
                AND TABLE_NAME = 'expense_claims'
                AND COLUMN_NAME = 'approved_by'
            ", [DB::connection()->getDatabaseName()]);

            if ($columnInfo && strpos($columnInfo->COLUMN_TYPE, 'bigint') !== false) {
                // Column exists but is bigint, need to change to int to match users.id
                DB::statement("ALTER TABLE expense_claims MODIFY COLUMN approved_by INT UNSIGNED NULL");
            }
        }

        // Add foreign key constraint if it doesn't exist
        // Use raw SQL to check and add foreign key safely
        $dbName = DB::connection()->getDatabaseName();
        $fkExists = DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
            AND TABLE_NAME = 'expense_claims'
            AND COLUMN_NAME = 'approved_by'
            AND REFERENCED_TABLE_NAME = 'users'
            LIMIT 1
        ", [$dbName]);

        if (!$fkExists) {
            // Try to add the foreign key, ignore if it already exists
            try {
                DB::statement("
                    ALTER TABLE expense_claims
                    ADD CONSTRAINT expense_claims_approved_by_foreign
                    FOREIGN KEY (approved_by) REFERENCES users(id)
                    ON DELETE SET NULL
                ");
            } catch (\Exception $e) {
                // Foreign key might already exist with different name, try to find and use it
                // or just continue if it fails
                if (
                    strpos($e->getMessage(), 'Duplicate foreign key') === false &&
                    strpos($e->getMessage(), 'already exists') === false
                ) {
                    throw $e;
                }
            }
        }

        // Now modify the status enum to include: draft, submitted, approved, rejected, paid
        DB::statement("ALTER TABLE expense_claims MODIFY COLUMN status ENUM('draft', 'submitted', 'approved', 'rejected', 'paid') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Find and drop the foreign key constraint if it exists
        $dbName = DB::connection()->getDatabaseName();
        $fk = DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
            AND TABLE_NAME = 'expense_claims'
            AND COLUMN_NAME = 'approved_by'
            AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ", [$dbName]);

        if ($fk && isset($fk->CONSTRAINT_NAME)) {
            try {
                DB::statement("ALTER TABLE expense_claims DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
            } catch (\Exception $e) {
                // Try alternative method
                Schema::table('expense_claims', function (Blueprint $table) use ($fk) {
                    $table->dropForeign([$fk->CONSTRAINT_NAME]);
                });
            }
        }

        // Remove approved_by column if it exists
        if (Schema::hasColumn('expense_claims', 'approved_by')) {
            Schema::table('expense_claims', function (Blueprint $table) {
                $table->dropColumn('approved_by');
            });
        }

        // Change status enum back to: draft, active, cancelled
        DB::statement("ALTER TABLE expense_claims MODIFY COLUMN status VARCHAR(20) DEFAULT 'draft'");

        DB::table('expense_claims')
            ->whereIn('status', ['submitted', 'approved', 'paid'])
            ->update(['status' => 'active']);

        DB::table('expense_claims')
            ->where('status', 'rejected')
            ->update(['status' => 'cancelled']);

        DB::statement("ALTER TABLE expense_claims MODIFY COLUMN status ENUM('draft', 'active', 'cancelled') DEFAULT 'active'");
    }
};

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WipeDatabase extends Command
{
    protected $signature = 'db:wipe-force {--force : Really, really wipe everything}';
    protected $description = 'Truncate all tables in the database';

    public function handle()
    {
        if (! $this->option('force')) {
            $this->error('Add --force to confirm you want to WIPE everything.');
            return;
        }

        Schema::disableForeignKeyConstraints();

        $tables = DB::select('SHOW TABLES');
        $key = 'Tables_in_' . DB::getDatabaseName();

        foreach ($tables as $table) {
            $tableName = $table->$key;
            DB::table($tableName)->truncate();
            $this->line("Truncated: {$tableName}");
        }

        Schema::enableForeignKeyConstraints();

        $this->info('Database wiped clean. Nothing left, just echoes of your mistakes.');
    }
}

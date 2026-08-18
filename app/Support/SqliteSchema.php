<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SqliteSchema
{
    /**
     * Drop columns on SQLite by rebuilding the table.
     * Native ALTER TABLE DROP COLUMN fails when the column is used by a
     * foreign key or index.
     *
     * @param  list<string>  $columns
     */
    public static function dropColumns(string $table, array $columns): void
    {
        Schema::disableForeignKeyConstraints();

        $info = DB::select("PRAGMA table_info(\"{$table}\")");
        $keep = array_values(array_filter(
            $info,
            fn (object $column) => ! in_array($column->name, $columns, true)
        ));

        $definitions = [];
        foreach ($keep as $column) {
            $definition = '"'.$column->name.'" '.$column->type;

            if ((int) $column->pk === 1 && strcasecmp($column->type, 'INTEGER') === 0) {
                $definition .= ' PRIMARY KEY AUTOINCREMENT';
            } else {
                if ((int) $column->notnull === 1) {
                    $definition .= ' NOT NULL';
                }

                if ($column->dflt_value !== null) {
                    $definition .= ' DEFAULT '.$column->dflt_value;
                }
            }

            $definitions[] = $definition;
        }

        $quoted = implode(', ', array_map(fn (object $column) => '"'.$column->name.'"', $keep));
        $temp = $table.'_tmp_dropcol';

        DB::statement("DROP TABLE IF EXISTS \"{$temp}\"");
        DB::statement('CREATE TABLE "'.$temp.'" ('.implode(', ', $definitions).')');
        DB::statement("INSERT INTO \"{$temp}\" ({$quoted}) SELECT {$quoted} FROM \"{$table}\"");
        DB::statement("DROP TABLE \"{$table}\"");
        DB::statement("ALTER TABLE \"{$temp}\" RENAME TO \"{$table}\"");

        Schema::enableForeignKeyConstraints();
    }
}

<?php

namespace App\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IndexValidator
{
    public function validateForeignKeys(string $table): array
    {
        $foreignKeys = $this->getForeignKeys($table);
        $indexes = $this->getIndexes($table);
        $violations = [];

        foreach ($foreignKeys as $foreignKey) {
            $column = $foreignKey['column'];
            if (!$this->hasIndexForColumn($indexes, $column)) {
                $violations[] = "Foreign key column '{$column}' in table '{$table}' lacks an index";
            }
        }

        return $violations;
    }

    public function validateQueryColumns(string $table, array $columns): array
    {
        $indexes = $this->getIndexes($table);
        $violations = [];

        foreach ($columns as $column) {
            if (!$this->hasIndexForColumn($indexes, $column)) {
                $violations[] = "Frequently queried column '{$column}' in table '{$table}' lacks an index";
            }
        }

        return $violations;
    }

    protected function getForeignKeys(string $table): array
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        if ($connection->getDriverName() === 'sqlite') {
            $foreignKeys = DB::select("PRAGMA foreign_key_list({$table})");
            return array_map(function ($fk) {
                return ['column' => $fk->from];
            }, $foreignKeys);
        }

        if ($connection->getDriverName() === 'mysql') {
            $query = "
                SELECT COLUMN_NAME as column_name
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = ? 
                AND TABLE_NAME = ?
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ";
            $results = DB::select($query, [$database, $table]);
            return array_map(function ($row) {
                return ['column' => $row->column_name];
            }, $results);
        }

        return [];
    }

    protected function getIndexes(string $table): array
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        if ($connection->getDriverName() === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list({$table})");
            $result = [];
            foreach ($indexes as $index) {
                $indexInfo = DB::select("PRAGMA index_info({$index->name})");
                foreach ($indexInfo as $info) {
                    $result[] = [
                        'name' => $index->name,
                        'column' => $info->name,
                    ];
                }
            }
            return $result;
        }

        if ($connection->getDriverName() === 'mysql') {
            $query = "
                SELECT INDEX_NAME as name, COLUMN_NAME as column_name
                FROM INFORMATION_SCHEMA.STATISTICS
                WHERE TABLE_SCHEMA = ? 
                AND TABLE_NAME = ?
            ";
            $results = DB::select($query, [$database, $table]);
            return array_map(function ($row) {
                return ['name' => $row->name, 'column' => $row->column_name];
            }, $results);
        }

        return [];
    }

    protected function hasIndexForColumn(array $indexes, string $column): bool
    {
        foreach ($indexes as $index) {
            if ($index['column'] === $column) {
                return true;
            }
        }
        return false;
    }
}

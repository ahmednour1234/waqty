<?php

namespace App\Database\Migrations\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait RequiresIndexes
{
    protected function validateIndexes(string $table): void
    {
        $foreignKeys = $this->getForeignKeys($table);
        $indexes = $this->getIndexes($table);

        foreach ($foreignKeys as $foreignKey) {
            $column = $foreignKey['column'];
            $indexName = $this->getIndexName($table, $column);

            if (!$this->hasIndex($indexes, $indexName, $column)) {
                throw new \RuntimeException(
                    "Foreign key column '{$column}' in table '{$table}' must have an index. " .
                    "Add: \$table->index('{$column}');"
                );
            }
        }
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

    protected function hasIndex(array $indexes, string $indexName, string $column): bool
    {
        foreach ($indexes as $index) {
            if (($index['name'] === $indexName || str_contains($index['name'], $column)) &&
                $index['column'] === $column) {
                return true;
            }
        }
        return false;
    }

    protected function getIndexName(string $table, string $column): string
    {
        return "{$table}_{$column}_index";
    }
}

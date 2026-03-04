<?php

namespace App\Database\Migrations\Concerns;

use Illuminate\Database\Schema\Blueprint;

trait PerformanceValidation
{
    protected function validateQueryColumns(string $table, array $columns): void
    {
        foreach ($columns as $column) {
            if (is_array($column)) {
                $this->validateCompositeIndex($table, $column);
            } else {
                $this->validateSingleColumnIndex($table, $column);
            }
        }
    }

    protected function validateSingleColumnIndex(string $table, string $column): void
    {
        $indexes = $this->getIndexes($table);
        $hasIndex = false;

        foreach ($indexes as $index) {
            if ($index['column'] === $column) {
                $hasIndex = true;
                break;
            }
        }

        if (!$hasIndex) {
            throw new \RuntimeException(
                "Column '{$column}' in table '{$table}' is frequently queried but lacks an index. " .
                "Add: \$table->index('{$column}');"
            );
        }
    }

    protected function validateCompositeIndex(string $table, array $columns): void
    {
        $indexes = $this->getIndexes($table);
        $columnString = implode('_', $columns);
        $hasCompositeIndex = false;

        foreach ($indexes as $index) {
            if (str_contains($index['name'], $columnString) || 
                count(array_intersect($columns, [$index['column']])) > 0) {
                $matchingIndexes = array_filter($indexes, function ($idx) use ($index, $columns) {
                    return $idx['name'] === $index['name'] && in_array($idx['column'], $columns);
                });

                if (count($matchingIndexes) === count($columns)) {
                    $hasCompositeIndex = true;
                    break;
                }
            }
        }

        if (!$hasCompositeIndex) {
            $columnsList = implode("', '", $columns);
            throw new \RuntimeException(
                "Columns ['{$columnsList}'] in table '{$table}' are queried together but lack a composite index. " .
                "Add: \$table->index(['" . implode("', '", $columns) . "']);"
            );
        }
    }

    protected function getIndexes(string $table): array
    {
        $connection = \Illuminate\Support\Facades\Schema::getConnection();
        $database = $connection->getDatabaseName();

        if ($connection->getDriverName() === 'sqlite') {
            $indexes = \Illuminate\Support\Facades\DB::select("PRAGMA index_list({$table})");
            $result = [];
            foreach ($indexes as $index) {
                $indexInfo = \Illuminate\Support\Facades\DB::select("PRAGMA index_info({$index->name})");
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
            $results = \Illuminate\Support\Facades\DB::select($query, [$database, $table]);
            return array_map(function ($row) {
                return ['name' => $row->name, 'column' => $row->column_name];
            }, $results);
        }

        return [];
    }
}

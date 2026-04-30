<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Arr;

trait InteractsWithReportExports
{
    /**
     * Normalize requested export columns against the allowed set.
     *
     * @param  array<int, string>|string|null  $columns
     * @param  array<string, mixed>  $availableColumns
     * @param  array<int, string>|null  $defaultColumns
     * @return array<int, string>
     */
    protected function normalizeSelectedColumns(array|string|null $columns, array $availableColumns, ?array $defaultColumns = null): array
    {
        $selected = collect(Arr::wrap($columns))
            ->filter(fn ($column) => is_string($column) && array_key_exists($column, $availableColumns))
            ->unique()
            ->values()
            ->all();

        if ($selected !== []) {
            return $selected;
        }

        if ($defaultColumns !== null && $defaultColumns !== []) {
            return array_values(array_filter(
                $defaultColumns,
                fn ($column) => array_key_exists($column, $availableColumns)
            ));
        }

        return array_keys($availableColumns);
    }

    /**
     * Build a simple CSV filename from a report prefix and optional date filters.
     *
     * @param  array<string, mixed>  $filters
     */
    protected function buildReportFilename(string $prefix, array $filters, ?string $fromKey = null, ?string $toKey = null): string
    {
        $segments = [$prefix];

        $fromValue = $fromKey ? ($filters[$fromKey] ?? null) : null;
        $toValue = $toKey ? ($filters[$toKey] ?? null) : null;

        if (is_string($fromValue) && $fromValue !== '') {
            $segments[] = $fromValue;
        }

        if (is_string($toValue) && $toValue !== '' && $toValue !== $fromValue) {
            $segments[] = $toValue;
        }

        $segments[] = now()->format('Y-m-d-His');

        return implode('-', $segments).'.csv';
    }
}

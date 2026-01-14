<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    public function scopeFilter(Builder $query, array $filters)
    {
        $filterable = $this->filterable ?? [];

        foreach($filters as $field => $value) {
            if (!isset($filterable[$field])) {
                continue;
            }

            if (empty($value) && $value !== '0') {
                continue;
            }

            $filterType = $filterable[$field];

            if (str_ends_with($field, '_from') || str_ends_with($field, '_to')) {
                $columnName = str_replace(['_from', '_to'], '', $field);
            } elseif (str_ends_with($field, '_max') || str_ends_with($field, '_min')) {
                $columnName = str_replace(['_max', '_min'], '', $field);
            } else {
                $columnName = $field;
            }
            
            
            switch ($filterType) {
                case 'exact':
                    $query->where($columnName, '=',$value);
                    break;
                case 'partial':
                    $query->where($columnName, "ILIKE", "%{$value}%");
                    break;
                case 'date_gte':
                    $query->where($columnName, '>=',$value);
                    break;
                case 'date_lte':
                    $query->where($columnName, '<=',$value);
                    break;
                case 'decimal_min':
                    $query->where($columnName, '>=',$value);
                    break;
                case 'decimal_max':
                    $query->where($columnName, '<=',$value);
                    break;
            }
        }
    }
}

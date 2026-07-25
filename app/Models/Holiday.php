<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'type',
        'year',
        'is_recurring',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'year' => 'integer',
            'is_recurring' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function scopeUpcoming($query, ?int $limit = 10)
    {
        return $query->where('date', '>=', now()->toDateString())
            ->where('is_active', true)
            ->orderBy('date')
            ->limit($limit);
    }
}

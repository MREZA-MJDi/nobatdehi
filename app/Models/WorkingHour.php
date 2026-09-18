<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkingHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'salon_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_closed',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_closed' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function salon(): BelongsTo
    {
        return $this->belongsTo(
            Salon::class,
            'salon_id'
        );
    }
}

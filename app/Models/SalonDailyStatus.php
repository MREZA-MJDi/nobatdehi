<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalonDailyStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'salon_id',
        'date',
        'is_closed',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_closed' => 'boolean',
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'salon_id',
        'barber_id',
        'service_id',
        'title',
        'description',
        'before_image_path',
        'after_image_path',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function salon(): BelongsTo
    {
        return $this->belongsTo(
            Salon::class
        );
    }

    public function barber(): BelongsTo
    {
        return $this->belongsTo(
            Barber::class
        );
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(
            Service::class
        );
    }
}

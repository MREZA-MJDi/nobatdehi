<?php

namespace App\Models;

use App\Enums\PostType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'salon_id',
        'barber_id',
        'performed_by_owner',
        'service_id',
        'type',
        'media_path',
        'thumbnail_path',
        'title',
        'caption',
        'is_active',
        'sort_order',
        'views_count',
        'likes_count',
        'comments_count',
    ];

    protected function casts(): array
    {
        return [
            'type' => PostType::class,

            'performed_by_owner' => 'boolean',

            'is_active' => 'boolean',

            'sort_order' => 'integer',

            'views_count' => 'integer',

            'likes_count' => 'integer',

            'comments_count' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Salon
    |--------------------------------------------------------------------------
    */

    public function salon(): BelongsTo
    {
        return $this->belongsTo(
            Salon::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Barber
    |--------------------------------------------------------------------------
    */

    public function barber(): BelongsTo
    {
        return $this->belongsTo(
            Barber::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Service
    |--------------------------------------------------------------------------
    */

    public function service(): BelongsTo
    {
        return $this->belongsTo(
            Service::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Comments
    |--------------------------------------------------------------------------
    */

//    public function comments(): HasMany
//    {
//        return $this->hasMany(
//            PostComment::class
//        );
//    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePublished($query)
    {
        return $query->where(
            'is_active',
            true
        );
    }

    public function scopeOfType(
        $query,
        PostType|string $type
    ) {
        $value = $type instanceof PostType
            ? $type->value
            : $type;

        return $query->where(
            'type',
            $value
        );
    }
}
